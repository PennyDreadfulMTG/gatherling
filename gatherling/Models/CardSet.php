<?php

declare(strict_types=1);

namespace Gatherling\Models;

use stdClass;
use mysqli_stmt;
use Gatherling\Exceptions\FileNotFoundException;

use function Gatherling\Helpers\db;
use function Gatherling\Helpers\normaliseCardName;
use function Safe\file_get_contents;
use function Safe\preg_match;
use function Safe\json_decode;

class CardSet
{
    /** @return list<string> */
    public static function insert(string $code): array
    {
        $url = "https://mtgjson.com/api/v5/{$code}.json";
        $messages = self::insertFromLocation($url);
        return array_merge(["Inserting $code from $url"], $messages);
    }

    /** @return list<string> */
    public static function insertFromLocation(string $filename): array
    {
        $messages = [];
        $file = file_get_contents($filename);
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

        $sql = "SELECT COALESCE(code, '') FROM cardsets where name = :set";
        $codes = db()->strings($sql, ['set' => $set]);
        if (count($codes) === 1 && $codes[0] === '') {
            $sql = 'UPDATE cardsets SET code = :code WHERE name = :set';
            db()->modify($sql, ['code' => $data->code, 'set' => $set]);
            $messages[] = "$set was missing code ($data->code) in db, updated.";
        } elseif (count($codes) === 0) {
            $sql = 'INSERT INTO cardsets (released, name, type, code, standard_legal, modern_legal)
                         VALUES (:released, :name, :type, :code, 0, 0)';
            db()->execute($sql, ['released' => $releaseDate, 'name' => $set, 'type' => $setType, 'code' => $data->code]);
            $messages[] = "Inserted new set ($set, $releaseDate, $setType)...";
        }

        $database = Database::getConnection();

        $stmt = $database->prepare('INSERT INTO cards(cost, convertedcost, name, cardset, type,
  isw, isu, isb, isr, isg, isp, rarity, scryfallId, is_changeling, is_online) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE `cost` = VALUES(`cost`), `convertedcost`= VALUES(`convertedcost`), `type` = VALUES(`type`),
  isw = VALUES(`isw`), isu = VALUES(`isu`), isb = VALUES(`isb`),isr = VALUES(`isr`),isg = VALUES(`isg`),isp = VALUES(`isp`),
  `rarity` = VALUES(`rarity`),scryfallId = VALUES(`scryfallId`), is_changeling = VALUES(`is_changeling`), is_online = VALUES(`is_online`);');

        foreach ($data->cards as $card) {
            $numCardsParsed++;
            $messages[] = self::insertCard($card, $set, $card->rarity, $stmt);
            $numCardsInserted++;
        }

        $messages[] = 'End of File Reached';
        $messages[] = "Total Cards Parsed: {$numCardsParsed}";
        $messages[] = "Total Cards Inserted: {$numCardsInserted}";
        $stmt->close();

        Format::constructTribes($set);
        return $messages;
    }

    public static function insertCard(stdClass $card, string $set, string $rarity, mysqli_stmt $stmt): string
    {
        $typeline = implode(' ', $card->types);
        if (isset($card->subtypes) && count($card->subtypes) > 0) {
            $typeline = $typeline . ' - ' . implode(' ', $card->subtypes);
        }
        $name = normaliseCardName($card->name);
        $isw = $isu = $isb = $isr = $isg = $isp = 0;
        if (isset($card->manaCost)) {
            if (preg_match('/W/', $card->manaCost)) {
                $isw = 1;
            }
            if (preg_match('/U/', $card->manaCost)) {
                $isu = 1;
            }
            if (preg_match('/B/', $card->manaCost)) {
                $isb = 1;
            }
            if (preg_match('/R/', $card->manaCost)) {
                $isr = 1;
            }
            if (preg_match('/G/', $card->manaCost)) {
                $isg = 1;
            }
            if (preg_match('/P/', $card->manaCost)) {
                $isp = 1;
            }
        }

        $changeling = 0;
        if (isset($card->text) && preg_match('/is every creature type/', $card->text)) {
            $changeling = 1;
        }

        $online = in_array('mtgo', $card->availability);

        if (property_exists($card, 'manaCost')) {
            $stmt->bind_param('sdsssddddddssdd', $card->manaCost, $card->convertedManaCost, $name, $set, $typeline, $isw, $isu, $isb, $isr, $isg, $isp, $rarity, $card->scryfallId, $changeling, $online);
        } else {
            $empty_string = '';
            $zero = 0;
            $stmt->bind_param('sdsssddddddssdd', $empty_string, $zero, $name, $set, $typeline, $isw, $isu, $isb, $isr, $isg, $isp, $rarity, $card->scryfallId, $changeling, $online);
        }

        if (!$stmt->execute()) {
            return "CARD INSERTION ERROR: {$name} - {$stmt->error}";
        }
        return "{$name} - Card Inserted Successfully";
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
