<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Exception;

class Format
{
    public ?string $name;
    public ?string $description;
    public ?string $type;        // who has access to filter: public, private, system
    public ?string $series_name; // filter owner
    public ?int $priority;
    public ?bool $new = null;

    // card set construction
    /** @var list<string> */
    public array $card_banlist = [];
    /** @var list<string> */
    public array $card_restrictedlist = [];
    /** @var list<string> */
    public array $card_legallist = [];
    /** @var list<string> */
    public array $legal_sets = [];
    public ?int $eternal;
    public ?int $modern;
    public ?int $standard;

    // deck construction switches
    public ?int $singleton;
    public ?int $commander;
    public ?int $planechase;
    public ?int $vanguard;
    public ?int $prismatic;
    public ?int $tribal;
    public ?int $pure;
    public ?int $underdog;
    public ?int $limitless;

    // rarities allowed switches
    public ?int $allow_commons;
    public ?int $allow_uncommons;
    public ?int $allow_rares;
    public ?int $allow_mythics;
    public ?int $allow_timeshifted;

    // deck limits
    public ?int $min_main_cards_allowed;
    public ?int $max_main_cards_allowed;
    public ?int $min_side_cards_allowed;
    public ?int $max_side_cards_allowed;

    // Meta Formats
    public ?int $is_meta_format;

    /** @var list<string> */
    private array $error = [];

    public function __construct(string $name)
    {
        if ($name == '') {
            $this->name = '';
            $this->description = '';
            $this->type = '';
            $this->series_name = '';
            $this->priority = 1;
            $this->card_banlist = [];
            $this->card_legallist = [];
            $this->card_restrictedlist = [];
            $this->legal_sets = [];
            $this->eternal = 0;
            $this->modern = 0;
            $this->standard = 0;
            $this->singleton = 0;
            $this->commander = 0;
            $this->planechase = 0;
            $this->vanguard = 0;
            $this->prismatic = 0;
            $this->tribal = 0;
            $this->pure = 0;
            $this->underdog = 0;
            $this->allow_commons = 1;
            $this->allow_uncommons = 1;
            $this->allow_rares = 1;
            $this->allow_mythics = 1;
            $this->allow_timeshifted = 1;
            $this->min_main_cards_allowed = 0;
            $this->max_main_cards_allowed = 2000;
            $this->min_side_cards_allowed = 0;
            $this->max_side_cards_allowed = 15;
            $this->is_meta_format = 0;
            $this->new = true;

            return;
        }

        if ($this->new) {
            $this->new = false;

            $this->insertNewFormat();
            return;
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT name, description, type, series_name, singleton, commander, planechase, vanguard,
                                         prismatic, tribal, pure, underdog, limitless, allow_commons, allow_uncommons, allow_rares, allow_mythics,
                                         allow_timeshifted, priority, min_main_cards_allowed, max_main_cards_allowed,
                                         min_side_cards_allowed, max_side_cards_allowed, eternal, modern, `standard`, is_meta_format
                                  FROM formats
                                  WHERE name = ?');
            $stmt or exit($db->error);
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $stmt->bind_result(
                $this->name,
                $this->description,
                $this->type,
                $this->series_name,
                $this->singleton,
                $this->commander,
                $this->planechase,
                $this->vanguard,
                $this->prismatic,
                $this->tribal,
                $this->pure,
                $this->underdog,
                $this->limitless,
                $this->allow_commons,
                $this->allow_uncommons,
                $this->allow_rares,
                $this->allow_mythics,
                $this->allow_timeshifted,
                $this->priority,
                $this->min_main_cards_allowed,
                $this->max_main_cards_allowed,
                $this->min_side_cards_allowed,
                $this->max_side_cards_allowed,
                $this->eternal,
                $this->modern,
                $this->standard,
                $this->is_meta_format
            );
            if ($stmt->fetch() == null) {
                throw new Exception('Format ' . $name . ' not found in DB');
            }
            $stmt->close();
            $this->card_banlist = $this->getBanList();
            $this->card_legallist = $this->getLegalList();
            $this->card_restrictedlist = $this->getRestrictedList();
            $this->legal_sets = $this->getLegalCardsets();
        }
    }

