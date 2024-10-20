<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Gatherling\Log;
use Gatherling\Exceptions\SetMissingException;

use function Gatherling\Helpers\db;

class Formats
{
    public static function updateDefaultFormats(): void
    {
        self::updateStandard();
        self::updateModern();
        self::updatePennyDreadful();
    }

    private static function loadFormat(string $format): Format
    {
        if (!Format::doesFormatExist($format)) {
            $active_format = new Format('');
            $active_format->name = $format;
            $active_format->type = 'System';
            $active_format->series_name = 'System';
            $active_format->min_main_cards_allowed = 60;
            $active_format->save();
        }

        return new Format($format);
    }

    private static function updateStandard(): void
    {
        $fmt = self::loadFormat('Standard');
        if (!$fmt->standard) {
            $fmt->standard = 1;
            $fmt->save();
        }
        $legal = json_decode(file_get_contents('https://whatsinstandard.com/api/v5/sets.json'));
        if (!$legal) {
            Log::info('Unable to load WhatsInStandard API.  Aborting.');

            return;
        }
        $expected = [];
        foreach ($legal->sets as $set) {
            $now = time();
            $enter = strtotime($set->enter_date);
            $exit = is_null($set->exit_date) ? $now + 1 : strtotime($set->exit_date);
            if ($exit < $now) {
                // Set has rotated out.
            } elseif ($enter == null || $enter > $now) {
                // Set is yet to be released. (And probably not available in MTGJSON yet)
                break;
            }
            // Found one we care about.
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT name, type, standard_legal FROM cardsets WHERE code = ?');
            $stmt->bind_param('s', $set->code);
            $stmt->execute();
            $stmt->bind_result($setName, $setType, $standard_legal);
            $success = $stmt->fetch();
            $stmt->close();
            if (!$success) {
                throw new SetMissingException("Did not find set with code {$set->code} please add it to the database");
            }
            $expected[] = $setName;
        }
        foreach ($fmt->getLegalCardsets() as $setName) {
            if (!in_array($setName, $expected, true)) {
                Log::info("{$setName} is no longer Standard Legal.");
                db()->execute('UPDATE cardsets SET standard_legal = 0 WHERE `name` = :set_name', ['set_name' => $setName]);
            }
        }
        foreach ($expected as $setName) {
            if (!$fmt->isCardSetLegal($setName)) {
                Log::info("{$setName} is now Standard Legal.");
                db()->execute('UPDATE cardsets SET standard_legal = 1 WHERE `name` = :set_name', ['set_name' => $setName]);
            }
        }
    }

    private static function updateModern(): void
    {
        Log::info('Updating Modern...');
        $fmt = self::loadFormat('Modern');
        if (!$fmt->modern) {
            $fmt->modern = 1;
            $fmt->save();
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT name, type, released FROM cardsets WHERE `type` != 'extra' ORDER BY `cardsets`.`released`");
        $stmt->execute();
        $stmt->bind_result($setName, $setType, $setDate);

        $sets = [];
        while ($stmt->fetch()) {
            $sets[] = [$setName, $setType, $setDate];
        }
        $stmt->close();

        $cutoff = strtotime('2003-07-27');
        foreach ($sets as $set) {
            $setName = $set[0];
            $release = strtotime($set[2]);
            if ($release > $cutoff) {
                if (!$fmt->isCardSetLegal($setName)) {
                    Log::info("{$setName} is Modern Legal.");
                    db()->execute('UPDATE cardsets SET modern_legal = 1 WHERE `name` = :set_name', ['set_name' => $setName]);
                }
            }
        }
    }

    private static function updatePennyDreadful(): void
    {
        Log::info('Updating Penny Dreadful...');
        $fmt = self::loadFormat('Penny Dreadful');

        $url = 'https://pennydreadfulmtg.github.io/legal_cards.txt';
        $legal_cards = parseCards(file_get_contents($url));
        if (!$legal_cards) {
            Log::error('Unable to fetch Penny Dreadful legal cards');

            return;
        }
        $i = 0;
        foreach ($fmt->card_legallist as $card) {
            if (!in_array($card, $legal_cards, true)) {
                $fmt->deleteCardFromLegallist($card);
                Log::info("{$card} is no longer Penny Dreadful Legal.");
            }

            if (++$i % 200 == 0) {
                Log::info("Deleted $i cards");
            }
        }
        $i = 0;
        foreach ($legal_cards as $card) {
            if (!in_array($card, $fmt->card_legallist)) {
                if ($fmt->isCardOnBanList($card)) {
                    Log::info("{$card} is banned");
                    continue;
                }
                $success = $fmt->insertCardIntoLegallist($card);
                if (!$success) {
                    Log::error("Can't add {$card} to Penny Dreadful Legal list, it is not in the database.");
                    $setCode = self::findSetForCard($card);
                    throw new SetMissingException("Did not find set with code {$setCode} please add it to the database");
                }
            }

            if (++$i % 200 == 0) {
                Log::info("Added $i cards");
            }
        }
    }

    private static function findSetForCard(string $card): string
    {
        $card = rawurlencode($card);
        $options = ['http' => ['header' => "User-Agent: gatherling.com/1.0\r\n"]];
        $context = stream_context_create($options);
        $data = json_decode(file_get_contents("http://api.scryfall.com/cards/named?exact={$card}", false, $context));

        return strtoupper($data->set);
    }
}
