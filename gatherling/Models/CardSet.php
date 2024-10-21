<?php

declare(strict_types=1);

namespace Gatherling\Models;

use stdClass;
use mysqli_stmt;
use Gatherling\Log;
use Gatherling\Exceptions\DatabaseException;
use Gatherling\Exceptions\FileNotFoundException;

use function Gatherling\Helpers\db;

class CardSet
{
    public static function insert(string $code): void
    {
        $url = "https://mtgjson.com/api/v5/{$code}.json";
        self::insertFromLocation($url);
    }

    public static function insertFromLocation(string $filename): void
    {
        $file = file_get_contents($filename);

        if (!$file) {
            throw new FileNotFoundException("Can't open the file you requested: {$filename}");
        }

        $data = json_decode($file);
        $data = $data->data;
        $set = $data->name;
        if ($set == 'Time Spiral "Timeshifted"') {
            // Terrible hack, but needed.
            $set = 'Time Spiral Timeshifted';
        }
        $setType = $data->type;
        $setType = match ($setType) {
            'core', 'starter' => 'Core',
            'expansion' => 'Block',
            default     => 'Extra',
        };
        $releaseDate = $data->releaseDate;

        $numCardsParsed = 0;
        $numCardsInserted = 0;

        $database = Database::getConnection();

        $stmt = $database->prepare('SELECT * FROM cardsets where name = ?');
        if (!$stmt) {
            throw new DatabaseException($database->error);
        }

        $stmt->bind_param('s', $set);

        $set_already_in = false;

        if (!$stmt->execute()) {
            throw new \Exception($stmt->error);
        }
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $set_already_in = true;

            $row = $result->fetch_array();
            if (is_null($row['code'])) {
                Log::info("$set is missing code ($data->code) in db.");
                $stmt = $database->prepare('UPDATE cardsets SET code = ? WHERE name = ?');
                $stmt->bind_param('ss', $data->code, $row['name']);
                if (!$stmt->execute()) {
                    throw new \Exception($stmt->error);
                }
            }
        }

        if (!$set_already_in) {
            Log::info("Inserting card set ($set, $releaseDate, $setType)...");

            // Insert the card set
            $stmt = $database->prepare('INSERT INTO cardsets(released, name, type, code, standard_legal, modern_legal) values(?, ?, ?, ?, 0, 0)');
            $stmt->bind_param('ssss', $releaseDate, $set, $setType, $data->code);

            if (!$stmt->execute()) {
                throw new \Exception($stmt->error);
            } else {
                Log::info("Inserted new set {$set}!");
            }
            $stmt->close();
        }

        $stmt = $database->prepare('INSERT INTO cards(cost, convertedcost, name, cardset, type,
  isw, isu, isb, isr, isg, isp, rarity, scryfallId, is_changeling, is_online) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE `cost` = VALUES(`cost`), `convertedcost`= VALUES(`convertedcost`), `type` = VALUES(`type`),
  isw = VALUES(`isw`), isu = VALUES(`isu`), isb = VALUES(`isb`),isr = VALUES(`isr`),isg = VALUES(`isg`),isp = VALUES(`isp`),
  `rarity` = VALUES(`rarity`),scryfallId = VALUES(`scryfallId`), is_changeling = VALUES(`is_changeling`), is_online = VALUES(`is_online`);');

        foreach ($data->cards as $card) {
            $numCardsParsed++;
            self::insertCard($card, $set, $card->rarity, $stmt);
            $numCardsInserted++;
        }

        Log::info('End of File Reached');
        Log::info("Total Cards Parsed: {$numCardsParsed}");
        Log::info("Total Cards Inserted: {$numCardsInserted}");
        $stmt->close();

        Format::constructTribes($set);
    }

    public static function insertCard(stdClass $card, string $set, string $rarity, mysqli_stmt $stmt): void
    {
        $typeline = implode(' ', $card->types);
        if (isset($card->subtypes) && count($card->subtypes) > 0) {
            $typeline = $typeline . ' - ' . implode(' ', $card->subtypes);
        }
        $name = normaliseCardName($card->name);
        Log::info("Inserting $name");
        foreach (['manaCost', 'convertedManaCost', 'type', 'rarity'] as $attr) {
            if (isset($card->{$attr})) {
                Log::info("{$attr}: {$card->{$attr}}");
            }
        }
        $isw = $isu = $isb = $isr = $isg = $isp = 0;
        $colors = [];
        if (isset($card->manaCost)) {
            if (preg_match('/W/', $card->manaCost)) {
                $isw = 1;
                $colors[] = 'White';
            }
            if (preg_match('/U/', $card->manaCost)) {
                $isu = 1;
                $colors[] = 'Blue';
            }
            if (preg_match('/B/', $card->manaCost)) {
                $isb = 1;
                $colors[] = 'Black';
            }
            if (preg_match('/R/', $card->manaCost)) {
                $isr = 1;
                $colors[] = 'Red';
            }
            if (preg_match('/G/', $card->manaCost)) {
                $isg = 1;
                $colors[] = 'Green';
            }
            if (preg_match('/P/', $card->manaCost)) {
                $isp = 1;
                $colors[] = 'Phyrexian';
            }
        }
        Log::info("Colors: " . implode(', ', $colors));

        $changeling = 0;
        if (isset($card->text) && preg_match('/is every creature type/', $card->text)) {
            $changeling = 1;
        }

        $online = in_array('mtgo', $card->availability);

        $empty_string = '';
        $zero = 0;

        if (property_exists($card, 'manaCost')) {
            $stmt->bind_param('sdsssddddddssdd', $card->manaCost, $card->convertedManaCost, $name, $set, $typeline, $isw, $isu, $isb, $isr, $isg, $isp, $rarity, $card->scryfallId, $changeling, $online);
        } else {
            $stmt->bind_param('sdsssddddddssdd', $empty_string, $zero, $name, $set, $typeline, $isw, $isu, $isb, $isr, $isg, $isp, $rarity, $card->scryfallId, $changeling, $online);
        }

        if (!$stmt->execute()) {
            Log::error("Card Insertion Error: {$stmt->error}");
            exit($stmt->error);
        } else {
            Log::info('Card Inserted Successfully');
        }
    }

    /**
     * @return list<string>
     */
    public static function getMissingSets(string $cardSetType, Format $format): array
    {
        $sql = 'SELECT name FROM cardsets WHERE type = :type';
        $cardSets = db()->strings($sql, ['type' => $cardSetType]);

        $finalList = [];
        foreach ($cardSets as $cardSetName) {
            if (!$format->isCardSetLegal($cardSetName)) {
                $finalList[] = $cardSetName;
            }
        }

        return $finalList;
    }
}
