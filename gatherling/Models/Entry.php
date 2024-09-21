<?php

namespace Gatherling\Models;

use Exception;
use Gatherling\Views\TemplateHelper;

class Entry
{
    public $event;
    public $player;
    public $deck;
    public $medal;
    public $drop_round;
    public $initial_byes;
    public $initial_seed;
    public $ignored;

    public static function findByEventAndPlayer($event_id, $playername)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT deck, medal FROM entries WHERE event_id = ? AND player = ?');
        $stmt->bind_param('ds', $event_id, $playername);
        $stmt->execute();
        $stmt->store_result();
        $found = false;
        if ($stmt->num_rows > 0) {
            $found = true;
        }
        $stmt->close();

        if ($found) {
            return new self($event_id, $playername);
        } else {
            return;
        }
    }

    public static function getActivePlayers($eventid)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.player
            FROM entries e
            JOIN events ev ON e.event_id = ev.id
            JOIN standings s ON ev.name = s.event
            WHERE e.event_id = ?
            AND s.active = 1
            GROUP BY player');
        $stmt->bind_param('d', $eventid);
        $entries = [];
        $playernames = [];
        $playername = '';
        $stmt->execute();
        $stmt->bind_result($playername);
        while ($stmt->fetch()) {
            $playernames[] = $playername;
        }
        $stmt->close();

        foreach ($playernames as $name) {
            $entries[] = new Entry($eventid, $name);
        }

        return $entries;
    }

    public static function playerRegistered($eventid, $playername)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT n.player
            FROM entries n
            JOIN events e ON n.event_id = e.id
            WHERE n.event_id = ?
            AND n.player = ?
            GROUP BY player');
        $stmt->bind_param('ds', $eventid, $playername);
        $stmt->execute();
        $stmt->store_result();
        $entry_exists = $stmt->num_rows > 0;
        $stmt->close();

        return $entry_exists;
    }

    // TODO: remove ignore functionality
    public function __construct($event_id, $playername)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT deck, medal, ignored, drop_round, initial_byes, initial_seed FROM entries WHERE event_id = ? AND player = ?');
        $stmt or exit($db->error);
        $stmt->bind_param('ds', $event_id, $playername);
        $stmt->execute();
        $this->ignored = 0;
        $stmt->bind_result($deckid, $this->medal, $this->ignored, $this->drop_round, $this->initial_byes, $this->initial_seed);

        if ($stmt->fetch() == null) {
            throw new Exception('Entry for '.$playername.' in '.$event_id.' not found');
        }
        $stmt->close();

        $this->event = new Event($event_id);
        $this->player = new Player($playername);

        if ($deckid != null) {
            $this->deck = new Deck($deckid);
        } else {
            $this->deck = null;
        }
    }

    public function recordString()
    {
        $matches = $this->getMatches();
        $wins = 0;
        $losses = 0;
        $draws = 0;
        foreach ($matches as $match) {
            if ($match->playerWon($this->player)) {
                $wins = $wins + 1;
            } elseif ($match->playerLost($this->player)) {
                $losses = $losses + 1;
            } elseif ($match->playerBye($this->player)) {
                $wins = $wins + 1;
            } elseif ($match->playerMatchInProgress($this->player)) {
                // do nothing since match is in progress and there are no results
            } else {
                $draws = $draws + 1;
            }
        }

        if ($draws == 0) {
            return $wins.'-'.$losses;
        } else {
            return $wins.'-'.$losses.'-'.$draws;
        }
    }

    public function getMatches()
    {
        return $this->player->getMatchesEvent($this->event->name);
    }

    public function canDelete()
    {
        $matches = $this->getMatches();

        return count($matches) == 0;
    }

    public function dropped()
    {
        return $this->drop_round > 0;
    }

    public function canCreateDeck($username)
    {
        $player = new Player($username);

        if (
            $player->isSuper() ||
            $this->event->isHost($username) ||
            $this->event->isOrganizer($username) ||
            strcasecmp($username, $this->player->name) == 0
        ) {
            return true;
        }

        return false;
    }

    // TODO: Remove ignore functionality
    public function setIgnored($new_ignored)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE entries SET ignored = ? WHERE player = ? and event_id = ?');
        $playername = $this->player->name;
        $event_id = $this->event->id;
        $stmt->bind_param('isd', $new_ignored, $playername, $event_id);
        $stmt->execute();
        $stmt->close();
    }

    public function removeEntry()
    {
        $db = Database::getConnection();
        if ($this->deck) {
            $this->deck->delete();
        } // if the player being unreg'd entered a deck list, remove it

        $stmt = $db->prepare('DELETE FROM entries WHERE event_id = ? AND player = ?');
        $stmt->bind_param('ds', $this->event->id, $this->player->name);
        $stmt->execute();
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM standings WHERE event = ? AND player = ?');
        $stmt->bind_param('ss', $this->event->name, $this->player->name);
        $stmt->execute();
        $stmt->close();

        return $removed;
    }

    public function createDeckLink(): string
    {
        // creates a link to enter a deck list once a player is registered for the event
        $args = $this->createDeckLinkArgs();

        return TemplateHelper::render('partials/createDeckLink', $args);
    }

    public function createDeckLinkArgs(): array
    {
        $args = [
            'canCreateDeck' => $this->canCreateDeck(Player::loginName()),
        ];
        if (!$args['canCreateDeck']) {
            return $args;
        }
        $args['createDeckLink'] = 'deck.php?player='.rawurlencode($this->player->name).'&event='.rawurlencode($this->event->id).'&mode=create';

        return $args;
    }

    /**
     * @param int $byeqty
     *
     * @return void
     */
    public function setInitialByes($byeqty)
    {
        $db = Database::getConnection();
        $db->autocommit(false);
        $stmt = $db->prepare('UPDATE entries SET initial_byes = ? WHERE player = ? AND event_id = ?');
        $stmt->bind_param('dsd', $byeqty, $this->player->name, $this->event->id);
        $stmt->execute();
        if ($stmt->affected_rows < 0) {
            $db->rollback();
            $db->autocommit(true);

            throw new Exception('Entry for '.$this->player->name.' in '.$this->event->name.' not found');
        }
        $db->commit();
        $db->autocommit(true);
    }

    public function setInitialSeed($byeqty)
    {
        $db = Database::getConnection();
        $db->autocommit(false);
        $stmt = $db->prepare('UPDATE entries SET initial_seed = ? WHERE player = ? AND event_id = ?');
        $stmt->bind_param('dsd', $byeqty, $this->player->name, $this->event->id);
        $stmt->execute();
        if ($stmt->affected_rows < 0) {
            $db->rollback();
            $db->autocommit(true);

            throw new Exception('Entry for '.$this->player->name.' in '.$this->event->name.' not found');
        }
        $db->commit();
        $db->autocommit(true);
    }
}
