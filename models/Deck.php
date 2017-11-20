<?php

class Deck {
  public $id;
  public $name;
  public $archetype; 
  public $notes;

  public $sideboard_cards = array(); // Has many sideboard_cards through deckcontents (issideboard = 1)
  public $maindeck_cards = array(); // Has many maindeck_cards through deckcontents (issideboard = 0)

  public $maindeck_cardcount = 0;
  public $sideboard_cardcount = 0;

  public $errors = array();

  public $playername; // Belongs to player through entries
  public $eventname; // Belongs to event through entries
  public $subeventid; // Belongs to event
  public $format; // Belongs to event

  public $medal; // has a medal

  public $new; // is new

  function __construct($id) { 
    if ($id == 0) { 
      $this->id = 0;
      $this->new = true;
      return;
    } 
    $database = Database::getConnection(); 
    $stmt = $database->prepare("SELECT name, archetype, notes, deck_hash, sideboard_hash, whole_hash
                                FROM decks d
                                WHERE id = ?");
    $stmt->bind_param("d", $id);
    $stmt->execute();
    $stmt->bind_result($this->name, $this->archetype, $this->notes, $this->deck_hash, $this->sideboard_hash, 
                       $this->whole_hash);

    if ($stmt->fetch() == NULL) { 
      $this->id = 0;
      $this->new = true;
      return;
    }

    $this->new = false;
    $this->id = $id; 

    $stmt->close();
    // Retrieve cards.
    $stmt = $database->prepare("SELECT c.name, dc.qty, dc.issideboard
                                FROM cards c, deckcontents dc, decks d
                                WHERE d.id = dc.deck 
                                AND c.id = dc.card 
                                AND d.id = ?"); 
    $stmt->bind_param("d", $id);
    $stmt->execute(); 
    $stmt->bind_result($cardname, $cardqty, $isside);

    $this->maindeck_cardcount = 0;
    $this->sideboard_cardcount = 0;
    while ($stmt->fetch()) {
      if ($isside == 0) {
        $this->maindeck_cards[$cardname] = $cardqty;
        $this->maindeck_cardcount += $cardqty;
      } else {
        $this->sideboard_cards[$cardname] = $cardqty;
        $this->sideboard_cardcount += $cardqty;
      }
    }

    $stmt->close();

    // Retrieve player
    $stmt = $database->prepare("SELECT p.name 
                                FROM players p, entries e, decks d
                                WHERE p.name = e.player 
                                AND d.id = e.deck 
                                AND d.id = ?");
    $stmt->bind_param("d", $id);
    $stmt->execute();
    $stmt->bind_result($this->playername);
    $stmt->fetch();

    $stmt->close();

    // Retrieve event 
    $stmt = $database->prepare("SELECT e.name
                                FROM events e, entries n, decks d
                                WHERE d.id = ? and d.id = n.deck 
                                AND n.event = e.name"); 
    $stmt->bind_param("d", $id);
    $stmt->execute(); 
    $stmt->bind_result($this->eventname);
    $stmt->fetch();
    $stmt->close();

    // Retrieve format
    // The entire constructor does not run when a new deck is created, so this has to be duplicated
    // later in the save() function
     if (!is_null($this->eventname)) {
         $this->format = Database::single_result_single_param("SELECT format 
                                                               FROM events 
                                                               WHERE name = ?", "s", $this->eventname);
         $this->subeventid = Database::single_result_single_param("SELECT id
                                                                   FROM subevents
                                                                   WHERE parent = ?", "s", $this->eventname);
     } else {
         $this->format = "";
         $this->subeventid = NULL;
     }
    
    // Retrieve medal 
    $stmt = $database->prepare("SELECT n.medal
                                FROM entries n 
                                WHERE n.deck = ?");
    $stmt->bind_param("d", $id);
    $stmt->execute();
    $stmt->bind_result($this->medal);
    $stmt->fetch();
    $stmt->close();
    if ($this->medal == NULL) { $this->medal = "dot"; }
    
    // Retrieve errors
    $stmt = $database->prepare("Select error 
                                FROM deckerrors 
                                WHERE deck = ?");
    $stmt->bind_param("d", $this->id);
    $stmt->execute();
    $stmt->bind_result($error);  

    while ($stmt->fetch()) {
        $this->errors[] = $error;
    }
    $stmt->close();
  }

  static function getArchetypes() {
    return Database::list_result("SELECT name FROM archetypes WHERE priority > 0 ORDER BY priority DESC, name");
  }

  function getEntry() {
    return new Entry($this->eventname, $this->playername);
  } 

  function recordString() {
    if ($this->playername == NULL) { return "?-?"; }
    return $this->getEntry()->recordString();
  } 

  function getColorImages() {
    $count = $this->getColorCounts();
    $str = ""; 
    foreach ($count as $color => $n) { 
      if ($n > 0) { 
        $str = $str . image_tag("mana{$color}.png");
      } 
    }  
    return $str;
  } 

  function getColorCounts() { 
    $db = Database::getConnection(); 
    $stmt = $db->prepare("SELECT sum(isw*d.qty) 
                          AS w, sum(isg*d.qty) 
                          AS g, sum(isu*d.qty) 
                          AS u, sum(isr*d.qty) 
                          AS r, sum(isb*d.qty) 
                          AS b
                          FROM cards c, deckcontents d 
                          WHERE d.deck = ? 
                          AND c.id = d.card 
                          AND d.issideboard != 1"); 
    $stmt->bind_param("d", $this->id);
    $stmt->execute(); 
    $count = array();
    $stmt->bind_result($count["w"], $count["g"], $count["u"], $count["r"], $count["b"]);
    $stmt->fetch();

    $stmt->close();
    return $count;
  }

  static public function getColorString() {
    $str = ""; 
    foreach ($count as $color => $n) { 
      if ($n > 0) { 
        $str = $str . $color;
      }
    }
    return strtoupper($str);
  }

  // TODO: Find a way to list the inline id as a param
  function getCastingCosts() { 
    $db = Database::getConnection(); 
    $result = $db->query("SELECT convertedcost 
                          AS cc, sum(qty) 
                          AS s
                          FROM cards c, deckcontents d 
                          WHERE d.deck = {$this->id} 
                          AND c.id = d.card 
                          AND d.issideboard = 0
                          GROUP BY c.convertedcost 
                          HAVING cc > 0"); 

    $convertedcosts = array(); 
    while ($res = $result->fetch_assoc()) { 
      $convertedcosts[$res['cc']] = $res['s']; 
    } 

    return $convertedcosts; 
  } 

  function getEvent() { 
    return new Event($this->eventname); 
  } 

  function getCardCount($cards) {
    $cardCount = 0;
    
    foreach($cards as $card => $amt) {
        $cardCount += $amt;
    }
    return $cardCount;
}

  function getCreatureCards() { 
    $db = Database::getConnection(); 
    $result = $db->query("SELECT dc.qty, c.name
                          FROM deckcontents dc, cards c 
                          WHERE c.id = dc.card 
                          AND dc.deck = {$this->id} 
                          AND c.type 
                          LIKE '%Creature%' 
                          AND dc.issideboard = 0 
                          ORDER BY dc.qty 
                          DESC, c.name"); 

    $cards = array(); 
    while ($res = $result->fetch_assoc()) { 
      $cards[$res['name']] = $res['qty'];
    } 

    return $cards;
  } 
  
  // find a way to list the id as a param
  function getLandCards() { 
    $db = Database::getConnection(); 
    $result = $db->query("SELECT dc.qty, c.name
                          FROM deckcontents dc, cards c 
                          WHERE c.id = dc.card 
                          AND dc.deck = {$this->id} 
                          AND c.type 
                          LIKE '%Land%' 
                          AND dc.issideboard = 0 
                          ORDER BY dc.qty 
                          DESC, c.name"); 

    $cards = array(); 
    while ($res = $result->fetch_assoc()) { 
      $cards[$res['name']] = $res['qty'];
    } 

    return $cards;
  } 

  function getErrors() {
      $db = Database::getConnection();
      $stmt = $db->prepare("Select error FROM deckerrors WHERE deck = ?");
      $stmt->bind_param("d", $this->id);
      $stmt->execute();
      $stmt->bind_result($error);  

      $errors = array();
      while ($stmt->fetch()) {
          $errors[] = $error;
      }
      $stmt->close();
      return $errors;
  }    

  // find a way to list the id as a param
  function getOtherCards() { 
    $db = Database::getConnection(); 
    $result = $db->query("SELECT dc.qty, c.name
                         FROM deckcontents dc, cards c 
                         WHERE c.id = dc.card 
                         AND dc.deck = {$this->id} 
                         AND c.type 
                         NOT LIKE '%Creature%' 
                         AND c.type 
                         NOT LIKE '%Land%'
                         AND dc.issideboard = 0 
                         ORDER BY dc.qty 
                         DESC, c.name"); 

    $cards = array(); 
    while ($res = $result->fetch_assoc()) { 
      $cards[$res['name']] = $res['qty'];
    } 

    return $cards;
  } 

  function getMatches() { 
    if ($this->playername == NULL) { return array(); }
    return $this->getEntry()->getMatches();
  } 

  function getPlayer() { 
    return new Player($this->playername); 
  } 

  function canEdit($username) { 
    $event = $this->getEvent(); 
    $player = new Player($username);

    if ($player->isSuper() || 
        $event->isHost($username) || 
        $event->isOrganizer($username) || 
        (!$event->finalized && !$event->active && strcasecmp($username, $this->playername) == 0)) {
        return true;
    }
    return false;
   }

  function canView($username) { 
    $event = $this->getEvent(); 
    $player = new Player($username);

   if ($event->finalized && !$event->active) {
       return true;
   } else {
       if ($player->isSuper() || 
           $event->isHost($username) || 
           $event->isOrganizer($username) || 
           strcasecmp($username, $this->playername) == 0) {
           return true;
       } 
   }
   return false;
  }

  private function getCard($cardname) { 
    $db = Database::getConnection(); 
    $stmt = $db->prepare("SELECT id, name FROM cards WHERE name = ?");
    $stmt->bind_param("s", $cardname); 
    $stmt->execute(); 
    $cardar = array();
    $stmt->bind_result($cardar['id'], $cardar['name']); 
    if (is_null($stmt->fetch())) { 
      $cardar = NULL; 
    } 
    $stmt->close(); 

    return $cardar;
  }

  function isValid() {
    return (count($this->errors) == 0);
  }

  // functions to remove deck errors, otherwise when you try to delete a deck that cannot be deleted the error 
  // will stay until you try to update the deck again
  function flushDeckErrors() {
      $db = Database::getConnection(); 
      $db->autocommit(FALSE);
      $this->errors = array();
      
      $succ = $db->query("DELETE FROM deckerrors WHERE deck = {$this->id}");

      if (!$succ) {
          $db->rollback(); 
          $db->autocommit(TRUE);
          throw new Exception("Cannot flush the deckerror content {$this->id}"); 
    return false;
      } else {
          return true;
      }
  }
  

  function delete() {
      $db = Database::getConnection(); 
      $db->autocommit(FALSE);
      $this->errors = array();
	
     // Checks to see if any matches have been played by the deck, if not deletes the deck
     if(count($this->getMatches()) == 0 ) {
         $succ = $db->query("DELETE FROM entries WHERE deck = {$this->id}");
         if (!$succ) {
             $db->rollback();
             $db->autocommit(TRUE);
             throw new Exception("Can't delete deck contents {$this->id} expection 1"); 
         }
         $succ = $db->query("DELETE FROM deckerrors WHERE deck = {$this->id}");
         if (!$succ) {
             $db->rollback();
             $db->autocommit(TRUE);
             throw new Exception("Can't delete deck contents {$this->id} expection 2");
         }
         $succ = $db->query("DELETE FROM deckcontents WHERE deck = {$this->id}");
         if (!$succ) {
             $db->rollback();
             $db->autocommit(TRUE);
             throw new Exception("Can't delete deck contents {$this->id} expection 3"); 
         }
         $succ = $db->query("DELETE FROM decks WHERE id = {$this->id}");
         if (!$succ) {
             $db->rollback();
             $db->autocommit(TRUE);
             throw new Exception("Can't delete deck contents {$this->id} expection 4"); 
         }
         $db->commit();
         $db->autocommit(TRUE);
         return true;
      } 	
   }
  
  
  function save() {
    $db = Database::getConnection(); 
    $db->autocommit(FALSE);
    $this->errors = array();
    $format = NULL; // will initialize later after I verify that eventname has been.

    if ($this->name == NULL || $this->name == "") {
      $this->errors[] = "Name cannot be blank";
    }
    if ($this->archetype != "Unclassified" && !in_array($this->archetype, Deck::getArchetypes())) {
      $this->archetype = "Unclassified";
    }
    
    if ($this->id == 0) { 
      // New record.  Set up the decks entry and the Entry.
      $stmt = $db->prepare("INSERT INTO decks (archetype, name, notes) values(?, ?, ?)");
      $stmt->bind_param("sss", $this->archetype, $this->name, $this->notes); 
      $stmt->execute();
      $this->id = $stmt->insert_id;

      $stmt = $db->prepare("UPDATE entries SET deck = ? WHERE player = ? AND event = ?");
      $stmt->bind_param("dss", $this->id, $this->playername, $this->eventname);
      $stmt->execute(); 
      if ($stmt->affected_rows != 1) { 
        $db->rollback(); 
        $db->autocommit(TRUE);
            throw new Exception('Entry for '. $this->playername .' in '. $this->eventname .' not found');
      } 
      // had to put this here since the constructor doesn't run entirely when a new deck is created 
      if (!is_null($this->eventname)) {
          $this->format = Database::single_result_single_param("SELECT format FROM events WHERE name = ?", "s", $this->eventname);
      } else {
          $this->format = "";
      }
      $format = new Format($this->format); 

    } else { 
      $stmt = $db->prepare("UPDATE decks SET archetype = ?, name = ?, notes = ? WHERE id = ?"); 
      if (!$stmt) { 
        echo $db->error;
      } 
      $stmt->bind_param("sssd", $this->archetype, $this->name, $this->notes, $this->id); 
      if (!$stmt->execute()) { 
        $db->rollback(); 
        $db->autocommit(TRUE);
        throw new Exception('Can\'t update deck '. $this->id); 
      }
      $format = new Format($this->format);      
    }
   
   // TODO: find a way to list the id as a param
   $succ = $db->query("DELETE FROM deckcontents WHERE deck = {$this->id}");

    if (!$succ) {
      $db->rollback(); 
      $db->autocommit(TRUE);
      throw new Exception("Can't update deck contents {$this->id}"); 
    }
   
    // find a way to list the id as a param
    $succ = $db->query("DELETE FROM deckerrors WHERE deck = {$this->id}");

    if (!$succ) {
      $db->rollback(); 
      $db->autocommit(TRUE);
      throw new Exception("Can't update deck contents {$this->id}"); 
    }
    
    // begin parsing deck list
    $newmaindeck = array();
    $legalCards = $format->getLegalList();
    $this->maindeck_cardcount = 0;
    
    foreach ($this->maindeck_cards as $card => $amt) {
      $card = stripslashes($card);
      $testcard = Format::getCardName($card);
      $cardar = $format->getLegalCard($testcard);
      if (is_null($cardar)) {
        $this->errors[] = "Could not find maindeck card: {$amt} {$card} in legal sets";

        if (!isset($this->unparsed_cards[$card])) {
          $this->unparsed_cards[$card] = 0;
        }
        $this->unparsed_cards[$card] += $amt;
        continue;
      } else {
          $card = $testcard;
      }
      
      // Restricted Card list. Only one of these cards is alowed in a deck
      if($format->isCardOnRestrictedList($card) && $amt > 1) {
          $this->errors[] = "Maindeck card: {$amt} {$card} is on the restricted list. 
                             Only one of this card may be in a deck list.";
          if (!isset($this->unparsed_cards[$card])) {
              $this->unparsed_cards[$card] = 0;
          }
          $this->unparsed_cards[$card] += $amt;
          continue;
      }
      
      if($format->singleton) {
          if(!$format->isCardSingletonLegal($card, $amt)) {
              $this->errors[] = "Singleton formats allow only one of any card, except basic lands. 
                                 You entered {$amt} {$card} in your mainboard.";
          }
      } else {
          if (!$format->isQuantityLegal($card, $amt)) {
              $this->errors[] = "No more than four of any card is allowed in this format, except basic lands. 
                                 You entered {$amt} {$card} in your mainboard.";
          }
      }
      
      // You can only use the legal card list, or the ban list - not both
      if (count($legalCards)) {
          if (!$format->isCardOnLegalList($card)) {
              $this->errors[] = "Maindeck card: {$amt} {$card} is not on the legal card list";
              if (!isset($this->unparsed_cards[$card])) {
                  $this->unparsed_cards[$card] = 0;
              }
              $this->unparsed_cards[$card] += $amt;
              continue;
          }
      } else {
          if($format->isCardOnBanList($card)) {
              $this->errors[] = "Maindeck card: {$amt} {$card} is banned in {$format->name}";
              if (!isset($this->unparsed_cards[$card])) {
                  $this->unparsed_cards[$card] = 0;
              }
              $this->unparsed_cards[$card] += $amt;
              continue;
          }
      }
      if(!$format->isCardLegalByRarity($card)) {
          $this->errors[] = "Maindeck card : {$amt} {$card} is illegal by rarity.";
          if (!isset($this->unparsed_cards[$card])) {
              $this->unparsed_cards[$card] = 0;
          }
          $this->unparsed_cards[$card] += $amt;
          continue;          
      }
      $this->maindeck_cardcount += $amt;
      $stmt = $db->prepare("INSERT INTO deckcontents (deck, card, issideboard, qty) values(?, ?, 0, ?)");
      $stmt->bind_param("ddd", $this->id, $cardar['id'], $amt);
      $stmt->execute();
      $newmaindeck[$cardar['name']] = $amt;
    }

    $this->maindeck_cards = $newmaindeck;

    // begin parsing sideboard
    $newsideboard = array();
    $this->sideboard_cardcount = 0;
    
    foreach ($this->sideboard_cards as $card => $amt) {
      $card = stripslashes($card);
      $card = Format::getCardName($card);      
      $cardar = $format->getLegalCard($card);
      if (is_null($cardar)) {
        $this->errors[] = "Could not find sideboard card: {$amt} {$card} in legal sets";

        if (!isset($this->unparsed_side[$card])) {
          $this->unparsed_side[$card] = 0;
        } 
        $this->unparsed_side[$card] += $amt;
        continue; 
      }
      
      // Restricted Card list. Only one of these cards is alowed in a deck
      if($format->isCardOnRestrictedList($card)) {
          $restrictedError = false;
          if($amt > 1) {
              $restrictedError = true;
          }
          foreach($this->maindeck_cards as $restrictedCard => $mainamt) {
              if ($restrictedCard == $card) {
                  $restrictedError = true;
                  break;
              }
          }
          if($restrictedError) {
              $this->errors[] = "Sideboard card: {$amt} {$card} is on the restricted list. 
                                 Only one of this card may be in a deck list.";
              if (!isset($this->unparsed_side[$card])) {
                  $this->unparsed_side[$card] = 0;
              }
              $this->unparsed_side[$card] += $amt;
              continue;
          }
      }
      
      if($format->singleton) {
          if(!$format->isCardSingletonLegal($card, $amt)) {
              $this->errors[] = "Singleton formats allow only one of any card, except basic lands. 
                                 You entered {$amt} {$card} on your sideboard.";
          }
          foreach($this->maindeck_cards as $singletonCard => $mainamt) {
              if ($singletonCard == $card) {
                  $this->errors[] = "Singleton formats allow only one of any card, except basic lands. 
                                     You entered {$amt} {$card} on your sideboard 
                                     and {$mainamt} {$card} in your mainboard.";
                  break;
              }
          }          
      } else {
          if (!$format->isQuantityLegal($card, $amt)) {
              $this->errors[] = "No more than four of any card is allowed in this format, except basic lands. 
                                 You entered {$amt} {$card} on your sideboard.";
          } else {
              foreach($this->maindeck_cards as $quantityCard => $mainamt) {
                  if (!$format->isQuantityLegalAgainstMain($card, $amt, $quantityCard, $mainamt)) {
                      $this->errors[] = "No more than four of any card is allowed in this format, except basic lands. 
                                         You entered {$amt} {$card} on your sideboard 
                                         and {$mainamt} {$card} in your mainboard.";
                      break;
                  }
              }
          }
      }      
            
      // You can only use the legal card list, or the ban list - not both
      if (count($legalCards)) {
          if (!$format->isCardOnLegalList($card)) {
              $this->errors[] = "Sideboard card: {$amt} {$card} is not on the legal card list";
              if (!isset($this->unparsed_side[$card])) {
                  $this->unparsed_side[$card] = 0;
              }
              $this->unparsed_side[$card] += $amt;
              continue;
          }
      } else {
          if($format->isCardOnBanList($card)) {
              $this->errors[] = "Sideboard card: {$amt} {$card} is banned in {$format->name}";
              if (!isset($this->unparsed_side[$card])) {
                  $this->unparsed_side[$card] = 0;
              }
              $this->unparsed_side[$card] += $amt;
              continue;
          }
      }
      if(!$format->isCardLegalByRarity($card)) {
          $this->errors[] = "Sideboard card : {$amt} {$card} is illegal by rarity.";
          if (!isset($this->unparsed_side[$card])) {
              $this->unparsed_side[$card] = 0;
          } 
          $this->unparsed_side[$card] += $amt;
          continue;          
      }
      
      $this->sideboard_cardcount += $amt;
      $stmt = $db->prepare("INSERT INTO deckcontents (deck, card, issideboard, qty) values(?, ?, 1, ?)"); 
      $stmt->bind_param("ddd", $this->id, $cardar['id'], $amt); 
      $stmt->execute();
      $newsideboard[$cardar['name']] = $amt;
      }

    $this->sideboard_cards = $newsideboard;
    
    $stmt = $db->prepare("UPDATE decks SET notes = ? WHERE id = ?");
    if (!$stmt) {
      echo $db->error;
    }
    $stmt->bind_param("sd", $this->notes, $this->id);
    if (!$stmt->execute()) {
      $db->rollback();
      $db->autocommit(TRUE);
      throw new Exception('Can\'t update deck '. $this->id);
    }

    $this->deck_contents_cache = implode('|', array_merge(array_keys($this->maindeck_cards),
                                                          array_keys($this->sideboard_cards)));

    $stmt = $db->prepare("UPDATE decks set deck_contents_cache = ? WHERE id = ?");

    $stmt->bind_param("sd", $this->deck_contents_cache, $this->id);
    $stmt->execute();

    $db->commit();
    $db->autocommit(TRUE);
    $this->calculateHashes();
    
    if ($this->maindeck_cardcount < $format->min_main_cards_allowed) {
        $this->errors[] = "This format requires a minimum of {$format->min_main_cards_allowed} Maindeck Cards";
    } else if ($this->maindeck_cardcount > $format->max_main_cards_allowed) {
        $this->errors[] = "This format allows a maximum of {$format->max_main_cards_allowed} Maindeck Cards";
    }
    
    if ($this->sideboard_cardcount < $format->min_side_cards_allowed && 
        $this->sideboard_cardcount > $format->max_side_cards_allowed) {
        if ($format->min_side_cards_allowed == $format->max_side_cards_allowed)
            $this->errors[] = "A legal sideboard for this format has $format->max_side_cards_allowed cards.";
        else {
            $this->errors[] = "A legal sideboard for this format has between $format->min_side_cards_allowed and 
                              $format->max_side_cards_allowed cards.";
        }
    }
    
    if ($format->commander) {
        if(!$format->isDeckCommanderLegal($this->id)) {
            $beg = $this->errors;
            $end = $format->getErrors();
            $this->errors = array();
            $this->errors = array_merge($beg, $end);
        } 
    }
    
    foreach($this->errors as $error) {
        $stmt = $db->prepare("INSERT INTO deckerrors (deck, error) values(?, ?)");
        $stmt->bind_param("ds", $this->id, $error);
        $stmt->execute();        
    }

    return true;
  }

  function findIdenticalDecks() { 
    if (!isset($this->identicalDecks)) {
      $db = Database::getConnection();
      $stmt = $db->prepare("SELECT d.id 
                            FROM decks d, entries n, events e 
                            WHERE deck_hash = ? 
                            AND id != ? 
                            AND n.deck = d.id 
                            AND e.name = n.event 
                            ORDER BY e.start 
                            DESC");
      $stmt->bind_param("sd", $this->deck_hash, $this->id);
      $same_ids = array();
      $this_id = 0;
      $stmt->execute();
      $stmt->bind_result($this_id);
      while ($stmt->fetch()) { 
        $same_ids[] = $this_id;
      } 
      $stmt->close(); 

      $decks = array();

      foreach ($same_ids as $other_deck_id) { 
        $possibledeck = new Deck($other_deck_id); 
        if (isset($possibledeck->playername)) { 
          $decks[] = $possibledeck; 
        } 
      } 
      $this->identical_decks = $decks;
    }
    return $this->identical_decks;
  }

  function calculateHashes() {
    # Deck HASHES are an easy way to compare two decks for EQUALITY.
    # They are computed as follows:
    #  A string is built with the following format:
    #   "(amt)(Cardname)(amt)(Cardname)..."
    #  The cardnames are unique per Magic: The Gathering
    #  The cardnames are lexographically sorted!
    #  The amounts are NOT PADDED: 1 => 1, 10 => 10, 100 => 100
    #  There is NO SPACE BETWEEN THE amount and the cardname, or between cards
    #  Make this string for the main deck and the sideboard. 
    #  Concatenate these strings: maindeckStr + "<sb>" + sideboardStr
    #  Make a SHA-1 hash of this string for the whole_hash
    #  Make a SHA-1 hash of the maindeckStr for the maindeck_hash
    #  Make a SHA-1 hash of the sideboardStr for the sideboard_hash
    $cards = array_keys($this->maindeck_cards);
    sort($cards, SORT_STRING);
    $maindeckStr = "";
    foreach ($cards as $cardname) { 
      $maindeckStr .= $this->maindeck_cards[$cardname] . $cardname;
    }
    $this->deck_hash = sha1($maindeckStr);
    $sideboardStr = "";
    $cards = array_keys($this->sideboard_cards);
    sort($cards, SORT_STRING);
    foreach ($cards as $cardname) { 
      $sideboardStr .= $this->sideboard_cards[$cardname] . $cardname;
    }
    $this->sideboard_hash = sha1($sideboardStr);
    $this->whole_hash = sha1($maindeckStr . "<sb>" . $sideboardStr); 
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE decks SET sideboard_hash = ?, deck_hash = ?, whole_hash = ? where id = ?");
    $stmt->bind_param("sssd", $this->sideboard_hash, $this->deck_hash, $this->whole_hash, $this->id);
    $stmt->execute();
    $stmt->close();
  }

  static function uniqueCount() { 
    $db = @Database::getConnection(); 
    $stmt = $db->prepare("SELECT count(deck_hash) FROM decks GROUP BY deck_hash");
    $stmt->execute(); 
    $stmt->store_result();
    /// SLIGHTLY different than singular
    $uniquecount = $stmt->num_rows;
    $stmt->close(); 
    return $uniquecount; 
  }

  function linkTo() {
    $verify = "deckverified";      
    if ($this->new) {
      return "Deck not found";
    } else {
      if (empty($this->name)) {
          $this->name = $this->getColorString() . ' ' . $this->archetype; 
      }
      if (!$this->isValid()) {
          $verify = "deckunverified";
      }
      return "<a class=\"$verify\" href=\"deck.php?mode=view&id={$this->id}\">{$this->name}</a>";
    }
  }
}
