<?php

class Entry {
  public $event;
  public $player;
  public $deck;
  public $medal;
  public $drop_round;

  static function findByEventAndPlayer($eventname, $playername) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT deck, medal FROM entries WHERE event = ? AND player = ?");
    $stmt->bind_param("ss", $eventname, $playername);
    $stmt->execute();
    $stmt->store_result();
    $found = false;
    if ($stmt->num_rows > 0) {
      $found = true;
    }
    $stmt->close();

    if ($found) {
      return new Entry($eventname, $playername);
    } else {
      return NULL;
    }
  }

  // TODO: remove ignore functionality
  function __construct($eventname, $playername) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT deck, medal, ignored, drop_round FROM entries WHERE event = ? AND player = ?");
    $stmt or die($db->error);
    $stmt->bind_param("ss", $eventname, $playername);
    $stmt->execute();
    $this->ignored = 0;
    $stmt->bind_result($deckid, $this->medal, $this->ignored, $this->drop_round);

    if ($stmt->fetch() == NULL) {
            throw new Exception('Entry for '. $playername .' in '. $eventname .' not found');
    }
    $stmt->close();

    $this->event = new Event($eventname);
    $this->player = new Player($playername);

    if ($deckid != NULL) {
      $this->deck = new Deck($deckid);
    } else {
      $this->deck = NULL;
    }
  }

  function recordString() {
    $matches = $this->getMatches();
    $wins = 0;
    $losses = 0;
    $draws = 0;
    foreach ($matches as $match) {
      if ($match->playerWon($this->player)) {
        $wins = $wins + 1;
      } else if ($match->playerLost($this->player)) {
        $losses = $losses + 1;
      } else if ($match->playerBye($this->player)) {
        $wins = $wins + 1;
      } else if ($match->playerMatchInProgress($this->player)) {
          ; // do nothing since match is in progress and there are no results
      } else {
        $draws = $draws + 1;
      }
    }

    if ($draws == 0) {
      return $wins . "-" . $losses;
    } else {
      return $wins . "-" . $losses . "-" . $draws;
    }
  }

  function getMatches() {
    return $this->player->getMatchesEvent($this->event->name);
  }

  function canDelete() {
    $matches = $this->getMatches();
    return (count($matches) == 0);
  }

  function dropped() {
    return ($this->drop_round > 0);
  }

  function canCreateDeck($username) {
    $player = new Player($username);

    if ($player->isSuper() ||
        $this->event->isHost($username) ||
        $this->event->isOrganizer($username) ||
        strcasecmp($username, $this->player->name) == 0) {
      return true;
    }
    return false;
  }

  // TODO: Remove ignore functionality
  function setIgnored($new_ignored) {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE entries SET ignored = ? WHERE player = ? and event = ?");
    $playername = $this->player->name;
    $eventname = $this->event->name;
    $stmt->bind_param("iss", $new_ignored, $playername, $eventname);
    $stmt->execute();
    $stmt->close();
  }

  function removeEntry() {
    $db = Database::getConnection();
    if ($this->deck) {$this->deck->delete();} // if the player being unreg'd entered a deck list, remove it

    $stmt = $db->prepare("DELETE FROM entries WHERE event = ? AND player = ?");
    $stmt->bind_param("ss", $this->event->name, $this->player->name);
    $stmt->execute();
    $removed = $stmt->affected_rows > 0;
    $stmt->close();

    $db = Database::getConnection();
    $stmt = $db->prepare("DELETE FROM standings WHERE event = ? AND player = ?");
    $stmt->bind_param("ss", $this->event->name, $this->player->name);
    $stmt->execute();
    $stmt->close();
    return $removed;
  }

  function createDeckLink() { // creates a link to enter a deck list once a player is registered for the event
    if ($this->canCreateDeck(Player::loginName())) {
      return "<a class=\"create_deck_link\" href=\"deck.php?player=" . urlencode($this->player->name) . "&event=" . urlencode($this->event->name) . "&mode=create\">[Create Deck]</a>";
    } else {
      return "(no deck entered)";
    }
  }
}


