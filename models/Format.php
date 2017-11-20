<?PHP

class Format {
    
    public $name;
    public $description;
    public $type;        // who has access to filter: public, private, system
    public $series_name; // filter owner
    public $priority;
    public $new;
    
    // card set construction
    public $card_banlist = array();
    public $card_restrictedlist = array();
    public $card_legallist = array ();
    public $legal_sets = array(); 

    // deck construction switches
    public $singleton;
    public $commander;
    public $planechase;
    public $vanguard;
    public $prismatic;
    
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
    
    private $error = array();   
    
    function __construct($name) {
        if ($name == "") {
            $this->name = ""; 
            $this->description = "";
            $this->type = "";        
            $this->series_name = ""; 
            $this->priority = 1;
            $this->card_banlist = array();
            $this->card_legallist = array();
            $this->card_restrictedlist = array();
            $this->legal_sets = array(); 
            $this->singleton = 0;
            $this->commander = 0;
            $this->planechase = 0;
            $this->vanguard = 0;
            $this->prismatic = 0;
            $this->allow_commons = 0;
            $this->allow_uncommons = 0;
            $this->allow_rares = 0;
            $this->allow_mythics = 0; 
            $this->allow_timeshifted = 0;
            $this->min_main_cards_allowed = 0;
            $this->max_main_cards_allowed = 0;
            $this->min_side_cards_allowed = 0;
            $this->max_side_cards_allowed = 0;
            $this->new = true;
            return; 
        } 

        if ($this->new) {
            $this->new = false;
            return $this->insertNewFormat();
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT name, description, type, series_name, singleton, commander, planechase, vanguard, 
                                         prismatic, allow_commons, allow_uncommons, allow_rares, allow_mythics, allow_timeshifted, 
                                         priority, min_main_cards_allowed, max_main_cards_allowed, min_side_cards_allowed,
                                         max_side_cards_allowed
                                  FROM formats 
                                  WHERE name = ?");
            if (!$stmt) {
                die($db->error);
            }
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->bind_result($this->name, $this->description, $this->type, $this->series_name, $this->singleton, 
                               $this->commander, $this->planechase, $this->vanguard, $this->prismatic, $this->allow_commons, 
                               $this->allow_uncommons, $this->allow_rares, $this->allow_mythics, $this->allow_timeshifted, 
                               $this->priority, $this->min_main_cards_allowed, $this->max_main_cards_allowed,
                               $this->min_side_cards_allowed, $this->max_side_cards_allowed);
            if ($stmt->fetch() == NULL) {
                throw new Exception('Format '. $name .' not found in DB');
            }
            $stmt->close();
            $this->card_banlist = $this->getBanList();
            $this->card_legallist = $this->getLegalList();
            $this->card_restrictedlist = $this->getRestrictedList();
            $this->legal_sets = $this->getLegalCardsets();
        }
    }

    static public function doesFormatExist($format) {
        $success = false;
        $formatName = array();
        $formatName = Database::single_result_single_param("SELECT name FROM formats WHERE name = ?", "s", $format);
        if (count($formatName)) {
            $success = true;
        }
        return $success;
    }
    
