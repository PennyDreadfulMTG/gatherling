<?php

class Format
{
    public $name;
    public $description;
    public $type;        // who has access to filter: public, private, system
    public $series_name; // filter owner
    public $priority;
    public $new;

    // card set construction
    public $card_banlist = [];
    public $card_restrictedlist = [];
    public $card_legallist = [];
    public $legal_sets = [];
    public $eternal;
    public $modern;
    public $standard;

    // deck construction switches
    public $singleton;
    public $commander;
    public $planechase;
    public $vanguard;
    public $prismatic;
    public $tribal;
    public $pure;
    public $underdog;

    // rarities allowed switches
    public $allow_commons;
    public $allow_uncommons;
    public $allow_rares;
    public $allow_mythics;
    public $allow_timeshifted;

    // deck limits
    public $min_main_cards_allowed;
    public $max_main_cards_allowed;
    public $min_side_cards_allowed;
    public $max_side_cards_allowed;

    // Meta Formats
    public $is_meta_format;
    public $sub_formats = [];

    private $error = [];

    public function __construct($name)
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
            $this->sub_formats = [];
            $this->new = true;

            return;
        }

        if ($this->new) {
            $this->new = false;

            return $this->insertNewFormat();
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT name, description, type, series_name, singleton, commander, planechase, vanguard,
                                         prismatic, tribal, pure, underdog, allow_commons, allow_uncommons, allow_rares, allow_mythics,
                                         allow_timeshifted, priority, min_main_cards_allowed, max_main_cards_allowed,
                                         min_side_cards_allowed, max_side_cards_allowed, eternal, modern, `standard`, is_meta_format
                                  FROM formats
                                  WHERE name = ?');
            $stmt or die($db->error);
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $stmt->bind_result($this->name, $this->description, $this->type, $this->series_name, $this->singleton,
                               $this->commander, $this->planechase, $this->vanguard, $this->prismatic, $this->tribal,
                               $this->pure, $this->underdog, $this->allow_commons, $this->allow_uncommons, $this->allow_rares,
                               $this->allow_mythics,$this->allow_timeshifted, $this->priority, $this->min_main_cards_allowed,
                               $this->max_main_cards_allowed, $this->min_side_cards_allowed, $this->max_side_cards_allowed,
                               $this->eternal, $this->modern, $this->standard, $this->is_meta_format);
            if ($stmt->fetch() == null) {
                throw new Exception('Format '.$name.' not found in DB');
            }
            $stmt->close();
            $this->card_banlist = $this->getBanList();
            $this->card_legallist = $this->getLegalList();
            $this->card_restrictedlist = $this->getRestrictedList();
            $this->legal_sets = $this->getLegalCardsets();
            $this->sub_formats = $this->getSubFormats();
        }
    }

    public static function constructTribes($set = 'All')
    {
        // adds tribe types to tribes table in database
        // if no set is specified, uses all sets from cardsets table

        $cardSets = [];
        if ($set == 'All') {
            $cardSets = Database::list_result('SELECT name FROM cardsets');
        } else {
            $cardSets[] = $set;
        }

        foreach ($cardSets as $cardSet) {
            echo "Processing $cardSet<br />";
            $cardTypes = Database::list_result_single_param("SELECT type
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
                        echo "New Tribe Found! Inserting: $subtype<br />";
                        $db = Database::getConnection();
                        $stmt = $db->prepare('INSERT INTO tribes(name) VALUES(?)');
                        $stmt->bind_param('s', $subtype);
                        $stmt->execute() or die($stmt->error);
                        $stmt->close();
                    }
                }
            }
        }
    }

    public static function isTribeTypeInDatabase($type)
    {
        $tribe = Database::single_result_single_param('SELECT name
                                           FROM tribes
                                           WHERE name = ?', 's', $type);
        if (strcasecmp($tribe, $type) == 0) {
            return true;
        }

        return false;
    }

    public static function doesFormatExist($format)
    {
        $success = false;
        $formatName = [];
        $formatName = Database::single_result_single_param('SELECT name FROM formats WHERE name = ?', 's', $format);
        if ($formatName) {
            $success = true;
        }

        return $success;
    }

    private function insertNewFormat()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO formats(name, description, type, series_name, singleton, commander, planechase,
                                                  vanguard, prismatic, tribal, pure, underdog, allow_commons, allow_uncommons, allow_rares,
                                                  allow_mythics, allow_timeshifted, priority, min_main_cards_allowed,
                                                  max_main_cards_allowed, min_side_cards_allowed, max_side_cards_allowed,
                                                  eternal, modern, `standard`, is_meta_format)
                              VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssdddddddddddddddddddddd',
                          $this->name, $this->description, $this->type, $this->series_name, $this->singleton,
                          $this->commander, $this->planechase, $this->vanguard, $this->prismatic, $this->tribal,
                          $this->pure, $this->underdog, $this->allow_commons, $this->allow_uncommons, $this->allow_rares,
                          $this->allow_mythics, $this->allow_timeshifted, $this->priority, $this->min_main_cards_allowed,
                          $this->max_main_cards_allowed, $this->min_side_cards_allowed, $this->max_side_cards_allowed,
                          $this->eternal, $this->modern, $this->standard, $this->is_meta_format);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

        return true;
    }

    public function saveAndDeleteAuthorization($playerName)
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

    public function viewAuthorization($playerName)
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

    public function save()
    {
        if ($this->new) {
            $this->new = false;

            return $this->insertNewFormat();
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare('UPDATE formats
                                  SET description = ?, type = ?, series_name = ?, singleton = ?, commander = ?,
                                  planechase = ?, vanguard = ?, prismatic = ?, tribal = ?, pure = ?, underdog = ?, allow_commons = ?, allow_uncommons = ?, allow_rares = ?,
                                  allow_mythics = ?, allow_timeshifted = ?, priority = ?, min_main_cards_allowed = ?,
                                  max_main_cards_allowed = ?, min_side_cards_allowed = ?, max_side_cards_allowed = ?,
                                  eternal = ?, modern = ?, `standard` = ?, is_meta_format = ?
                                  WHERE name = ?');
            $stmt or die($db->error);
            $stmt->bind_param('sssdddddddddddddddddddddds',
                              $this->description, $this->type, $this->series_name, $this->singleton, $this->commander,
                              $this->planechase, $this->vanguard, $this->prismatic, $this->tribal, $this->pure, $this->underdog,
                              $this->allow_commons, $this->allow_uncommons, $this->allow_rares, $this->allow_mythics,
                              $this->allow_timeshifted, $this->priority, $this->min_main_cards_allowed,
                              $this->max_main_cards_allowed, $this->min_side_cards_allowed, $this->max_side_cards_allowed,
                              $this->eternal, $this->modern, $this->standard, $this->is_meta_format,
                              $this->name);
            $stmt->execute() or die($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function saveAs($oldName = '')
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

    public function rename($oldName = '')
    {
        // $this->name, $this->type, and $this->series_name of the new format should all be specified before calling this function
        $success = $this->saveAs($oldName);
        if ($oldName != '' && $success) {
            $oldFormat = new self($oldName);
            $success = $oldFormat->delete();
        }

        return $success;
    }

    public function delete()
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

    public function noFormatLoaded()
    {
        return ($this->name == '') || (is_null($this->name));
    }

    public function getLegalCardsets()
    {
        if ($this->eternal) {
            return Database::list_result('SELECT name FROM cardsets');
        }
        if ($this->modern) {
            return Database::list_result('SELECT name FROM cardsets WHERE modern_legal = 1');
        }
        if ($this->standard) {
            return Database::list_result('SELECT name FROM cardsets WHERE standard_legal = 1');
        }

        return database::list_result_single_param('SELECT cardset FROM setlegality WHERE format = ?', 's', $this->name);
    }

    public function getSubFormats()
    {
        if (!$this->is_meta_format) {
            return [];
        }

        return Database::list_result_single_param('SELECT childformat FROM subformats WHERE parentformat = ?', 's', $this->name);
    }

    public function getLegalCard($cardName)
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

    public static function getSystemFormats()
    {
        return Database::list_result_single_param('SELECT name FROM formats WHERE type = ?', 's', 'System');
    }

    public static function getPublicFormats()
    {
        return Database::list_result_single_param('SELECT name FROM formats WHERE type = ?', 's', 'Public');
    }

    public static function getPrivateFormats($seriesName)
    {
        return Database::list_result_double_param('SELECT name FROM formats WHERE type = ? AND series_name = ?',
                                                  'ss', 'Private', $seriesName);
    }

    public static function getAllFormats()
    {
        return Database::list_result('SELECT name FROM formats');
    }

    public function getCoreCardsets()
    {
        $legalSets = Database::list_result_single_param('SELECT cardset FROM setlegality WHERE format = ?', 's', $this->name);

        $legalCoreSets = [];
        foreach ($legalSets as $legalSet) {
            $setType = Database::single_result_single_param('SELECT type FROM cardsets WHERE name = ?', 's', $legalSet);
            if (strcmp($setType, 'Core') == 0) {
                $legalCoreSets[] = $legalSet;
            }
        }

        return $legalCoreSets;
    }

    public function getBlockCardsets()
    {
        $legalSets = Database::list_result_single_param('SELECT cardset FROM setlegality WHERE format = ?', 's', $this->name);

        $legalBlockSets = [];
        foreach ($legalSets as $legalSet) {
            $setType = Database::single_result_single_param('SELECT type FROM cardsets WHERE name = ?', 's', $legalSet);
            if (strcmp($setType, 'Block') == 0) {
                $legalBlockSets[] = $legalSet;
            }
        }

        return $legalBlockSets;
    }

    public function getExtraCardsets()
    {
        $legalSets = Database::list_result_single_param('SELECT cardset FROM setlegality WHERE format = ?', 's', $this->name);

        $legalExtraSets = [];
        foreach ($legalSets as $legalSet) {
            $setType = Database::single_result_single_param('SELECT type FROM cardsets WHERE name = ?', 's', $legalSet);
            if (strcmp($setType, 'Extra') == 0) {
                $legalExtraSets[] = $legalSet;
            }
        }

        return $legalExtraSets;
    }

    public function getBanList()
    {
        return Database::list_result_single_param('SELECT card_name
                                                   FROM bans
                                                   WHERE format = ?
                                                   AND allowed = 0
                                                   ORDER BY card_name', 's', $this->name);
    }

    public function getTribesBanned()
    {
        return Database::list_result_single_param('SELECT name
                                                   FROM tribe_bans
                                                   WHERE format = ?
                                                   AND allowed = 0
                                                   ORDER BY name', 's', $this->name);
    }

    public function getSubTypesBanned()
    {
        return Database::list_result_single_param('SELECT name
                                                   FROM subtype_bans
                                                   WHERE format = ?
                                                   AND allowed = 0
                                                   ORDER BY name', 's', $this->name);
    }

    public function getLegalList()
    {
        return Database::list_result_single_param('SELECT card_name
                                                   FROM bans
                                                   WHERE format = ? AND allowed = 1
                                                   ORDER BY card_name',
                                                  's', $this->name);
    }

    public function getTribesAllowed()
    {
        return Database::list_result_single_param('SELECT name
                                                   FROM tribe_bans
                                                   WHERE format = ? AND allowed = 1
                                                   ORDER BY name',
                                                  's', $this->name);
    }

    public function getRestrictedList()
    {
        return Database::list_result_single_param('SELECT card_name
                                                   FROM restricted
                                                   WHERE format = ?
                                                   ORDER BY card_name',
                                                  's', $this->name);
    }

    public function getRestrictedTotribeList()
    {
        return Database::list_result_single_param('SELECT card_name
                                                   FROM restrictedtotribe
                                                   WHERE format = ? AND allowed = 1
                                                   ORDER BY card_name',
                                                  's', $this->name);
    }

    public function isError()
    {
        return count($this->errors) > 0;
    }

    public function getErrors()
    {
        $currentErrors = $this->error;
        $this->error = [];

        return $currentErrors;
    }

    public function getFormats()
    {
        return Database::list_result('SELECT name FROM formats');
    }

    public static function getTribesList()
    {
        return Database::list_result('SELECT name FROM tribes ORDER BY name');
    }

    public function isCardLegalByRarity($cardName)
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
                    die("Unexpected rarity {$rarity}!");
                    break;
            }
        }

        return false;
    }

    public function isCardOnBanList($card)
    {
        return count(Database::list_result_double_param('SELECT card_name
                                                         FROM bans
                                                         WHERE (format = ?
                                                         AND card_name = ?
                                                         AND allowed = 0)',
                                                         'ss', $this->name, $card)) > 0;
    }

    public function isCardOnLegalList($card)
    {
        return count(Database::list_result_double_param('SELECT card_name
                                                         FROM bans
                                                         WHERE (format = ?
                                                         AND card_name = ?
                                                         AND allowed = 1)',
                                                         'ss', $this->name, $card)) > 0;
    }

    public function isCardOnRestrictedList($card)
    {
        return count(Database::list_result_double_param('SELECT card_name
                                                         FROM restricted
                                                         WHERE (format = ?
                                                         AND card_name = ?)',
                                                         'ss', $this->name, $card)) > 0;
    }

    public function isCardOnRestrictedToTribeList($card)
    {
        return count(Database::list_result_double_param('SELECT card_name
                                                         FROM restrictedtotribe
                                                         WHERE (format = ?
                                                         AND card_name = ?)',
                                                         'ss', $this->name, $card)) > 0;
    }

    public function isCardSetLegal($setName)
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

    public function isCardBasic($card)
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

    public function isCardSingletonLegal($card, $amt)
    {
        if ($amt == 1) {
            return true;
        }

        if ($this->isCardBasic($card)) {
            return true;
        }

        return false;
    }

    public static function getCardType($card)
    {
        // Selecting card type for card = $card
        $cardType = Database::single_result_single_param('SELECT type
                                                          FROM cards
                                                          WHERE name = ?', 's', $card);

        return $cardType;
    }

    public static function removeTypeCrap($typeString)
    {
        // leave only the tribal sub types
        $typeString = str_replace('Tribal ', '', $typeString);
        $typeString = str_replace('Creature - ', '', $typeString);
        $typeString = str_replace('Artifact ', '', $typeString);
        $typeString = str_replace('Artifact - ', '', $typeString);
        $typeString = str_replace('Instant - ', '', $typeString);
        $typeString = str_replace('Enchantment - ', '', $typeString);
        $typeString = str_replace('Sorcery - ', '', $typeString);
        $typeString = str_replace('Legendary ', '', $typeString);

        return $typeString;
    }

    public static function isChangeling($card)
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

    public function getTribe($deckID)
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

        foreach ($subTypeCount as $type=>$amt) {
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
                $frequency = Database::single_result("SELECT Count(*) FROM cards WHERE type LIKE '%{$Type}%'");
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
                $frequency = Database::single_result("SELECT Count(*) FROM cards WHERE type LIKE '%{$underdogKey}%'");
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
                $frequency = Database::single_result("SELECT Count(*) FROM cards WHERE type LIKE '%{$Type}%'");
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

    public function isDeckTribalLegal($deckID)
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

    public function isDeckCommanderLegal($deckID)
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

    public static function getCardColors($card)
    {
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

    public static function getCommanderCard($deck)
    {
        foreach ($deck->sideboard_cards as $card => $amt) {
            if (self::isCardLegendary($card)) {
                return $card;
            }
        }
    }

    public static function isCardLegendary($card)
    {
        return count(Database::list_result_single_param("SELECT id FROM cards WHERE name = ? AND type LIKE '%Legendary%'",
                                                         's', $card)) > 0;
    }

    public function isQuantityLegal($card, $amt)
    {
        if ($amt <= 4) {
            return true;
        }

        if ($this->isCardBasic($card)) {
            return true;
        }

        return false;
    }

    public function isQuantityLegalAgainstMain($sideCard, $sideAmt, $mainCard, $mainAmt)
    {
        if ($sideCard == $mainCard) {
            if (($sideAmt + $mainAmt) <= 4) {
                return true;
            }

            if ($this->isCardBasic($sideCard)) {
                return true;
            }
        } else {
            return true; // mainCard and sideCard don't match so is automatically legal
                         // individual quantity check has already been done. We are only
                         // interested in finding too many of the same card between the side and main
        }

        return false;
    }

    public function insertCardIntoBanlist($card)
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
            $stmt->execute() or die($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function insertCardIntoLegallist($card)
    {
        $card = stripslashes($card);
        $card = normaliseCardName($card);
        $card = $this->getCardName($card);
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
            $stmt->execute() or die($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function insertCardIntoRestrictedlist($card)
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
            $stmt->execute() or die($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function insertCardIntoRestrictedToTribeList($card)
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
            $stmt->execute() or die($stmt->error);
            $stmt->close();

            return true;
        }
    }

    public function deleteCardFromBanlist($cardName)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM bans WHERE format = ? AND card_name = ? and allowed = 0');
        $stmt->bind_param('ss', $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteEntireBanlist()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM bans WHERE format = ? AND allowed = 0');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteAllLegalSets()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM setlegality WHERE format = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteAllBannedTribes()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM tribe_bans WHERE format = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteCardFromLegallist($cardName)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM bans WHERE format = ? AND card_name = ? AND allowed = 1');
        $stmt->bind_param('ss', $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteEntireLegallist()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM bans WHERE format = ? AND allowed = 1');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteCardFromRestrictedlist($cardName)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM restricted WHERE format = ? AND card_name = ?');
        $stmt->bind_param('ss', $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteCardFromRestrictedToTribeList($cardName)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM restrictedtotribe WHERE format = ? AND card_name = ?');
        $stmt->bind_param('ss', $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteEntireRestrictedlist()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM restricted WHERE format = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteEntireRestrictedToTribeList()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM restrictedtotribe WHERE format = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    private function getCardID($cardname)
    {
        // Honestly I can't think of a good reason why we would have to ban a specific card (ban by id number).
        // When you ban a card, don't you want to ban all versions of it? Not just one version?
        // so it makes more sense to ban by card name. But I will implement cardID's for now since that is how the
        // database was set up.
        return Database::single_result_single_param('SELECT id FROM cards WHERE name = ?', 's', $cardname);
    }

    public static function getCardName($cardname)
    {
        // this is used to return the name of the card as it appears in the database
        // otherwise the ban list will have cards on it like rOnCoR, RONCOR, rONCOR, etc
        return Database::single_result_single_param('SELECT name FROM cards WHERE name = ?', 's', $cardname);
    }

    public function insertNewLegalSet($cardsetName)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO setlegality(format, cardset)VALUES(?, ?)');
        $stmt->bind_param('ss', $this->name, $cardsetName);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

        return true;
    }

    public function insertNewSubTypeBan($subTypeBanned)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO subtype_bans(name, format, allowed) VALUES(?, ?, 0)');
        $stmt->bind_param('ss', $subTypeBanned, $this->name);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

        return true;
    }

    public function insertNewTribeBan($tribeBanned)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO tribe_bans(name, format, allowed) VALUES(?, ?, 0)');
        $stmt->bind_param('ss', $tribeBanned, $this->name);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

        return true;
    }

    public function insertSubFormat($subformat)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO subformats(parentformat, childformat) VALUES(?, ?)');
        $stmt->bind_param('ss', $this->name, $subformat);
        $stmt->execute() or die($stmt->error);
        $stmt->close();

        return true;
    }

    public function banAllTribes()
    {
        $this->deleteAllBannedTribes();
        $tribes = self::getTribesList();

        foreach ($tribes as $nextBan) {
            $this->insertNewTribeBan($nextBan);
        }
    }

    public function deleteLegalCardSet($cardsetName)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM setlegality WHERE format = ? AND cardset = ?');
        $stmt->bind_param('ss', $this->name, $cardsetName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteSubTypeBan($subTypeName)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM subtype_bans WHERE format = ? AND name = ?');
        $stmt->bind_param('ss', $this->name, $subTypeName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        return $removed;
    }

    public function deleteTribeBan($tribeName)
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