    public static function constructTribes(string $set = 'All'): void
    {
        // adds tribe types to tribes table in database
        // if no set is specified, uses all sets from cardsets table

        $cardSets = [];
        if ($set == 'All') {
            $cardSets = Database::listResult('SELECT name FROM cardsets');
        } else {
            $cardSets[] = $set;
        }

        foreach ($cardSets as $cardSet) {
            echo "Processing $cardSet<br />";
            $cardTypes = Database::listResultSingleParam("SELECT type
                                                             FROM cards
                                                             WHERE type
                                                             LIKE '%Creature%'
                                                             AND cardset = ?", 's', $cardSet);
            foreach ($cardTypes as $type) {
                $type = self::removeTypeCrap($type);
                $types = explode(' ', $type);
                foreach ($types as $subtype) {
                    $type = trim($subtype);
                    if ($subtype == '') {
                        continue;
                    }
                    if (self::isTribeTypeInDatabase($subtype)) {
                        continue;
                    } else {
                        // type is not in database, so insert it
                        echo "New Tribe Found! Inserting: <pre>$subtype</pre><br />";
                        $db = Database::getConnection();
                        $stmt = $db->prepare('INSERT INTO tribes(name) VALUES(?)');
                        $stmt->bind_param('s', $subtype);
                        $stmt->execute() or exit($stmt->error);
                        $stmt->close();
                    }
                }
            }
        }
    }

    public static function isTribeTypeInDatabase(string $type): bool
    {
        $tribe = Database::singleResultSingleParam('SELECT name
                                           FROM tribes
                                           WHERE name = ?', 's', $type);
        return ($type && $tribe && strcasecmp($tribe, $type) == 0);
    }

    public static function doesFormatExist(string $format): bool
    {
        $success = false;
        $formatName = Database::singleResultSingleParam('SELECT name FROM formats WHERE name = ?', 's', $format);
        if ($formatName) {
            $success = true;
        }

        return $success;
    }

    private function insertNewFormat(): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO formats(name, description, type, series_name, singleton, commander, planechase,
                                                  vanguard, prismatic, tribal, pure, underdog, limitless, allow_commons, allow_uncommons, allow_rares,
                                                  allow_mythics, allow_timeshifted, priority, min_main_cards_allowed,
                                                  max_main_cards_allowed, min_side_cards_allowed, max_side_cards_allowed,
                                                  eternal, modern, `standard`, is_meta_format)
                              VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'ssssddddddddddddddddddddddd',
            $this->name,
            $this->description,
            $this->type,
            $this->series_name,
            $this->singleton,
            $this->commander,
            $this->planechase,
            $this->vanguard,
            $this->prismatic,
            $this->tribal,
            $this->pure,
            $this->underdog,
            $this->limitless,
            $this->allow_commons,
            $this->allow_uncommons,
            $this->allow_rares,
            $this->allow_mythics,
            $this->allow_timeshifted,
            $this->priority,
            $this->min_main_cards_allowed,
            $this->max_main_cards_allowed,
            $this->min_side_cards_allowed,
            $this->max_side_cards_allowed,
            $this->eternal,
            $this->modern,
            $this->standard,
            $this->is_meta_format
        );
        $stmt->execute() or exit($stmt->error);
        $stmt->close();

        return true;
    }

    public function saveAndDeleteAuthorization(string $playerName): bool
    {
        // this will be used to determine if the save and delete buttons will appear on the format editor
        // there are 3 different format types: system, public, private

        $player = new Player($playerName); // to access isOrganizer and isSuper functions
        $authorized = false;

        switch ($this->type) {
            case 'System':
                // Only supers can save or delete system formats
                if ($player->isSuper()) {
                    $authorized = true;
                }
                break;
            case 'Public':
                // Only Series Organizer of the series that created the format
                // and Supers can save or delete Public formats
                if ($player->isOrganizer($this->series_name) || $player->isSuper()) {
                    $authorized = true;
                }
                break;
            case 'Private':
                // The only difference in access between a public and private format is that private formats can be
                // viewed only by the series organizers of the series it belongs to
                // the save and delete access is the same
                if ($player->isOrganizer($this->series_name) || $player->isSuper()) {
                    $authorized = true;
                }
                break;
        }

        return $authorized;
    }

    public function viewAuthorization(string $playerName): bool
    {
        // this will be used to determine if a format will appear in the drop down to load in the format filter
        // there are 3 different format types: system, public, private

        $player = new Player($playerName); // to access isOrganizer and isSuper functions

        switch ($this->type) {
            case 'System':
            case 'Public':
                return true; // anyone can view a system and public format
            case 'Private':
                // Only supers and organizers can view private formats
                if ($player->isOrganizer($this->series_name) || $player->isSuper()) {
                    return true;
                }
                break;
        }

        return false;
    }