    private function insertNewFormat() {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO formats(name, description, type, series_name, singleton, commander, planechase, 
                                                  vanguard, prismatic, allow_commons, allow_uncommons, allow_rares, allow_mythics, 
                                                  allow_timeshifted, priority, min_main_cards_allowed, max_main_cards_allowed,
                                                  min_side_cards_allowed, max_side_cards_allowed)
                              VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssddddddddddddddd", 
                          $this->name, $this->description, $this->type, $this->series_name, $this->singleton, 
                          $this->commander, $this->planechase, $this->vanguard, $this->prismatic, $this->allow_commons, 
                          $this->allow_uncommons, $this->allow_rares, $this->allow_mythics, $this->allow_timeshifted, 
                          $this->priority, $this->min_main_cards_allowed, $this->max_main_cards_allowed,
                          $this->min_side_cards_allowed, $this->max_side_cards_allowed);
        $stmt->execute() or die($stmt->error);
        $stmt->close();
        return true;        
    }
    
    public function saveAndDeleteAuthorization($playerName) {
        // this will be used to determine if the save and delete buttons will appear on the format editor
        // there are 3 different format types: system, public, private
        
        $player = new Player($playerName); // to access isOrganizer and isSuper functions
        $authorized = false;
        
        switch ($this->type) {
            case "System":
                 // Only supers can save or delete system formats
                if($player->isSuper()) {$authorized = true;}
                break;
            case "Public":
                // Only Series Organizer of the series that created the format
                // and Supers can save or delete Public formats
                if($player->isOrganizer($this->series_name) || $player->isSuper()) {$athorized = true;}
                break;
            case "Private":
                // The only difference in access between a public and private format is that private formats can be
                // viewed only by the series organizers of the series it belongs to
                // the save and delete access is the same
                if($player->isOrganizer($this->series_name) || $player->isSuper()) {$athorized = true;}
                break;
        }
        return $authorized;
    }
    
    public function viewAuthorization($playerName) {
        // this will be used to determine if a format will appear in the drop down to load in the format filter
        // there are 3 different format types: system, public, private
        
        $player = new Player($playerName); // to access isOrganizer and isSuper functions
        $authorized = false;
        
        switch ($this->type) {
            case "System":
                $authorized = true; // anyone can view a system format
                break;
            case "Public":
                $athorized = true; // anyone can view a public format
                break;
            case "Private":
                // Only supers and organizers can view private formats
                if($player->isOrganizer($this->series_name) || $player->isSuper()) {$athorized = true;}
                break;
        }
        return $authorized;
    }
    
    public function save() {
        if ($this->new) {
            $this->new = false;
            return $this->insertNewFormat();
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE formats 
                                  SET description = ?, type = ?, series_name = ?, singleton = ?, commander = ?, 
                                  planechase = ?, vanguard = ?, prismatic = ?, allow_commons = ?, allow_uncommons = ?, allow_rares = ?, 
                                  allow_mythics = ?, allow_timeshifted = ?, priority = ?, min_main_cards_allowed = ?, 
                                  max_main_cards_allowed = ?, min_side_cards_allowed = ?, max_side_cards_allowed = ?
                                  WHERE name = ?");
            $stmt or die($db->error);
            $stmt->bind_param("sssddddddddddddddds", 
                              $this->description, $this->type, $this->series_name, $this->singleton, $this->commander, 
                              $this->planechase, $this->vanguard, $this->prismatic, $this->allow_commons, $this->allow_uncommons, 
                              $this->allow_rares, $this->allow_mythics, $this->allow_timeshifted, $this->priority, 
                              $this->min_main_cards_allowed, $this->max_main_cards_allowed, $this->min_side_cards_allowed, 
                              $this->max_side_cards_allowed, $this->name);
            $stmt->execute() or die($stmt->error);
            $stmt->close(); 
            return true;
        }
    }
    
    public function saveAs($oldName = "") {
        // name, type, and series_name should all be specified before calling this function
        $success = $this->insertNewFormat();
        if($oldName != "") {
            $oldFormat = new Format($oldName);
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
            $this->priority = $oldFormat->priority;
            $this->description = $oldFormat->description;
            $this->min_main_cards_allowed = $oldFormat->min_main_cards_allowed;
            $this->max_main_cards_allowed = $oldFormat->max_main_cards_allowed;
            $this->min_side_cards_allowed = $oldFormat->min_side_cards_allowed;
            $this->max_side_cards_allowed = $oldFormat->max_side_cards_allowed;
            $this->new = false;
            $success = $this->save();
            if (!$success) {return false;}
            
            foreach($oldFormat->card_banlist as $bannedCard) {
                $this->insertCardIntoBanlist($bannedCard);
            }
            
            foreach($oldFormat->card_restrictedlist as $restrictedCard) {
                $this->insertCardIntoRestrictedlist($restrictedCard);
            }
            
            foreach($oldFormat->card_legallist as $legalCard) {
                $this->insertCardIntoLegallist($legalCard);
            }
            
            foreach($oldFormat->legal_sets as $legalset) {
                $this->insertNewLegalSet($legalset);
            }            
        }
        return $success;
    }
    
    public function rename($oldName = "") {
    // $this->name, $this->type, and $this->series_name of the new format should all be specified before calling this function
        $success = $this->saveAs($oldName);
        if($oldName != "" && $success) {
            $oldFormat = new Format($oldName);
            $success = $oldFormat->delete();
        }
        return $success;
    }
    
    public function delete() {
        $success = $this->deleteEntireLegallist();
        $success = $this->deleteEntireBanlist();
        $success = $this->deleteEntireRestrictedlist();
        $success = $this->deleteAllLegalSets();
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM formats WHERE name = ? AND series_name = ?");
        $stmt->bind_param("ss", $this->name, $this->series_name);
        $stmt->execute();
        $success = $stmt->affected_rows > 0;
        $stmt->close();  
        return $success;
    }
    
    public function load($formatName) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT name, description, type, series_name, singleton, commander, planechase, vanguard, 
                                     prismatic, allow_commons, allow_uncommons, allow_rares, allow_mythics, allow_timeshifted, 
                                     priority, min_main_cards_allowed, max_main_cards_allowed, min_side_cards_allowed, 
                                     max_side_cards_allowed
                              FROM formats 
                              WHERE name = ?");
        if (!$stmt) {
            die($db->error);
        }
        $stmt->bind_param("s", $formatName);
        $stmt->execute();
        $stmt->bind_result($this->name, $this->description, $this->type, $this->series_name, $this->singleton, 
                           $this->commander, $this->planechase, $this->vanguard, $this->prismatic, $this->allow_commons, 
                           $this->allow_uncommons, $this->allow_rares, $this->allow_mythics, $this->allow_timeshifted, 
                           $this->priority, $this->min_main_cards_allowed, $this->max_main_cards_allowed, 
                           $this->min_side_cards_allowed, $this->max_side_cards_allowed);
        if ($stmt->fetch() == NULL) {
            $this->error[] = "Format not found!";
            return false;
        }
        return true;
    }
    
    public function noFormatLoaded() {
        return (($this->name == "") || (is_null($this->name)));
    }
    
    public function getLegalCardsets() {
        return database::list_result_single_param("SELECT cardset FROM setlegality WHERE format = ?", "s", $this->name);
    }
    
    public function getLegalCard($cardName) {
        $db = Database::getConnection(); 
        $stmt = $db->prepare("SELECT id, name FROM cards WHERE name = ? AND cardset = ?");
        $cardar = array();

        foreach($this->legal_sets as $setName) {
            $stmt->bind_param("ss", $cardName, $setName);
            $stmt->execute(); 
            $stmt->bind_result($cardar['id'], $cardar['name']); 
            if (is_null($stmt->fetch())) {
                $cardar = NULL;
            } else {
                break; // we only need to know that the card exists in the legal card sets once
            } 
        }
        $stmt->close();       
        return $cardar; 
    }
    
    static public function getSystemFormats() {
        return Database::list_result_single_param("SELECT name FROM formats WHERE type = ?", "s", "System");
    }
    
    static public function getPublicFormats() {
        return Database::list_result_single_param("SELECT name FROM formats WHERE type = ?", "s", "Public");
    }
    
    static public function getPrivateFormats($seriesName) {
        return Database::list_result_double_param("SELECT name FROM formats WHERE type = ? AND series_name = ?", 
                                                  "ss", "Private", $seriesName);
    }
    
    static public function getAllFormats() {
        return Database::list_result("SELECT name FROM formats");
    }
    
    public function getCoreCardsets() {
        $legalSets = Database::list_result_single_param("SELECT cardset FROM setlegality WHERE format = ?", "s", $this->name);
        
        $legalCoreSets = array();
        foreach($legalSets as $legalSet) {
            $setType = Database::single_result_single_param("SELECT type FROM cardsets WHERE name = ?", "s", $legalSet);
            if (strcmp($setType, "Core") == 0) {
                $legalCoreSets[] = $legalSet;
            }
        }
        return $legalCoreSets;
    }
    
    public function getBlockCardsets() {
        $legalSets = Database::list_result_single_param("SELECT cardset FROM setlegality WHERE format = ?", "s", $this->name);
        
        $legalBlockSets = array();
        foreach($legalSets as $legalSet) {
            $setType = Database::single_result_single_param("SELECT type FROM cardsets WHERE name = ?", "s", $legalSet);
            if (strcmp($setType, "Block") == 0) {
                $legalBlockSets[] = $legalSet;
            }
        }
        return $legalBlockSets;
    }
    
    public function getExtraCardsets() {
        $legalSets = Database::list_result_single_param("SELECT cardset FROM setlegality WHERE format = ?", "s", $this->name);
        
        $legalExtraSets = array();
        foreach($legalSets as $legalSet) {
            $setType = Database::single_result_single_param("SELECT type FROM cardsets WHERE name = ?", "s", $legalSet);
            if (strcmp($setType, "Extra") == 0) {
                $legalExtraSets[] = $legalSet;
            }
        }
        return $legalExtraSets;
    }
    
    public function getBanList() {
        return Database::list_result_single_param("SELECT card_name 
                                                   FROM bans 
                                                   WHERE format = ? 
                                                   AND allowed = 0", "s", $this->name);
    }
    
    public function getLegalList() {
        return Database::list_result_single_param("SELECT card_name 
                                                   FROM bans 
                                                   WHERE format = ? AND allowed = 1", 
                                                  "s", $this->name);
    }
    
    public function getRestrictedList() {
        return Database::list_result_single_param("SELECT card_name 
                                                   FROM restricted 
                                                   WHERE format = ?", 
                                                  "s", $this->name);        
    }

    public function isError() {
        return count($this->errors) > 0;
    }
    
    public function getErrors() {
        $currentErrors = $this->error;
        $this->error = array();
        return $currentErrors;
    }

    public function getFormats() {
        return Database::list_result("SELECT name FROM formats");
    }
    
    public function isCardLegalByRarity($cardName) {
        $db = Database::getConnection(); 
        $stmt = $db->prepare("SELECT rarity FROM cards WHERE name = ? AND cardset = ?");
        $isLegal = false;
        $cardRarities = array();        

        foreach($this->legal_sets as $setName) {
            $stmt->bind_param("ss", $cardName, $setName);
            $stmt->execute(); 
            $stmt->bind_result($result);
            if ($stmt->fetch()) {
                $cardRarities[] = $result;
            }
        }
        $stmt->close();       

        foreach($cardRarities as $rarity) {
            switch($rarity) {
                case "Land":
                case "Basic Land":
                    $isLegal = true;
                    break;
                case "Common":
                    if($this->allow_commons == 1){$isLegal = true;}  
                    break;
                case "Uncommon":
                    if($this->allow_uncommons == 1){$isLegal = true;}  
                    break;
                case "Rare":
                    if($this->allow_rares == 1){$isLegal = true;}  
                    break;
                case "Mythic Rare":
                    if($this->allow_mythics == 1){$isLegal = true;}  
                    break;
                case "Timeshifted":
                    if($this->allow_timeshifted == 1) {$isLegal = true;}
                    break;
                case "Special":
                    if($this->vanguard == 1) {$isLegal = true;}
                    break;
                default:
                    die("Unexpected rarity {$rarity}!");
                    break;
            }
        }
        return $isLegal;
    }
    
    public function isCardOnBanList($card) {
        return count(Database::list_result_double_param("SELECT card_name 
                                                         FROM bans 
                                                         WHERE (format = ? 
                                                         AND card_name = ?
                                                         AND allowed = 0)", 
                                                         "ss", $this->name, $card)) > 0;
    }
    
    public function isCardOnLegalList($card) {
        return count(Database::list_result_double_param("SELECT card_name 
                                                         FROM bans 
                                                         WHERE (format = ? 
                                                         AND card_name = ?
                                                         AND allowed = 1)", 
                                                         "ss", $this->name, $card)) > 0;
    }
    
    public function isCardOnRestrictedList($card) {
        return count(Database::list_result_double_param("SELECT card_name 
                                                         FROM restricted 
                                                         WHERE (format = ? 
                                                         AND card_name = ?)", 
                                                         "ss", $this->name, $card)) > 0;
    }
    
    public function isCardSetLegal($setName) {
        $legal = $this->getLegalCardsets();
        foreach ($legal as $legalsetName) {
            if (strcmp($setName, $legalsetName) == 0) {  
              return true;
            }
        }
        return false;
    }

    public function isCardSingletonLegal($card, $amt) {
        $isLegal = false;
        
        if($amt == 1) {$isLegal = true;}
        
        switch($card) {
            case "Swamp":
                $isLegal = true;
                break;
            case "Plains":
                $isLegal = true;  
                break;
            case "Island":
                $isLegal = true;  
                break;
            case "Mountain":
                $isLegal = true;  
                break;
            case "Forest":
                $isLegal = true;  
                break;
            case "Snow-Covered Swamp":
                $isLegal = true;
                break;
            case "Snow-Covered Plains":
                $isLegal = true;  
                break;
            case "Snow-Covered Island":
                $isLegal = true;  
                break;
            case "Snow-Covered Mountain":
                $isLegal = true;  
                break;
            case "Snow-Covered Forest":
                $isLegal = true;  
                break;
        }
        return $isLegal;
    }
    
    public function isDeckCommanderLegal($deckID) {
        $isLegal = true;
        $deck = new Deck($deckID);
        $commanderColors = array();
        $commanderCard = Format::getCommanderCard($deck);
        
        if(is_null($commanderCard)) {
            $this->error[] = "Cannot find a Commander in your deck. There must be a Legendary Creature on the sideboard to serve as the Commander.";
            return false;
        } else {
            $commanderColors = Format::getCardColors($commanderCard);            
        }
        
        foreach($deck->maindeck_cards as $card => $amt){
            $colors = Format::getCardColors($card);
            foreach($colors as $color => $num) {
                if($num > 0) {
                    if ($commanderColors[$color] == 0) {
                       $isLegal = false;
                       $this->error[] = "Illegal card: $card. Card contains the color $color which does not match the Commander's Colors. The Commander was determined to be $commanderCard."; 
                    }
                }
            }
        }
        
        return $isLegal;
    }

    public static function getCardColors($card) {
        $db = Database::getConnection(); 
        $stmt = $db->prepare("SELECT isw, isr, isg, isu, isb
                              FROM cards 
                              WHERE name = ?"); 
        $stmt->bind_param("s", $card);
        $stmt->execute(); 
        $stmt->bind_result($colors["White"], $colors["Red"], $colors["Green"], $colors["Blue"], $colors["Black"]);
        $stmt->fetch();
        $stmt->close();
        return $colors;        
    }
    
    public static function getCommanderCard($deck) {
        foreach($deck->sideboard_cards as $card => $amt) {
            if(Format::isCardLegendary($card)) {
                return $card;
            }
        }    
        return NULL;
    }
    
    public static function isCardLegendary ($card) {
        return (count(Database::list_result_single_param("SELECT id FROM cards WHERE name = ? AND type LIKE '%Legendary%'", 
                                                         "s", $card)) > 0);
    }
    
    public function isQuantityLegal($card, $amt) {
        $isLegal = false;
        
        if($amt <= 4) {$isLegal = true;}
        
        switch($card) {
            case "Relentless Rats":
                $isLegal = true;
                break;
            case "Swamp":
                $isLegal = true;
                break;
            case "Plains":
                $isLegal = true;  
                break;
            case "Island":
                $isLegal = true;  
                break;
            case "Mountain":
                $isLegal = true;  
                break;
            case "Forest":
                $isLegal = true;  
                break;
            case "Snow-Covered Swamp":
                $isLegal = true;
                break;
            case "Snow-Covered Plains":
                $isLegal = true;  
                break;
            case "Snow-Covered Island":
                $isLegal = true;  
                break;
            case "Snow-Covered Mountain":
                $isLegal = true;  
                break;
            case "Snow-Covered Forest":
                $isLegal = true;  
                break;
        }
        return $isLegal;
    }
    
    public function isQuantityLegalAgainstMain($sideCard, $sideAmt, $mainCard, $mainAmt) {
        $isLegal = false;
        
        if ($sideCard == $mainCard) {
            if(($sideAmt + $mainAmt) <= 4) {$isLegal = true;}
        
            switch($sideCard) {
                case "Relentless Rats":
                    $isLegal = true;
                    break;
                case "Swamp":
                    $isLegal = true;
                    break;
                case "Plains":
                    $isLegal = true;  
                    break;
                case "Island":
                    $isLegal = true;  
                    break;
                case "Mountain":
                    $isLegal = true;  
                    break;
                case "Forest":
                    $isLegal = true;  
                    break;
                case "Snow-Covered Swamp":
                    $isLegal = true;
                    break;
                case "Snow-Covered Plains":
                    $isLegal = true;  
                    break;
                case "Snow-Covered Island":
                    $isLegal = true;  
                    break;
                case "Snow-Covered Mountain":
                    $isLegal = true;  
                    break;
                case "Snow-Covered Forest":
                    $isLegal = true;  
                    break;
                }
        } else {
            $isLegal = true; // mainCard and sideCard don't match so is automatically legal
                             // individual quantity check has already been done. We are only
                             // interested in finding too many of the same card between the side and main
        }
        return $isLegal;
    }
    
    public function insertCardIntoBanlist($card) {
        $card = stripslashes($card);
        $card = $this->getCardName($card);
        $cardID = $this->getCardID($card);
        if (is_null($cardID)) {
            return false; // card not found in database
        }
        
        if($this->isCardOnBanList($card) || $this->isCardOnLegalList($card) || $this->isCardOnLegalList($card)) {
            return false;
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO bans(card_name, card, format, allowed) VALUES(?, ?, ?, 0)");
            $stmt->bind_param("sds", $card, $cardID, $this->name);
            $stmt->execute() or die($stmt->error);
            $stmt->close();
            return true;
        }
    }
    
    public function insertCardIntoLegallist($card) {
        $card = stripslashes($card);
        $card = $this->getCardName($card);
        $cardID = $this->getCardID($card);
        if (is_null($cardID)) {
            return false; // card not found in database
        }
        
        if ($this->isCardOnLegalList($card)){
            return true;
        } else if ($this->isCardOnBanList($card) || $this->isCardOnLegalList($card)) {
            return false;
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO bans(card_name, card, format, allowed) VALUES(?, ?, ?, 1)");
            $stmt->bind_param("sds", $card, $cardID, $this->name);
            $stmt->execute() or die($stmt->error);
            $stmt->close();
            return true;
        }
    }
    
    public function insertCardIntoRestrictedlist($card) {
        $card = stripslashes($card);
        $card = $this->getCardName($card);
        $cardID = $this->getCardID($card);
        if (is_null($cardID)) {
            return false; // card not found in database
        }
        
        if($this->isCardOnBanList($card) || $this->isCardOnLegalList($card) || $this->isCardOnLegalList($card)) {
            return false;
        } else {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO restricted(card_name, card, format, allowed) VALUES(?, ?, ?, 2)");
            $stmt->bind_param("sds", $card, $cardID, $this->name);
            $stmt->execute() or die($stmt->error);
            $stmt->close();
            return true;
        }
    }
    
    public function deleteCardFromBanlist ($cardName) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM bans WHERE format = ? AND card_name = ? and allowed = 0");
        $stmt->bind_param("ss", $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close(); 
        return $removed;
     }
     
     public function deleteEntireBanlist () {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM bans WHERE format = ? AND allowed = 0");
        $stmt->bind_param("s", $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close(); 
        return $removed;         
     }
    
     public function deleteAllLegalSets () {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM setlegality WHERE format = ?");
        $stmt->bind_param("s", $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close(); 
        return $removed;         
     }
     
    public function deleteCardFromLegallist ($cardName) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM bans WHERE format = ? AND card_name = ? AND allowed = 1");
        $stmt->bind_param("ss", $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close(); 
        return $removed;
     }
     
     public function deleteEntireLegallist () {         
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM bans WHERE format = ? AND allowed = 1");
        $stmt->bind_param("s", $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close(); 
        return $removed;
     }
    
    public function deleteCardFromRestrictedlist ($cardName) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM restricted WHERE format = ? AND card_name = ?");
        $stmt->bind_param("ss", $this->name, $cardName);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close(); 
        return $removed;
     }
     
     public function deleteEntireRestrictedlist () {         
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM restricted WHERE format = ?");
        $stmt->bind_param("s", $this->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close(); 
        return $removed;
     }
    
  private function getCardID($cardname) {
      // Honestly I can't think of a good reason why we would have to ban a specific card (ban by id number). 
      // When you ban a card, don't you want to ban all versions of it? Not just one version?
      // so it makes more sense to ban by card name. But I will implement cardID's for now since that is how the
      // database was set up.
      return Database::single_result_single_param("SELECT id FROM cards WHERE name = ?", "s", $cardname);
  }

  static public function getCardName($cardname) {
      // this is used to return the name of the card as it appears in the database
      // otherwise the ban list will have cards on it like rOnCoR, RONCOR, rONCOR, etc
      return Database::single_result_single_param("SELECT name FROM cards WHERE name = ?", "s", $cardname);
  }
  
  public function insertNewLegalSet($cardsetName) {
      $db = Database::getConnection();
      $stmt = $db->prepare("INSERT INTO setlegality(format, cardset)VALUES(?, ?)");
      $stmt->bind_param("ss", $this->name, $cardsetName);
      $stmt->execute() or die($stmt->error);
      $stmt->close();
      return true;      
  }
  
  public function deleteLegalCardSet($cardsetName) {
      $db = Database::getConnection();
      $stmt = $db->prepare("DELETE FROM setlegality WHERE format = ? AND cardset = ?");
      $stmt->bind_param("ss", $this->name, $cardsetName);
      $stmt->execute();
      $removed = $stmt->affected_rows > 0;
      $stmt->close(); 
      return $removed;
  }
}
