<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Exception;
use Gatherling\Data\Db;
use Gatherling\Views\TemplateHelper;
use Gatherling\Exceptions\NotFoundException;

class Entry
{
    public Event $event;
    public Player $player;
    public ?Deck $deck;
    public ?string $medal;
    public ?int $drop_round;
    public ?int $initial_byes;
    public ?int $initial_seed;
    public ?int $ignored;

    public static function findByEventAndPlayer(int $event_id, string $playername): ?self
    {
        $sql = 'SELECT deck FROM entries WHERE event_id = :event_id AND player = :player';
        $params = ['event_id' => $event_id, 'player' => $playername];
        $deckId = Db::optionalInt($sql, $params);
        if (!$deckId) {
            return null;
        }
        return new self($event_id, $playername);
    }

    /**
     * @return list<self>
     */
    public static function getActivePlayers(int $eventid): array
    {
        $sql = '
            SELECT
                e.player
            FROM
                entries e
            JOIN
                events ev ON e.event_id = ev.id
            JOIN
                standings s ON ev.name = s.event
            WHERE
                e.event_id = :eventid
            AND
                s.active = 1
            GROUP BY
                player';
        $playernames = Db::strings($sql, ['eventid' => $eventid]);
        return array_map(fn (string $name) => new Entry($eventid, $name), $playernames);
    }

    public static function playerRegistered(int $eventid, string $playername): bool
    {
        $sql = '
            SELECT
                n.player
            FROM
                entries n
            JOIN
                events e ON n.event_id = e.id
            WHERE
                n.event_id = :event_id  AND n.player = :player
            GROUP BY
                player';
        $params = ['event_id' => $eventid, 'player' => $playername];
        return Db::optionalString($sql, $params) !== null;
    }

    // TODO: remove ignore functionality
    public function __construct(int $event_id, string $playername)
    {
        $this->ignored = 0;
        $sql = '
            SELECT
                deck AS deck_id, medal, ignored, drop_round, initial_byes, initial_seed
            FROM
                entries
            WHERE
                event_id = :event_id AND player = :player';
        $params = ['event_id' => $event_id, 'player' => $playername];
        $entry = Db::selectOnlyOrNull($sql, EntryDto::class, $params);
        if ($entry == null) {
            throw new NotFoundException('Entry for ' . $playername . ' in ' . $event_id . ' not found');
        }
        $deck_id = null;
        foreach (get_object_vars($entry) as $key => $value) {
            if ($key == 'deck_id') {
                $deck_id = $value;
            } else {
                $this->{$key} = $value;
            }
        }

        $this->event = new Event($event_id);
        $this->player = new Player($playername);

        if ($deck_id != null) {
            $this->deck = new Deck($deck_id);
        } else {
            $this->deck = null;
        }
    }

    public function recordString(): string
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
            return $wins . '-' . $losses;
        }
        return $wins . '-' . $losses . '-' . $draws;
    }

    /**
     * @return list<Matchup>
     */
    public function getMatches(): array
    {
        return $this->player->getMatchesEvent($this->event->name);
    }

    public function canDelete(): bool
    {
        $matches = $this->getMatches();
        return count($matches) == 0;
    }

    public function dropped(): bool
    {
        return $this->drop_round > 0;
    }

    public function canCreateDeck(string $username): bool
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
    public function setIgnored(int $new_ignored): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE entries SET ignored = ? WHERE player = ? and event_id = ?');
        $playername = $this->player->name;
        $event_id = $this->event->id;
        $stmt->bind_param('isd', $new_ignored, $playername, $event_id);
        $stmt->execute();
        $stmt->close();
    }

    public function removeEntry(): bool
    {
        Db::begin('remove_entry');

        // if the player being unreg'd entered a deck list, remove it
        if ($this->deck) {
            $this->deck->delete();
        }

        $sql = 'DELETE FROM entries WHERE event_id = :event_id AND player = :player';
        $params = [
            'event_id' => $this->event->id,
            'player' => $this->player->name,
        ];
        $removed = Db::update($sql, $params) > 0;

        $sql = 'DELETE FROM standings WHERE event = :event AND player = :player';
        $params = [
            'event' => $this->event->name,
            'player' => $this->player->name,
        ];
        Db::execute($sql, $params);

        Db::commit('remove_entry');

        return $removed;
    }

    public function setInitialByes(int $byeqty): void
    {
        $sql = '
            UPDATE
                entries
            SET
                initial_byes = :initial_byes
            WHERE
                player = :player AND event_id = :event_id';
        $params = [
            'initial_byes' => $byeqty,
            'player' => $this->player->name,
            'event_id' => $this->event->id,
        ];
        Db::execute($sql, $params);
    }

    public function setInitialSeed(int $byeqty): void
    {
        $sql = '
            UPDATE
                entries
            SET
                initial_seed = :initial_seed
            WHERE
                player = :player AND event_id = :event_id';
        $params = [
            'initial_seed' => $byeqty,
            'player' => $this->player->name,
            'event_id' => $this->event->id,
        ];
        Db::execute($sql, $params);
    }
}