    public function save(): bool
    {
        if ($this->new) {
            $this->new = false;

            return $this->insertNewFormat();
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE formats
                                  SET description = ?, type = ?, series_name = ?, singleton = ?, commander = ?,
                                  planechase = ?, vanguard = ?, prismatic = ?, tribal = ?, pure = ?, underdog = ?, limitless = ?,
                                  allow_commons = ?, allow_uncommons = ?, allow_rares = ?,
                                  allow_mythics = ?, allow_timeshifted = ?, priority = ?, min_main_cards_allowed = ?,
                                  max_main_cards_allowed = ?, min_side_cards_allowed = ?, max_side_cards_allowed = ?,
                                  eternal = ?, modern = ?, `standard` = ?, is_meta_format = ?
                                  WHERE name = ?');
            $stmt or exit($db->error);
            $stmt->bind_param(
                'sssddddddddddddddddddddddds',
                $this->description,
                $this->type,
                $this->series_name,
                $this->singleton,
                $this->commander,
                $this->planechase,
                $this->vanguard,
                $this->prismatic,
                $this->tribal,
                $this->pure,
                $this->underdog,
                $this->limitless,
                $this->allow_commons,
                $this->allow_uncommons,
                $this->allow_rares,
                $this->allow_mythics,
                $this->allow_timeshifted,
                $this->priority,
                $this->min_main_cards_allowed,
                $this->max_main_cards_allowed,
                $this->min_side_cards_allowed,
                $this->max_side_cards_allowed,
                $this->eternal,
                $this->modern,
                $this->standard,
                $this->is_meta_format,
                $this->name
            );
            $stmt->execute() or exit($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function saveAs(string $oldName = ''): bool
    {
        // name, type, and series_name should all be specified before calling this function
        $success = $this->insertNewFormat();
        if ($oldName != '') {
            $oldFormat = new self($oldName);
            $this->allow_commons = $oldFormat->allow_commons;
            $this->allow_uncommons = $oldFormat->allow_uncommons;
            $this->allow_rares = $oldFormat->allow_rares;
            $this->allow_mythics = $oldFormat->allow_mythics;
            $this->allow_timeshifted = $oldFormat->allow_timeshifted;
            $this->singleton = $oldFormat->singleton;
            $this->commander = $oldFormat->commander;
            $this->planechase = $oldFormat->planechase;
            $this->vanguard = $oldFormat->vanguard;
            $this->prismatic = $oldFormat->prismatic;
            $this->tribal = $oldFormat->tribal;
            $this->pure = $oldFormat->pure;
            $this->underdog = $oldFormat->underdog;
            $this->limitless = $oldFormat->limitless;
            $this->priority = $oldFormat->priority;
            $this->description = $oldFormat->description;
            $this->min_main_cards_allowed = $oldFormat->min_main_cards_allowed;
            $this->max_main_cards_allowed = $oldFormat->max_main_cards_allowed;
            $this->min_side_cards_allowed = $oldFormat->min_side_cards_allowed;
            $this->max_side_cards_allowed = $oldFormat->max_side_cards_allowed;
            $this->eternal = $oldFormat->eternal;
            $this->modern = $oldFormat->modern;
            $this->standard = $oldFormat->standard;
            $this->is_meta_format = $oldFormat->is_meta_format;
            $this->new = false;
            $success = $this->save();
            if (!$success) {
                return false;
            }

            foreach ($oldFormat->card_banlist as $bannedCard) {
                $this->insertCardIntoBanlist($bannedCard);
            }

            foreach ($oldFormat->card_restrictedlist as $restrictedCard) {
                $this->insertCardIntoRestrictedlist($restrictedCard);
            }

            foreach ($oldFormat->card_legallist as $legalCard) {
                $this->insertCardIntoLegallist($legalCard);
            }

            foreach ($oldFormat->legal_sets as $legalset) {
                $this->insertNewLegalSet($legalset);
            }
        }

        return $success;
    }

    public function rename(string $oldName = ''): bool
    {
        // $this->name, $this->type, and $this->series_name of the new format should all be specified before calling this function
        $success = $this->saveAs($oldName);
        if ($oldName != '' && $success) {
            $oldFormat = new self($oldName);
            $success = $oldFormat->delete();
        }

        return $success;
    }

    public function delete(): bool
    {
        $success = $this->deleteEntireLegallist();
        $success = $this->deleteEntireBanlist();
        $success = $this->deleteEntireRestrictedlist();
        $success = $this->deleteAllLegalSets();
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM formats WHERE name = ? AND series_name = ?');
        $stmt->bind_param('ss', $this->name, $this->series_name);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();

        return $success;
    }

    public function noFormatLoaded(): bool
    {
        return ($this->name == '') || is_null($this->name);
    }

    /** @return list<string> */
    public function getLegalCardsets(): array
    {
        if ($this->eternal) {
            return Database::listResult('SELECT name FROM cardsets');
        }
        if ($this->modern) {
            return Database::listResult('SELECT name FROM cardsets WHERE modern_legal = 1');
        }
        if ($this->standard) {
            return Database::listResult('SELECT name FROM cardsets WHERE standard_legal = 1');
        }

        return Database::listResultSingleParam('SELECT cardset FROM setlegality WHERE format = ?', 's', $this->name);
    }

    /** @return ?array<string, string> */
    public function getLegalCard(string $cardName): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id, name FROM cards WHERE name = ? AND cardset = ?');
        $cardar = [];

        foreach ($this->legal_sets as $setName) {
            $stmt->bind_param('ss', $cardName, $setName);
            $stmt->execute();
            $stmt->bind_result($cardar['id'], $cardar['name']);
            if (is_null($stmt->fetch())) {
                $cardar = null;
            } else {
                break; // we only need to know that the card exists in the legal card sets once
            }
        }
        $stmt->close();

        return $cardar;
    }

    /** @return list<string> */
    public static function getSystemFormats(): array
    {
        return Database::listResultSingleParam('SELECT name FROM formats WHERE type = ?', 's', 'System');
    }

    /** @return list<string> */
    public static function getPublicFormats(): array
    {
        return Database::listResultSingleParam('SELECT name FROM formats WHERE type = ?', 's', 'Public');
    }

    /** @return list<string> */
    public static function getPrivateFormats(string $seriesName): array
    {
        return Database::listResultDoubleParam(
            'SELECT name FROM formats WHERE type = ? AND series_name = ?',
            'ss',
            'Private',
            $seriesName
        );
    }

    /** @return list<string> */
    public static function getAllFormats(): array
    {
        return Database::listResult('SELECT name FROM formats');
    }

    /** @return list<string> */
    public function getCoreCardsets(): array
    {
        $legalSets = Database::listResultSingleParam('SELECT cardset FROM setlegality WHERE format = ?', 's', $this->name);

        $legalCoreSets = [];
        foreach ($legalSets as $legalSet) {
            $setType = Database::singleResultSingleParam('SELECT type FROM cardsets WHERE name = ?', 's', $legalSet);
            if (strcmp($setType, 'Core') == 0) {
                $legalCoreSets[] = $legalSet;
            }
        }

        return $legalCoreSets;
    }

    /** @return list<string> */
    public function getBlockCardsets(): array
    {
        $legalSets = Database::listResultSingleParam('SELECT cardset FROM setlegality WHERE format = ?', 's', $this->name);

        $legalBlockSets = [];
        foreach ($legalSets as $legalSet) {
            $setType = Database::singleResultSingleParam('SELECT type FROM cardsets WHERE name = ?', 's', $legalSet);
            if (strcmp($setType, 'Block') == 0) {
                $legalBlockSets[] = $legalSet;
            }
        }

        return $legalBlockSets;
    }

    /** @return list<string> */
    public function getExtraCardsets(): array
    {
        $legalSets = Database::listResultSingleParam('SELECT cardset FROM setlegality WHERE format = ?', 's', $this->name);

        $legalExtraSets = [];
        foreach ($legalSets as $legalSet) {
            $setType = Database::singleResultSingleParam('SELECT type FROM cardsets WHERE name = ?', 's', $legalSet);
            if (strcmp($setType, 'Extra') == 0) {
                $legalExtraSets[] = $legalSet;
            }
        }

        return $legalExtraSets;
    }

    /** @return list<string> */
    public function getBanList(): array
    {
        return Database::listResultSingleParam('SELECT card_name
                                                   FROM bans
                                                   WHERE format = ?
                                                   AND allowed = 0
                                                   ORDER BY card_name', 's', $this->name);
    }

    /** @return list<string> */
    public function getTribesBanned(): array
    {
        return Database::listResultSingleParam('SELECT name
                                                   FROM tribe_bans
                                                   WHERE format = ?
                                                   AND allowed = 0
                                                   ORDER BY name', 's', $this->name);
    }

    /** @return list<string> */
    public function getSubTypesBanned(): array
    {
        return Database::listResultSingleParam('SELECT name
                                                   FROM subtype_bans
                                                   WHERE format = ?
                                                   AND allowed = 0
                                                   ORDER BY name', 's', $this->name);
    }

    /** @return list<string> */
    public function getLegalList(): array
    {
        return Database::listResultSingleParam(
            'SELECT card_name
                                                   FROM bans
                                                   WHERE format = ? AND allowed = 1
                                                   ORDER BY card_name',
            's',
            $this->name
        );
    }

    /** @return list<string> */
    public function getTribesAllowed(): array
    {
        return Database::listResultSingleParam(
            'SELECT name
                                                   FROM tribe_bans
                                                   WHERE format = ? AND allowed = 1
                                                   ORDER BY name',
            's',
            $this->name
        );
    }

    /** @return list<string> */
    public function getRestrictedList(): array
    {
        return Database::listResultSingleParam(
            'SELECT card_name
                                                   FROM restricted
                                                   WHERE format = ?
                                                   ORDER BY card_name',
            's',
            $this->name
        );
    }

    /** @return list<string> */
    public function getRestrictedTotribeList(): array
    {
        return Database::listResultSingleParam(
            'SELECT card_name
                                                   FROM restrictedtotribe
                                                   WHERE format = ? AND allowed = 1
                                                   ORDER BY card_name',
            's',
            $this->name
        );
    }

    /** @return list<string> */
    public function getErrors(): array
    {
        $currentErrors = $this->error;
        $this->error = [];

        return $currentErrors;
    }

    /** @return list<string> */
    public function getFormats(): array
    {
        return Database::listResult('SELECT name FROM formats');
    }

    /** @return list<string> */
    public static function getTribesList(): array
    {
        return Database::listResult('SELECT name FROM tribes ORDER BY name');
    }

    public function isCardLegalByRarity(string $cardName): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT rarity FROM cards WHERE name = ? AND cardset = ?');
        $cardRarities = [];

        foreach ($this->legal_sets as $setName) {
            $stmt->bind_param('ss', $cardName, $setName);
            $stmt->execute();
            $stmt->bind_result($result);
            if ($stmt->fetch()) {
                $cardRarities[] = $result;
            }
        }
        $stmt->close();

        $cardRarities = array_unique($cardRarities, SORT_STRING); //Get only the unique values

        foreach ($cardRarities as $rarity) {
            switch ($rarity) {
                case 'Land':
                case 'Basic Land':
                    return true;
                case 'Common':
                case 'common':
                    if ($this->allow_commons == 1) {
                        return true;
                    }
                    break;
                case 'Uncommon':
                case 'uncommon':
                    if ($this->allow_uncommons == 1) {
                        return true;
                    }
                    break;
                case 'Rare':
                case 'rare':
                    if ($this->allow_rares == 1) {
                        return true;
                    }
                    break;
                case 'Mythic Rare':
                case 'mythic rare':
                case 'mythic':
                    if ($this->allow_mythics == 1) {
                        return true;
                    }
                    break;
                case 'Timeshifted':
                case 'timeshifted':
                    if ($this->allow_timeshifted == 1) {
                        return true;
                    }
                    break;
                case 'Special':
                case 'special':
                    if ($this->vanguard == 1) {
                        return true;
                    }
                    break;
                default:
                    exit("Unexpected rarity {$rarity}!");
            }
        }

        return false;
    }

    public function isCardOnBanList(string $card): bool
    {
        return count(Database::listResultDoubleParam(
            'SELECT card_name
                                                         FROM bans
                                                         WHERE (format = ?
                                                         AND card_name = ?
                                                         AND allowed = 0)',
            'ss',
            $this->name,
            $card
        )) > 0;
    }

    public function isCardOnLegalList(string $card): bool
    {
        return count(Database::listResultDoubleParam(
            'SELECT card_name
                                                         FROM bans
                                                         WHERE (format = ?
                                                         AND card_name = ?
                                                         AND allowed = 1)',
            'ss',
            $this->name,
            $card
        )) > 0;
    }

    public function isCardOnRestrictedList(string $card): bool
    {
        return count(Database::listResultDoubleParam(
            'SELECT card_name
                                                         FROM restricted
                                                         WHERE (format = ?
                                                         AND card_name = ?)',
            'ss',
            $this->name,
            $card
        )) > 0;
    }

    public function isCardOnRestrictedToTribeList(string $card): bool
    {
        return count(Database::listResultDoubleParam(
            'SELECT card_name
                                                         FROM restrictedtotribe
                                                         WHERE (format = ?
                                                         AND card_name = ?)',
            'ss',
            $this->name,
            $card
        )) > 0;
    }

    public function isCardSetLegal(string $setName): bool
    {
        if ($this->eternal) {
            return true;
        }
        $legal = $this->getLegalCardsets();
        foreach ($legal as $legalsetName) {
            if (strcmp($setName, $legalsetName) == 0) {
                return true;
            }
        }

        return false;
    }

    public function isCardBasic(string $card): bool
    {
        switch ($card) {
            case 'Relentless Rats':
            case 'Rat Colony':
            case 'Shadowborn Apostle':
            case 'Persistent Petitioners':
            case 'Swamp':
            case 'Plains':
            case 'Island':
            case 'Mountain':
            case 'Forest':
            case 'Wastes':
            case 'Snow-Covered Swamp':
            case 'Snow-Covered Plains':
            case 'Snow-Covered Island':
            case 'Snow-Covered Mountain':
            case 'Snow-Covered Forest':
                return true;
            default:
                return false;
        }
    }

    public function isCardSingletonLegal(string $card, int $amt): bool
    {
        if ($amt == 1) {
            return true;
        }

        if ($this->isCardBasic($card)) {
            return true;
        }

        if ($card == 'Seven Dwarves' && $amt <= 7) {
            return true;
        }

        return false;
    }

    public static function getCardType(string $card): string
    {
        // Selecting card type for card = $card
        $cardType = Database::singleResultSingleParam('SELECT type
                                                          FROM cards
                                                          WHERE name = ?', 's', $card);

        return $cardType;
    }

    public static function removeTypeCrap(string $typeString): string
    {
        // leave only the tribal sub types
        $typeString = str_ireplace('Tribal ', '', $typeString);
        $typeString = str_ireplace('Creature ', '', $typeString);
        $typeString = str_ireplace('Snow ', '', $typeString);
        $typeString = str_ireplace('Artifact ', '', $typeString);
        $typeString = str_ireplace('Instant ', '', $typeString);
        $typeString = str_ireplace('Enchantment ', '', $typeString);
        $typeString = str_ireplace('Sorcery ', '', $typeString);
        $typeString = str_ireplace('Legendary ', '', $typeString);
        $typeString = str_ireplace('//', '', $typeString);
        $typeString = str_ireplace('- ', '', $typeString);
        $typeString = str_ireplace('	', ' ', $typeString); // Not gonna ask how a tab ended up in the db

        return $typeString;
    }

    public static function isChangeling(string $card): bool
    {
        switch ($card) {
            case 'Amoeboid Changeling':
            case 'Avian Changeling':
            case 'Cairn Wanderer':
            case 'Chameleon Colossus':
            case 'Changeling Berserker':
            case 'Changeling Hero':
            case 'Changeling Sentinel':
            case 'Changeling Titan':
            case 'Fire-Belly Changeling':
            case 'Game-Trail Changeling':
            case 'Ghostly Changeling':
            case 'Mirror Entity':
            case 'Mistform Ultimus':
            case 'Moonglove Changeling':
            case 'Mothdust Changeling':
            case 'Shapesharer':
            case 'Skeletal Changeling':
            case 'Taurean Mauler':
            case 'Turtleshell Changeling':
            case 'War-Spike Changeling':
            case 'Woodland Changeling':
                return true;
            default:
                return false;
        }
    }

    /** @return array{name: string, count: int} */
    public function getTribe(int $deckID): array
    {
        $deck = new Deck($deckID);
        $creatures = $deck->getCreatureCards();
        $subTypeCount = [];
        $subTypeChangeling = 0; // this holds total number of changeling
        $changelingCreatures = [];
        $restrictedToTribeCreatures = [];
        $tribesTied = [];
        $tribeKey = '';

        foreach ($creatures as $card => $amt) {
            // Begin processing tribe subtypes
            $creatureType = self::getCardType($card);
            $creatureType = self::removeTypeCrap($creatureType);
            if (self::isChangeling($card)) {
                $subTypeChangeling += $amt;
            } // have to add total number of changeling here, not in subtype loop.
            if (self::isCardOnRestrictedToTribeList($card)) {
                $restrictedToTribeCreatures[$card] = $creatureType;
            } // tribe must be of this cards subtype in order to be used
            $subTypes = explode(' ', $creatureType);
            foreach ($subTypes as $type) {
                $type = trim($type);
                if ($type == '') {
                    continue;
                }
                if (self::isChangeling($card)) {
                    if (array_key_exists($type, $changelingCreatures)) {
                        $changelingCreatures[$type] += $amt;
                    } else {
                        $changelingCreatures[$type] = $amt;
                    }
                } else {
                    // After Exploding subtype into array
                    if (array_key_exists($type, $subTypeCount)) {
                        $subTypeCount[$type] += $amt;
                    } else {
                        $subTypeCount[$type] = $amt;
                    }
                }
            }
        }

        foreach ($subTypeCount as $type => $amt) {
            echo "$type: $amt<br />";
        }

        arsort($subTypeCount); // sorts by value from high to low.

        $count = 0;
        $firstType = '';
        // After Sorting SubType List
        foreach ($subTypeCount as $Type => $amt) {
            // checking to see if there is a tie in the types
            if ($count == 0) {
                $tribesTied[$Type] = $amt;
                $firstType = $Type;
            } else {
                if ($tribesTied[$firstType] == $amt) {
                    $tribesTied[$Type] = $amt;
                }
            }
            $count++;
        }

        if (count($tribesTied) > 1) {
            // Two or more tribes are tied for largest tribe
            foreach ($tribesTied as $Type => $amt) {
                // Checking for tribe size in database for tie breaker
                // current routine has two logic errors
                // 1) Cards that have more than one printing should only be counted once
                // 2) Can't remember what the second one is... blah!
                $frequency = Database::singleResult("SELECT Count(*) FROM cards WHERE type LIKE '%{$Type}%'");
                $tribesTied[$Type] = $frequency;
            }
            asort($tribesTied); // sorts tribe size by value from low to high for tie breaker
            reset($tribesTied);
            $underdogKey = key($tribesTied);
        // get first key, which should be lowest from sort
        // Smallest Tribe is then selected
        } else {
            reset($subTypeCount);
            $underdogKey = key($subTypeCount); // get first key, which should highest from sort
        }

        if ($underdogKey == '') {
            // Deck contains all changelings
            arsort($changelingCreatures);
            reset($changelingCreatures);
            $underdogKey = key($changelingCreatures);
        }

        // underdog format allows shapeshifters to use as many changelings as they want
        // underdog format allows Tribes with only 3 members to
        // underdog allows only 4 changelings per deck list
        if ($this->underdog) {
            echo "Tribe is: $underdogKey<br />";
            if ($underdogKey != 'Shapeshifter') {
                $frequency = Database::singleResult("SELECT Count(*) FROM cards WHERE type LIKE '%{$underdogKey}%'");
                if ($frequency < 4) {
                    echo "$underdogKey is a 3 card tribe<br />";
                    if ($subTypeChangeling > 8) {
                        $this->error[] = "Tribe $underdogKey is allowed a maximum of 8 changeling's per deck in underdog format";
                    }
                } else {
                    // echo "I am not a 3 card tribe<br />";
                    if ($subTypeChangeling > 4) {
                        $this->error[] = "This tribe can't include more than 4 Changeling creatures because it's not a 3-member tribe.";
                    }
                }
            }
        }

        // do changeling
        // will need to add a changeling feature to the Format
        // so that this changeling feature can be turned on or off.
        // here we add the changeling numbers to each of the other subtypes
        if (!$this->pure) {
            foreach ($subTypeCount as $Type => $amt) {
                $subTypeCount[$Type] += $subTypeChangeling;
            }
        }

        // process changelings here since they were skipped earlier to
        // prevent duplicate adding
        // here we check to see if the changeling's type is already counted for
        // if not we add it to the list of types
        foreach ($changelingCreatures as $Type => $amt) {
            if (array_key_exists($Type, $subTypeCount)) {
                continue;
            } else {
                $subTypeCount[$Type] = $amt;
            }
        }

        $count = 0;
        $firstType = '';
        $bannedSubTypes = self::getSubTypesBanned();
        // After Sorting SubType List
        foreach ($subTypeCount as $Type => $amt) {
            // running tribe algorythm while outputting sort. Will output tribes after in new loop.
            foreach ($bannedSubTypes as $bannedSubType) {
                if ($Type == $bannedSubType) {
                    $this->error[] = "No creatures of type $bannedSubType are allowed";
                }
            }
            if ($count == 0) {
                $tribesTied[$Type] = $amt;
                $firstType = $Type;
            } else {
                if ($tribesTied[$firstType] == $amt) {
                    $tribesTied[$Type] = $amt;
                }
            }
            $count++;
        }

        if (count($tribesTied) > 1) {
            // Two or more tribes are tied for largest tribe
            foreach ($tribesTied as $Type => $amt) {
                // Checking for tribe size in database for tie breaker
                // current routine has two logic errors
                // 1) Cards that have more than one printing should only be counted once
                // 2) Can't remember what the second one is... blah!
                $frequency = Database::singleResult("SELECT Count(*) FROM cards WHERE type LIKE '%{$Type}%'");
                $tribesTied[$Type] = $frequency;
            }
            asort($tribesTied); // sorts tribe size by value from low to high for tie breaker
            reset($tribesTied);
            $tribeKey = key($tribesTied);
        // get first key, which should be lowest from sort
        // Smallest Tribe is then selected
        } else {
            reset($subTypeCount);
            $tribeKey = key($subTypeCount); // get first key, which should highest from sort
        }

        // set tribe column in the deck table
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE decks SET tribe = ? WHERE id = ?');
        $stmt->bind_param('sd', $tribeKey, $deckID);
        $stmt->execute();
        $stmt->close();

        // process restricted to tribe creatures and generate error if needed
        foreach ($restrictedToTribeCreatures as $Creature => $Type) {
            $subTypes = explode(' ', $Type);
            $sameTribe = false;
            foreach ($subTypes as $type) {
                $type = trim($type);
                if ($type == '') {
                    continue;
                }
                if ($type == $tribeKey) {
                    $sameTribe = true;
                }
            }
            if (!$sameTribe) {
                $this->error[] = "$Creature can be used with its tribe(s) only";
            }
        }

        // if (($this->pure) && ($tribeKey != "Shapeshifter") && ($subTypeChangeling > 0)) {
        //    $this->error[] = "Changelings are only allowed in Shapeshifter decks";
        //}

        $bannedTribes = self::getTribesBanned();
        foreach ($bannedTribes as $bannedTribe) {
            if ($tribeKey == $bannedTribe) {
                $this->error[] = "Tribe $bannedTribe is banned for this event";
            }
        }

        // return tribe name and count
        $tribe = [];
        $tribe['name'] = $tribeKey;
        $tribe['count'] = $subTypeCount[$tribeKey];

        return $tribe;
    }

    public function isDeckTribalLegal(int $deckID): bool
    {
        $isLegal = true;
        $deck = new Deck($deckID);
        $tribe = self::getTribe($deckID);
        if ($deck->getCardCount($deck->maindeck_cards) > 60) {
            $tribeNumberToBeLegal = floor($deck->getCardCount($deck->maindeck_cards) / 3);
        } else {
            $tribeNumberToBeLegal = round($deck->getCardCount($deck->maindeck_cards) / 3);
        }

        if ($this->pure) {
            $creatures = $deck->getCreatureCards();
            $creaturesCount = $deck->getCardCount($creatures);
            if ($creaturesCount != $tribe['count']) {
                $this->error[] = "Pure Tribal setting doesn't allow for off-tribe creatures or
                                  Changelings (all the creatures in the deck must share at least one
                                  creature type)";
            }
        }

        if ($tribe['count'] < $tribeNumberToBeLegal) {
            $this->error[] = "Tribe {$tribe['name']} does not have enough members. $tribeNumberToBeLegal needed,
                               current count is {$tribe['count']}";
        }
        if (count($this->error) > 0) {
            $isLegal = false;
        }

        return $isLegal;
    }

    public function isDeckCommanderLegal(int $deckID): bool
    {
        $isLegal = true;
        $deck = new Deck($deckID);
        $commanderColors = [];
        $commanderCard = self::getCommanderCard($deck);

        if (is_null($commanderCard)) {
            $this->error[] = 'Cannot find a Commander in your deck. There must be a Legendary Creature on the sideboard to serve as the Commander.';

            return false;
        } else {
            $commanderColors = self::getCardColors($commanderCard);
        }

        foreach ($deck->maindeck_cards as $card => $amt) {
            $colors = self::getCardColors($card);
            foreach ($colors as $color => $num) {
                if ($num > 0) {
                    if ($commanderColors[$color] == 0) {
                        $isLegal = false;
                        $this->error[] = "Illegal card: $card. Card contains the color $color which does not match the Commander's Colors. The Commander was determined to be $commanderCard.";
                    }
                }
            }
        }

        return $isLegal;
    }

    /** @return array<string, int> */
    public static function getCardColors(string $card): array
    {
        $colors = [];
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT isw, isr, isg, isu, isb
                              FROM cards
                              WHERE name = ?');
        $stmt->bind_param('s', $card);
        $stmt->execute();
        $stmt->bind_result($colors['White'], $colors['Red'], $colors['Green'], $colors['Blue'], $colors['Black']);
        $stmt->fetch();
        $stmt->close();

        return $colors;
    }

    public static function getCommanderCard(Deck $deck): ?string
    {
        foreach ($deck->sideboard_cards as $card => $amt) {
            if (self::isCardLegendary($card)) {
                return $card;
            }
        }
        return null;
    }

    public static function isCardLegendary(string $card): bool
    {
        return count(Database::listResultSingleParam(
            "SELECT id FROM cards WHERE name = ? AND type LIKE '%Legendary%'",
            's',
            $card
        )) > 0;
    }

    public function isQuantityLegal(string $card, int $amt): bool
    {
        if ($amt <= 4) {
            return true;
        }

        if ($this->isCardBasic($card)) {
            return true;
        }

        if ($card == 'Seven Dwarves' && $amt <= 7) {
            return true;
        }

        return false;
    }

    public function isQuantityLegalAgainstMain(string $sideCard, int $sideAmt, string $mainCard, int $mainAmt): bool
    {
        // mainCard and sideCard don't match so is automatically legal
        // individual quantity check has already been done. We are only
        // interested in finding too many of the same card between the side and main
        if ($sideCard != $mainCard) {
            return true;
        }
        if (($sideAmt + $mainAmt) <= 4) {
            return true;
        }
        if ($sideCard == 'Seven Dwarves' && $sideAmt + $mainAmt <= 7) {
            return true;
        }
        if ($this->isCardBasic($sideCard)) {
            return true;
        }
        return false;
    }

    public function insertCardIntoBanlist(string $card): bool
    {
        $card = stripslashes($card);
        $card = normaliseCardName($card);
        $card = $this->getCardName($card);
        $cardID = $this->getCardID($card);
        if (is_null($cardID)) {
            return false; // card not found in database
        }

        if ($this->isCardOnBanList($card)) {
            return true;
        } elseif ($this->isCardOnLegalList($card)) {
            return false;
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO bans(card_name, card, format, allowed) VALUES(?, ?, ?, 0)');
            $stmt->bind_param('sds', $card, $cardID, $this->name);
            $stmt->execute() or exit($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function insertCardIntoLegallist(string $card): bool
    {
        $card = stripslashes($card);
        $card = normaliseCardName($card);
        $testcard = $this->getCardName($card);
        if (is_null($testcard)) {
            $testcard = $this->getCardNameFromPartialDFC($card);
        }
        $card = $testcard;
        $cardID = $this->getCardID($card);
        if (is_null($cardID)) {
            return false; // card not found in database
        }

        if ($this->isCardOnLegalList($card)) {
            return true;
        } elseif ($this->isCardOnBanList($card)) {
            return false;
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO bans(card_name, card, format, allowed) VALUES(?, ?, ?, 1)');
            $stmt->bind_param('sds', $card, $cardID, $this->name);
            $stmt->execute() or exit($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function insertCardIntoRestrictedlist(string $card): bool
    {
        $card = stripslashes($card);
        $card = normaliseCardName($card);
        $card = $this->getCardName($card);
        $cardID = $this->getCardID($card);
        if (is_null($cardID)) {
            return false; // card not found in database
        }

        if ($this->isCardOnRestrictedList($card)) {
            return true;
        } elseif ($this->isCardOnBanList($card) || $this->isCardOnLegalList($card)) {
            return false;
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO restricted(card_name, card, format, allowed) VALUES(?, ?, ?, 2)');
            $stmt->bind_param('sds', $card, $cardID, $this->name);
            $stmt->execute() or exit($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function insertCardIntoRestrictedToTribeList(string $card): bool
    {
        $card = stripslashes($card);
        $card = $this->getCardName($card);
        $cardID = $this->getCardID($card);
        if (is_null($cardID)) {
            return false; // card not found in database
        }

        if ($this->isCardOnBanList($card) || $this->isCardOnRestrictedToTribeList($card)) {
            return false; // card is already banned no need to restrict card
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO restrictedtotribe(card_name, card, format, allowed) VALUES(?, ?, ?, 1)');
            $stmt->bind_param('sds', $card, $cardID, $this->name);
            $stmt->execute() or exit($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function deleteCardFromBanlist(string $cardName): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM bans WHERE format = ? AND card_name = ? and allowed = 0');
        $stmt->bind_param('ss', $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteEntireBanlist(): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM bans WHERE format = ? AND allowed = 0');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteAllLegalSets(): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM setlegality WHERE format = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteAllBannedTribes(): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM tribe_bans WHERE format = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteCardFromLegallist(string $cardName): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM bans WHERE format = ? AND card_name = ? AND allowed = 1');
        $stmt->bind_param('ss', $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteEntireLegallist(): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM bans WHERE format = ? AND allowed = 1');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteCardFromRestrictedlist(string $cardName): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM restricted WHERE format = ? AND card_name = ?');
        $stmt->bind_param('ss', $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteCardFromRestrictedToTribeList(string $cardName): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM restrictedtotribe WHERE format = ? AND card_name = ?');
        $stmt->bind_param('ss', $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteEntireRestrictedlist(): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM restricted WHERE format = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteEntireRestrictedToTribeList(): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM restrictedtotribe WHERE format = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    private function getCardID(?string $cardname): ?int
    {
        if (is_null($cardname)) {
            return null;
        }
        // Honestly I can't think of a good reason why we would have to ban a specific card (ban by id number).
        // When you ban a card, don't you want to ban all versions of it? Not just one version?
        // so it makes more sense to ban by card name. But I will implement cardID's for now since that is how the
        // database was set up.
        return Database::singleResultSingleParam('SELECT id FROM cards WHERE name = ?', 's', $cardname);
    }

    public static function getCardName(string $cardname): ?string
    {
        // this is used to return the name of the card as it appears in the database
        // otherwise the ban list will have cards on it like rOnCoR, RONCOR, rONCOR, etc
        return Database::singleResultSingleParam('SELECT name FROM cards WHERE name = ?', 's', $cardname);
    }

    public static function getCardNameFromPartialDFC(string $cardname): ?string
    {
        // this is used to return the name of the card as it appears in the database
        // otherwise the ban list will have cards on it like rOnCoR, RONCOR, rONCOR, etc
        return Database::singleResultSingleParam('SELECT name FROM cards WHERE name LIKE ?', 's', $cardname . '/%');
    }

    public function insertNewLegalSet(string $cardsetName): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO setlegality(format, cardset)VALUES(?, ?)');
        $stmt->bind_param('ss', $this->name, $cardsetName);
        $stmt->execute() or exit($stmt->error);
        $stmt->close();

        return true;
    }

    public function insertNewSubTypeBan(string $subTypeBanned): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO subtype_bans(name, format, allowed) VALUES(?, ?, 0)');
        $stmt->bind_param('ss', $subTypeBanned, $this->name);
        $stmt->execute() or exit($stmt->error);
        $stmt->close();

        return true;
    }

    public function insertNewTribeBan(string $tribeBanned): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO tribe_bans(name, format, allowed) VALUES(?, ?, 0)');
        $stmt->bind_param('ss', $tribeBanned, $this->name);
        $stmt->execute() or exit($stmt->error);
        $stmt->close();

        return true;
    }

    public function banAllTribes(): void
    {
        $this->deleteAllBannedTribes();
        $tribes = self::getTribesList();

        foreach ($tribes as $nextBan) {
            $this->insertNewTribeBan($nextBan);
        }
    }

    public function deleteLegalCardSet(string $cardsetName): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM setlegality WHERE format = ? AND cardset = ?');
        $stmt->bind_param('ss', $this->name, $cardsetName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteSubTypeBan(string $subTypeName): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM subtype_bans WHERE format = ? AND name = ?');
        $stmt->bind_param('ss', $this->name, $subTypeName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteTribeBan(string $tribeName): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM tribe_bans WHERE format = ? AND name = ?');
        $stmt->bind_param('ss', $this->name, $tribeName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }
}
