<?php

declare(strict_types=1);

namespace Gatherling\Models;

use DateTimeZone;
use Gatherling\Exceptions\NotFoundException;
use Gatherling\Exceptions\ValidationException;
use Safe\DateTimeImmutable;

use function Gatherling\Helpers\datetime;
use function Gatherling\Helpers\db;
use function Gatherling\Helpers\logger;

class Event
{
    public string $name;
    public int $id;

    public int $season;
    public int $number;
    public string $format;

    public DateTimeImmutable $start;
    public int $kvalue;
    public int $active;
    public int $finalized;
    public int $prereg_allowed;
    public string $threadurl;
    public string $reporturl;
    public string $metaurl;
    public int $private;
    public int $client;

    public int $player_editdecks;

    // Class associations
    public ?string $series = null; // belongs to Series
    public string $host; // has one Player - host
    public ?string $cohost; // has one Player - cohost

    // Subevents
    public int $mainrounds;
    public string $mainstruct;
    public ?int $mainid; // Has one main subevent
    public int $finalrounds;
    public string $finalstruct;
    public ?int $finalid; // Has one final subevent

    // Pairing/event related
    public int $current_round;
    public Standings $standing;
    public int $player_reportable;
    public int $player_reported_draws;
    public int $prereg_cap; // Cap on player initiated registration
    public int $late_entry_limit; // How many rounds we let people perform late entries

    public int $private_decks; // Toggle to disable deck privacy for active events. Allows the metagame page to display during an active event and lets deck lists be viewed if disabled.
    public int $private_finals; // As above, but for finals

    public ?int $hastrophy;
    private bool $new;

    public function __construct(int|string $name = '')
    {
        if ($name == '') {
            $this->id = 0;
            $this->name = '';
            $this->season = 0;
            $this->number = 0;
            $this->format = '';
            $this->mainrounds = 0;
            $this->mainstruct = '';
            $this->finalrounds = 0;
            $this->finalstruct = '';
            $this->host = '';
            $this->cohost = null;
            $this->threadurl = '';
            $this->reporturl = '';
            $this->metaurl = '';
            $this->private = 0;
            $this->start = datetime('+1 week');
            $this->kvalue = 16;
            $this->finalized = 0;
            $this->prereg_allowed = 0;
            $this->hastrophy = 0;
            $this->new = true;
            $this->active = 0;
            $this->current_round = 0;
            $this->player_reportable = 0;
            $this->prereg_cap = 0;
            $this->player_editdecks = 1;
            $this->private_decks = 1;
            $this->private_finals = 1;
            $this->player_reported_draws = 0;
            $this->late_entry_limit = 0;
            $this->private = 0;
            $this->client = 1;

            return;
        }

        $sql = '
            SELECT id, name, format, host, cohost, series, season, number, start, kvalue, finalized,
                    prereg_allowed, threadurl, metaurl, reporturl, active, current_round, player_reportable,
                    player_editdecks, prereg_cap, private_decks, private_finals, player_reported_draws,
                    late_entry_limit, `private`, client
                FROM events
                WHERE ';
        if (is_numeric($name)) {
            $sql .= 'id = :id';
            $params = ['id' => $name];
        } else {
            $sql .= 'name = :name';
            $params = ['name' => $name];
        }
        $event = db()->selectOnly($sql, EventDto::class, $params);
        $this->id = $event->id;
        $this->name = $event->name;
        $this->format = $event->format;
        $this->host = $event->host;
        $this->cohost = $event->cohost;
        $this->series = $event->series;
        $this->season = $event->season;
        $this->number = $event->number;
        $this->start = datetime($event->start);
        $this->kvalue = $event->kvalue;
        $this->finalized = $event->finalized;
        $this->prereg_allowed = $event->prereg_allowed;
        $this->threadurl = $event->threadurl;
        $this->metaurl = $event->metaurl;
        $this->reporturl = $event->reporturl;
        $this->active = $event->active;
        $this->current_round = $event->current_round;
        $this->player_reportable = $event->player_reportable;
        $this->player_editdecks = $event->player_editdecks;
        $this->prereg_cap = $event->prereg_cap;
        $this->private_decks = $event->private_decks;
        $this->private_finals = $event->private_finals;
        $this->player_reported_draws = $event->player_reported_draws;
        $this->late_entry_limit = $event->late_entry_limit;
        $this->private = $event->private;
        $this->client = $event->client;

        $this->standing = new Standings($this->name, '0');

        // Main rounds
        $this->mainid = null;
        $this->mainrounds = 0;
        $this->mainstruct = '';
        $sql = 'SELECT id AS mainid, rounds, type FROM subevents WHERE parent = :parent AND timing = 1';
        $params = ['parent' => $this->name];
        $subevent = db()->selectOnlyOrNull($sql, EventSubeventDto::class, $params);
        if ($subevent) {
            $this->mainid = $subevent->mainid;
            $this->mainrounds = $subevent->rounds;
            $this->mainstruct = $subevent->type;
        }

        // Final rounds
        $this->finalid = null;
        $this->finalrounds = 0;
        $this->finalstruct = '';
        $sql = 'SELECT id AS mainid, rounds, type FROM subevents WHERE parent = :parent AND timing = 2';
        $params = ['parent' => $this->name];
        $subevent = db()->selectOnlyOrNull($sql, EventSubeventDto::class, $params);
        if ($subevent) {
            $this->finalid = $subevent->mainid;
            $this->finalrounds = $subevent->rounds;
            $this->finalstruct = $subevent->type;
        }

        // Trophy count
        $sql = 'SELECT COUNT(*) AS cnt FROM trophies WHERE event = :event';
        $params = ['event' => $this->name];
        $this->hastrophy = db()->int($sql, $params);

        $this->new = false;
    }

    public function __toString(): string
    {
        return "Gatherling/Event($this->name)";
    }

    public static function createEvent(
        string $year,
        string $month,
        string $day,
        string $hour,
        string $naming,
        string $name,
        string $format,
        string $host,
        string $cohost,
        string $kvalue,
        string $series,
        string $season,
        string $number,
        string $threadurl,
        string $metaurl,
        string $reporturl,
        string $prereg_allowed,
        string $player_reportable,
        string $late_entry_limit,
        string $private,
        int $mainrounds,
        string $mainstruct,
        int $finalrounds,
        string $finalstruct,
        string $client
    ): Event {
        $event = new self('');
        $event->start = datetime("{$year}-{$month}-{$day} {$hour}:00");

        if (empty($season) && empty($number)) {
            $_series = new Series($series);
            $mostRecentEvent = $_series->mostRecentEvent();
            $season = $mostRecentEvent ? $mostRecentEvent->season : 0;
            $number = $mostRecentEvent ? $mostRecentEvent->number + 1 : 1;
        }

        if ($naming === 'auto') {
            $event->name = sprintf('%s %d.%02d', $series, $season, $number);
        } else {
            $event->name = $name;
        }

        $event->format = $format;
        $event->host = $host;
        $event->cohost = $cohost;
        $event->kvalue = (int) $kvalue;
        $event->series = $series;
        $event->season = (int) $season;
        $event->number = (int) $number;
        $event->threadurl = $threadurl;
        $event->metaurl = $metaurl;
        $event->reporturl = $reporturl;
        $event->private = (int) $private;
        $event->client = (int) $client;

        $event->prereg_allowed = (int) $prereg_allowed;

        $event->player_reportable = (int) $player_reportable;

        $event->late_entry_limit = (int) $late_entry_limit;

        if ($mainrounds === 0) {
            $mainrounds = 3;
        }
        if ($mainstruct == '') {
            $mainstruct = 'Swiss';
        }
        $event->mainrounds = $mainrounds;
        $event->mainstruct = $mainstruct;
        if ($finalstruct === '') {
            $finalstruct = 'Single Elimination';
        }
        $event->finalrounds = $finalrounds;
        $event->finalstruct = $finalstruct;
        $event->save();

        return $event;
    }

    private function validate(): void
    {
        if (db()->optionalInt('SELECT 1 FROM formats WHERE name = :name', ['name' => $this->format]) === null) {
            throw new ValidationException("Invalid format {$this->format}");
        }
        if (db()->optionalInt('SELECT 1 FROM players WHERE name = :name', ['name' => $this->host]) === null) {
            throw new ValidationException("Invalid host {$this->host}");
        }
        if ($this->cohost !== null && db()->optionalInt('SELECT 1 FROM players WHERE name = :name', ['name' => $this->cohost]) === null) {
            throw new ValidationException("Invalid cohost {$this->cohost}");
        }
        if (db()->optionalInt('SELECT 1 FROM series WHERE name = :name', ['name' => $this->series]) === null) {
            throw new ValidationException("Invalid series {$this->series}");
        }
        if (db()->optionalInt('SELECT 1 FROM client WHERE id = :id', ['id' => $this->client]) === null) {
            throw new ValidationException("Invalid client {$this->client}");
        }
    }

    public function save(): void
    {
        if ($this->cohost == '') {
            $this->cohost = null;
        }
        if ($this->finalized) {
            $this->active = 0;
        }

        $this->validate();

        if ($this->new) {
            $sql = '
                INSERT INTO events (name, start, format, host, cohost, kvalue, number, season, series, threadurl, reporturl,
                                    metaurl, prereg_allowed, finalized, player_reportable, prereg_cap, player_editdecks,
                                    private_decks, private_finals, player_reported_draws, late_entry_limit, `private`, client)
                     VALUES (:name, :start, :format, :host, :cohost, :kvalue, :number, :season, :series, :threadurl, :reporturl,
                             :metaurl, :prereg_allowed, 0, :player_reportable, :prereg_cap, :player_editdecks,
                             :private_decks, :private_finals, :player_reported_draws, :late_entry_limit, :private, :client)';
            $params = [
                'name' => $this->name,
                'start' => $this->start,
                'format' => $this->format,
                'host' => $this->host,
                'cohost' => $this->cohost,
                'kvalue' => $this->kvalue,
                'number' => $this->number,
                'season' => $this->season,
                'series' => $this->series,
                'threadurl' => $this->threadurl,
                'reporturl' => $this->reporturl,
                'metaurl' => $this->metaurl,
                'prereg_allowed' => $this->prereg_allowed,
                'player_reportable' => $this->player_reportable,
                'prereg_cap' => $this->prereg_cap,
                'player_editdecks' => $this->player_editdecks,
                'private_decks' => $this->private_decks,
                'private_finals' => $this->private_finals,
                'player_reported_draws' => $this->player_reported_draws,
                'late_entry_limit' => $this->late_entry_limit,
                'private' => $this->private,
                'client' => $this->client,
            ];
            db()->execute($sql, $params);

            $this->newSubevent($this->mainrounds, 1, $this->mainstruct);
            $this->newSubevent($this->finalrounds, 2, $this->finalstruct);
        } else {
            $sql = '
                UPDATE events
                   SET start = :start, format = :format, host = :host, cohost = :cohost, kvalue = :kvalue,
                       number = :number, season = :season, series = :series, threadurl = :threadurl, reporturl = :reporturl,
                       metaurl = :metaurl, finalized = :finalized, prereg_allowed = :prereg_allowed, active = :active,
                       current_round = :current_round, player_reportable = :player_reportable, prereg_cap = :prereg_cap,
                       player_editdecks = :player_editdecks, private_decks = :private_decks, private_finals = :private_finals,
                       player_reported_draws = :player_reported_draws, late_entry_limit = :late_entry_limit,
                       `private` = :private, client = :client
                 WHERE name = :name';
            $params = [
                'start' => $this->start,
                'format' => $this->format,
                'host' => $this->host,
                'cohost' => $this->cohost,
                'kvalue' => $this->kvalue,
                'number' => $this->number,
                'season' => $this->season,
                'series' => $this->series,
                'threadurl' => $this->threadurl,
                'reporturl' => $this->reporturl,
                'metaurl' => $this->metaurl,
                'finalized' => $this->finalized,
                'prereg_allowed' => $this->prereg_allowed,
                'active' => $this->active,
                'current_round' => $this->current_round,
                'player_reportable' => $this->player_reportable,
                'prereg_cap' => $this->prereg_cap,
                'player_editdecks' => $this->player_editdecks,
                'private_decks' => $this->private_decks,
                'private_finals' => $this->private_finals,
                'player_reported_draws' => $this->player_reported_draws,
                'late_entry_limit' => $this->late_entry_limit,
                'private' => $this->private,
                'client' => $this->client,
                'name' => $this->name,
            ];
            db()->execute($sql, $params);

            if ($this->mainid == null) {
                $this->newSubevent($this->mainrounds, 1, $this->mainstruct);
            } else {
                $main = new Subevent($this->mainid);
                $main->rounds = $this->mainrounds;
                $main->type = $this->mainstruct;
                $main->save();
            }

            if ($this->finalid == null) {
                $this->newSubevent($this->finalrounds, 2, $this->finalstruct);
            } else {
                $final = new Subevent($this->finalid);
                $final->rounds = $this->finalrounds;
                $final->type = $this->finalstruct;
                $final->save();
            }
        }
    }

    private function newSubevent(int $rounds, int $timing, string $type): void
    {
        $sql = '
            INSERT INTO
                subevents
                (parent, rounds, timing, type)
            VALUES
                (:name, :rounds, :timing, :type)';
        $params = [
            'name' => $this->name,
            'rounds' => $rounds,
            'timing' => $timing,
            'type' => $type,
        ];
        db()->execute($sql, $params);
    }

    public function getPlaceDeck(string $placing = '1st'): ?Deck
    {
        $sql = '
            SELECT
                n.deck
            FROM
                entries n, events e
            WHERE
                n.event_id = e.id AND n.medal = :medal AND e.name = :name';
        $params = ['medal' => $placing, 'name' => $this->name];
        $deckId = db()->optionalInt($sql, $params);
        if (!$deckId) {
            return null;
        }
        return new Deck($deckId);
    }

    public function decklistsVisible(): bool
    {
        return ($this->finalized && !$this->active) || $this->private_decks == 0 || ($this->current_round > $this->mainrounds && !$this->private_finals);
    }

    /** @return list<Deck> */
    public function getDecks(): array
    {
        $sql = 'SELECT deck FROM entries WHERE event_id = :event_id AND deck IS NOT NULL';
        $params = ['event_id' => $this->id];
        $deckIds = db()->ints($sql, $params);
        return array_map(fn(int $deckid) => new Deck($deckid), $deckIds);
    }

    /** @return list<array{medal: string, player: string, deck: ?int}> */
    public function getFinalists(): array
    {
        $sql = "
            SELECT
               medal, player, deck
            FROM
                entries
            WHERE
                event_id = :event_id AND medal != 'dot'
            ORDER BY
                medal, player";
        $params = ['event_id' => $this->id];
        $finalists = db()->select($sql, FinalistDto::class, $params);
        return array_map(
            fn(FinalistDto $finalist) => [
                'medal' => $finalist->medal,
                'player' => $finalist->player,
                'deck' => $finalist->deck,
            ],
            $finalists
        );
    }

    /**
     * @param list<string> $t4
     * @param list<string> $t8
     */
    public function setFinalists(string $win, ?string $sec, array $t4, array $t8): void
    {
        db()->begin('set_finalists');
        $sql = "UPDATE entries SET medal = 'dot' WHERE event_id = :event_id";
        db()->execute($sql, ['event_id' => $this->id]);
        $sql = 'UPDATE entries SET medal = :medal WHERE event_id = :event_id AND player = :player';
        db()->execute($sql, ['medal' => '1st', 'event_id' => $this->id, 'player' => $win]);
        db()->execute($sql, ['medal' => '2nd', 'event_id' => $this->id, 'player' => $sec]);
        foreach ($t4 as $player) {
            db()->execute($sql, ['medal' => 't4', 'event_id' => $this->id, 'player' => $player]);
        }
        foreach ($t8 as $player) {
            db()->execute($sql, ['medal' => 't8', 'event_id' => $this->id, 'player' => $player]);
        }
        db()->commit('set_finalists');
    }

    public function isHost(string $name): bool
    {
        $ishost = strcasecmp($name, $this->host) == 0;
        $iscohost = !is_null($this->cohost) && strcasecmp($name, $this->cohost) == 0;
        return $ishost || $iscohost;
    }

    public function isOrganizer(string $name): bool
    {
        if ($this->series === null || $this->series === '') {
            return false;
        }
        if (!Series::exists($this->series)) {
            return false;
        }
        $series = new Series($this->series);
        return $series->isOrganizer($name);
    }

    public function authCheck(?string $playername): bool
    {
        if ($playername == null) {
            return false;
        }
        $player = new Player($playername);

        if (
            $player->isSuper() ||
            $this->isHost($playername) ||
            $this->isOrganizer($playername)
        ) {
            return true;
        }

        return false;
    }

    public function getPlayerCount(): int
    {
        return Database::singleResultSingleParam('SELECT count(*) FROM entries WHERE event_id = ?', 'd', $this->id);
    }

    /** @return list<string> */
    public function getPlayers(): array
    {
        $sql = 'SELECT player FROM entries WHERE event_id = :event_id ORDER BY medal, player';
        return db()->strings($sql, ['event_id' => $this->id]);
    }

    /** @return list<string> */
    public function getRegisteredPlayers(bool $checkActive = false): array
    {
        $players = $this->getPlayers();
        $registeredPlayers = [];

        foreach ($players as $player) {
            $entry = new Entry($this->id, $player);
            if (is_null($entry->deck)) {
                continue;
            }
            $activeCheck = $checkActive ? (new Standings($this->name, $player))->active : true;
            if ($entry->deck->isValid() && $activeCheck) {
                $registeredPlayers[] = $player;
            }
        }

        return $registeredPlayers;
    }

    public function hasRegistrant(string $playername): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT count(player) FROM entries WHERE event_id = ? AND player = ?');
        $stmt->bind_param('ds', $this->id, $playername);
        $stmt->execute();
        $stmt->bind_result($isPlaying);
        $stmt->fetch();
        $stmt->close();

        return $isPlaying > 0;
    }

    /** @return list<Subevent> */
    public function getSubevents(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM subevents WHERE parent = ? ORDER BY timing');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($subeventid);

        $subids = [];
        while ($stmt->fetch()) {
            $subids[] = $subeventid;
        }
        $stmt->close();

        $subs = [];
        foreach ($subids as $subid) {
            $subs[] = new Subevent($subid);
        }

        return $subs;
    }

    /** @return list<string> */
    public function getEntriesByDateTime(): array
    {
        $sql = 'SELECT player FROM entries WHERE event_id = :event_id AND deck ORDER BY DATE(`registered_at`) ASC';
        $params = ['event_id' => $this->id];
        return db()->strings($sql, $params);
    }

    /** @return list<string> */
    public function getEntriesByMedal(): array
    {
        $sql = 'SELECT player FROM entries WHERE event_id = :event_id AND deck ORDER BY medal, player';
        $params = ['event_id' => $this->id];
        return db()->strings($sql, $params);
    }

    /** @return list<Entry> */
    public function getEntries(): array
    {
        $players = $this->getPlayers();

        $entries = [];
        foreach ($players as $player) {
            $entries[] = new Entry($this->id, $player);
        }

        return $entries;
    }

    /** @return list<Entry> */
    public function getRegisteredEntries(bool $deleteinvalid = false, bool $skip_invalid = false): array
    {
        $players = $this->getPlayers();

        $entries = [];
        foreach ($players as $player) {
            $entry = new Entry($this->id, $player);
            if (is_null($entry->deck) || !$entry->deck->isValid()) {
                if ($deleteinvalid) {
                    $entry->removeEntry();
                    continue;
                }
                if ($skip_invalid) {
                    continue;
                }
            }
            $entries[] = $entry;
        }

        return $entries;
    }

    //Players that doesn't play a single game and doesn't get a bye as well
    public function dropBlankEntries(): void
    {
        $players = $this->getPlayers();
        foreach ($players as $player) {
            $entry = new Entry($this->id, $player);
            if ($entry->canDelete()) {
                $this->removeEntry($player);
            }
        }
    }

    public function removeEntry(string $playername): bool
    {
        try {
            $entry = new Entry($this->id, $playername);
            return $entry->removeEntry();
        } catch (NotFoundException $e) {
            logger()->info("Tried to remove $playername from {$this->name} but no entry was found.");
            return true;
        }
    }

    public function addPlayer(string $playername): bool
    {
        $playername = trim($playername);
        if ($playername === '') {
            return false;
        }
        $series = new Series($this->series);
        $playerIsBanned = $series->isPlayerBanned($playername);
        if ($playerIsBanned) {
            return false;
        }
        $entry = Entry::findByEventAndPlayer($this->id, $playername);
        $added = false;
        if (is_null($entry)) {
            $player = Player::findOrCreateByName($playername);
            $sql = '
                INSERT INTO entries (event_id, player, registered_at)
                     VALUES (:event_id, :player, NOW())';
            $params = ['event_id' => $this->id, 'player' => $player->name];
            db()->execute($sql, $params);
            //For late registration. Check to see if event is active, if so, create entry for player in standings
            if ($this->active == 1) {
                $standing = new Standings($this->name, $playername);
                $standing->save();
            }
            $added = true;
        }

        return $added;
    }

    public function dropPlayer(string $playername, int $round = -1): void
    {
        if ($round == -1) {
            $round = $this->current_round;
        }
        db()->begin('drop_player');
        $sql = 'UPDATE entries SET drop_round = :round WHERE event_id = :event_id AND player = :player';
        $params = ['round' => $round, 'event_id' => $this->id, 'player' => $playername];
        db()->execute($sql, $params);
        $sql = 'UPDATE standings SET active = 0 WHERE event = :event AND player = :player';
        $params = ['event' => $this->name, 'player' => $playername];
        db()->execute($sql, $params);
        db()->commit('drop_player');
    }

    public function undropPlayer(string $playername): void
    {
        db()->begin('undrop_player');
        $sql = 'UPDATE entries SET drop_round = 0 WHERE event_id = :event_id AND player = :player';
        $params = ['event_id' => $this->id, 'player' => $playername];
        db()->execute($sql, $params);
        $sql = 'UPDATE standings SET active = 1 WHERE event = :event AND player = :player';
        $params = ['event' => $this->name, 'player' => $playername];
        db()->execute($sql, $params);
        db()->commit('undrop_player');
    }

    /** @return list<Matchup> */
    public function getMatches(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT m.id FROM matches m, subevents s, events e
      WHERE m.subevent = s.id AND s.parent = e.name AND e.name = ?
      ORDER BY s.timing, m.round, m.id');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($matchid);

        $mids = [];
        while ($stmt->fetch()) {
            $mids[] = $matchid;
        }
        $stmt->close();

        $matches = [];
        foreach ($mids as $mid) {
            $matches[] = new Matchup($mid);
        }

        return $matches;
    }

    /** @return list<Matchup> */
    public function getRoundMatches(int|string $roundnum): array
    {
        $all_rounds = $roundnum == 'ALL';
        $roundnum = intval($roundnum);
        if ($roundnum > $this->mainrounds) {
            $subevnum = 2;
            $roundnum = $roundnum - $this->mainrounds;
        } else {
            $subevnum = 1;
        }

        if ($all_rounds) {
            $sql = "
                SELECT
                    m.id
                FROM
                    matches m, subevents s, events e
                WHERE
                    m.subevent = s.id AND s.parent = e.name AND e.name = :name AND s.timing = :timing AND m.result <> 'P'";
            $params = ['name' => $this->name, 'timing' => $subevnum];
        } else {
            $sql = '
                SELECT
                    m.id
                FROM
                    matches m, subevents s, events e
                WHERE
                    m.subevent = s.id AND s.parent = e.name AND e.name = :name AND s.timing = :timing AND m.round = :round';
            $params = ['name' => $this->name, 'timing' => $subevnum, 'round' => $roundnum];
        }

        $mids = db()->ints($sql, $params);

        $matches = [];
        foreach ($mids as $mid) {
            $matches[] = new Matchup($mid);
        }

        return $matches;
    }

    /**
     * This is a really specific method, used to show how many matches someone has played in a specific league round.
     * Used on Player CP and nowhere else.
     */
    public function getPlayerLeagueMatchCount(string $player_name): int
    {
        if ($this->current_round > $this->mainrounds) {
            $subevnum = 2;
            $roundnum = $this->current_round - $this->mainrounds;
        } else {
            $subevnum = 1;
            $roundnum = $this->current_round;
        }
        $sql = '
            SELECT
                COUNT(m.id)
            FROM
                matches m, subevents s, events e
            WHERE
                m.subevent = s.id AND s.parent = e.name AND e.name = :name AND
                s.timing = :timing AND m.round = :round AND (m.playera = :player OR m.playerb = :player)';
        $params = ['name' => $this->name, 'timing' => $subevnum, 'round' => $roundnum, 'player' => $player_name];
        return db()->int($sql, $params);
    }

    // In preparation for automating the pairings this function will add match the next pairing
    // results should be equal to 'P' for match in progress
    public function addPairing(Standings $playera, Standings $playerb, int $round, string $result): int
    {
        $id = $this->mainid;
        if ($result == 'BYE') {
            $verification = 'verified';
        } else {
            $verification = 'unverified';
        }

        if ($round > $this->mainrounds) {
            $id = $this->finalid;
            $round = $round - $this->mainrounds;
        }
        $sql = '
            INSERT INTO
                matches
                (playera, playerb, round, subevent, result, verification)
            VALUES
                (:playera, :playerb, :round, :subevent, :result, :verification)';
        $params = [
            'playera' => $playera->player,
            'playerb' => $playerb->player,
            'round' => $round,
            'subevent' => $id,
            'result' => $result,
            'verification' => $verification,
        ];
        return db()->insert($sql, $params);
    }

    public function addMatch(Standings $playera, Standings $playerb, int $round = -99, string $result = 'P', int $playera_wins = 0, int $playerb_wins = 0): void
    {
        $draws = 0;
        $id = $this->mainid;

        if ($round > $this->mainrounds) {
            $id = $this->finalid;
            $round = $round - $this->mainrounds;
        }

        if ($round == -99) {
            $round = $this->current_round;
        }

        if ($result == 'BYE' or $result == 'D' or $result == 'League' or $playera_wins > 0 or $playerb_wins > 0) {
            $verification = 'verified';
        } else {
            $verification = 'unverified';
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO matches(playera, playerb, round, subevent, result, playera_wins, playera_losses, playera_draws, playerb_wins, playerb_losses, playerb_draws, verification) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssddsdddddds', $playera->player, $playerb->player, $round, $id, $result, $playera_wins, $playerb_wins, $draws, $playerb_wins, $playera_wins, $draws, $verification); // draws have not been implemented yet so I just assign a zero for now
        $stmt->execute();
        $stmt->close();
    }

    // Assigns trophies based on the finals matches which are entered.
    public function assignTrophiesFromMatches(): void
    {
        $win = null;
        $sec = null;
        $t4 = [];
        $t8 = [];

        $finalRounds = $this->finalrounds;
        $totalRounds = $this->mainrounds + $finalRounds;
        // If this event only has main rounds then those are effectively the "final rounds".
        if ($finalRounds === 0) {
            $finalRounds = $this->mainrounds;
        }

        $finalMatches = $this->getRoundMatches($totalRounds);
        if (!empty($finalMatches)) {
            $finalMatch = $finalMatches[0];
            $win = $finalMatch->getWinner();
            $sec = $finalMatch->getLoser();
        }

        if ($finalRounds >= 2) {
            $semiMatches = $this->getRoundMatches($totalRounds - 1);
            foreach ($semiMatches as $match) {
                $loser = $match->getLoser();
                if ($loser !== null) {
                    $t4[] = $loser;
                }
            }
        }

        if ($finalRounds >= 3) {
            $quarterMatches = $this->getRoundMatches($totalRounds - 2);
            foreach ($quarterMatches as $match) {
                $loser = $match->getLoser();
                if ($loser !== null) {
                    $t8[] = $loser;
                }
            }
        }

        $this->setFinalists($win, $sec, $t4, $t8);
    }

    public static function exists(string $name): bool
    {
        $db = Database::getConnection();
        $sql = 'SELECT name FROM events WHERE ';
        if (is_numeric($name)) {
            $sql .= 'id = ?';
            $pt = 'd';
        } else {
            $sql .= 'name = ?';
            $pt = 's';
        }

        $stmt = $db->prepare($sql);
        $stmt->bind_param($pt, $name);
        $stmt->execute();
        $stmt->store_result();
        $event_exists = $stmt->num_rows > 0;
        $stmt->close();

        return $event_exists;
    }

    public static function findMostRecentByHost(string $host_name): ?self
    {
        // TODO: This should show the closest non-finalized event.
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM events WHERE host = ? OR cohost = ? ORDER BY start DESC LIMIT 1');
        $stmt->bind_param('ss', $host_name, $host_name);
        $stmt->execute();
        $event_name = '';
        $stmt->bind_result($event_name);
        $event_exists = $stmt->fetch();
        $stmt->close();
        if ($event_exists) {
            return new self($event_name);
        }
        return null;
    }

    public function findPrev(): ?self
    {
        if ($this->number == 0) {
            return null;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM events WHERE series = ? AND season = ? AND number = ? LIMIT 1');
        $num = $this->number - 1;
        $stmt->bind_param('sdd', $this->series, $this->season, $num);
        $stmt->execute();
        $stmt->bind_result($event_name);
        $exists = $stmt->fetch();
        $stmt->close();
        if ($exists) {
            return new self($event_name);
        }
        return null;
    }

    public function findNext(): ?self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM events WHERE series = ? AND season = ? AND number = ? LIMIT 1');
        $num = $this->number + 1;
        $stmt->bind_param('sdd', $this->series, $this->season, $num);
        $stmt->execute();
        $stmt->bind_result($event_name);
        $exists = $stmt->fetch();
        $stmt->close();
        if ($exists) {
            return new self($event_name);
        }
        return null;
    }

    public static function count(): int
    {
        return db()->int('SELECT COUNT(name) FROM events');
    }

    public static function largestEventNum(): int
    {
        return db()->int('SELECT COALESCE(MAX(number), 0) FROM events WHERE number != 128'); // 128 is "special"
    }

    /** @return list<self> */
    public static function getNextPreRegister(int $num = 20): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM events WHERE prereg_allowed = 1 AND active = 0 AND finalized = 0 AND private = 0 AND DATE_SUB(start, INTERVAL 0 MINUTE) > NOW() ORDER BY start LIMIT ?');
        // 180 minute interal in Date_Sub is to compensate for time zone difference from Server and Eastern Standard Time which is what all events are quoted in
        $stmt->bind_param('d', $num);
        $stmt->execute();
        $stmt->bind_result($nextevent);
        $event_names = [];
        while ($stmt->fetch()) {
            $event_names[] = $nextevent;
        }
        $stmt->close();
        $events = [];
        foreach ($event_names as $eventname) {
            $events[] = new self($eventname);
        }

        return $events;
    }

    /** @return list<self> */
    public static function getUpcomingEvents(string $playername): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.name FROM events e, entries n WHERE n.event_id = e.id AND n.player = ? AND active = 0 AND finalized = 0 ORDER BY start');
        $stmt->bind_param('s', $playername);
        $stmt->execute();
        $stmt->bind_result($nextevent);
        $event_names = [];
        while ($stmt->fetch()) {
            $event_names[] = $nextevent;
        }
        $stmt->close();
        $events = [];
        foreach ($event_names as $eventname) {
            $events[] = new self($eventname);
        }

        return $events;
    }

    /** @return array{adjustment: int, reason: string} */
    public function getSeasonPointAdjustment(string $player): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT adjustment, reason FROM season_points WHERE event = ? AND player = ?');
        $stmt or exit($db->error);
        $stmt->bind_param('ss', $this->name, $player);
        $stmt->execute();
        $stmt->bind_result($adjustment, $reason);
        $exists = $stmt->fetch() != null;
        $stmt->close();
        if ($exists) {
            return ['adjustment' => $adjustment, 'reason' => $reason];
        }
        return ['adjustment' => 0, 'reason' => ''];
    }

    // Adjusts the season points for $player for this event by $points, with the reason $reason
    public function setSeasonPointAdjustment(string $player, int $points, string $reason): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT player FROM season_points WHERE event = ? AND player = ?');
        $stmt or exit($db->error);
        $stmt->bind_param('ss', $this->name, $player);
        $stmt->execute();
        $exists = $stmt->fetch() != null;
        $stmt->close();
        if ($exists) {
            $stmt = $db->prepare('UPDATE season_points SET reason = ?, adjustment = ? WHERE event = ? AND player = ?');
            $stmt->bind_param('sdss', $reason, $points, $this->name, $player);
        } else {
            $stmt = $db->prepare('INSERT INTO season_points(series, season, event, player, adjustment, reason) values(?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sdssds', $this->series, $this->season, $this->name, $player, $points, $reason);
        }
        $stmt->execute();
        $stmt->close();
    }

    public static function trophySrc(string $eventname): string
    {
        return 'displayTrophy.php?event=' . rawurlencode($eventname);
    }

    public function isLeague(): bool
    {
        $test = $this->current_round;
        if ($test <= ($this->finalrounds + $this->mainrounds)) {
            if ($test > $this->mainrounds) {
                $structure = $this->finalstruct;
            } else {
                $structure = $this->mainstruct;
            }

            return $structure == 'League' || $structure == 'League Match';
        }

        return false;
    }

    public function leagueLength(): int
    {
        return 6; // TODO: This should be customizable.
    }

    // All this should probably go somewhere else
    // Pairs the round which is currently running.
    // This should probably be in Standings?
    public function pairCurrentRound(bool $skip_invalid = false): void
    {
        //Check if all matches in the current round are finished
        if (count($this->unfinishedMatches()) === 0) {
            //Check to see if we are main rounds or final, get structure
            $test = $this->current_round;
            if ($test < ($this->finalrounds + $this->mainrounds)) {
                if ($test >= $this->mainrounds) {
                    // In the final rounds.
                    $structure = $this->finalstruct;
                    $subevent_id = $this->finalid;
                    $round = 'final';
                } else {
                    $structure = $this->mainstruct;
                    $subevent_id = $this->mainid;
                    $round = 'main';
                }

                $lock_db = db()->getLock((string) $subevent_id);
                if ($lock_db !== 1) {
                    // Someone else is already pairing the round. Ignore.
                    return;
                }

                // Run matching function
                switch ($structure) {
                    case 'Swiss':
                    case 'Swiss (Blossom)':
                        $this->swissPairingBlossom($subevent_id, $skip_invalid);
                        break;
                    case 'Single Elimination':
                        $this->singleElimination($round);
                        break;
                    case 'League':
                        //$this->current_round ++;
                        //$this->save();
                        break;
                }

                db()->releaseLock((string) $subevent_id);
            } else {
                $this->active = 0;
                $this->finalized = 1;
                $this->save();
                $this->assignMedals();
                $ratings = new Ratings();
                $ratings->calcFinalizedEventRatings($this->name, $this->format, $this->start);
            }
            $this->current_round++;
            $this->save();
        }
    }

    // Pairs the current swiss round by using the Blossom method
    public function swissPairingBlossom(int $subevent_id, bool $skip_invalid): void
    {
        Standings::resetMatched($this->name);
        $active_entries = Entry::getActivePlayers($this->id);

        if ($skip_invalid) {
            $this->skipInvalidDecks($active_entries);
        }
        $this->assignInitialByes($active_entries, $this->current_round + 1);

        $sql = '
            SELECT
                player,
                COALESCE(byes, 0) AS byes,
                COALESCE(score, 0) AS score
            FROM
                standings
            WHERE
                event = :event  AND active = 1 AND matched = 0
            ORDER BY
                RAND()';
        $activePlayersInfo = db()->select($sql, PairingPlayerDto::class, ['event' => $this->name]);
        $activePlayers = [];

        if (count($activePlayersInfo) > 0) {
            $bye_data = null;
            if (count($activePlayersInfo) % 2 != 0) {
                $bye_data = ['player' => $this->getByeProxyName(), 'score' =>  0, 'opponents' => [], 'paired' => false];
            }

            for ($i = 0; $i < count($activePlayersInfo); $i++) {
                $activePlayers[] = (array) $activePlayersInfo[$i];
                $list_opponents = $this->getActiveOpponents($activePlayersInfo[$i]->player, $subevent_id);

                $standing = new Standings($this->name, $activePlayersInfo[$i]->player);

                if ($bye_data && $standing->byes > 0 && count($list_opponents) < (count($activePlayersInfo) - 1)) {
                    //This player hasn't played against the remaining active players
                    //So they aren't allowed to get another bye this round
                    $bye_data['opponents'][] = $activePlayersInfo[$i]->player;
                    $list_opponents[] = $bye_data['player'];
                }

                $activePlayers[$i]['opponents'] = $list_opponents;
                $activePlayers[$i]['paired'] = false;
            }

            $pairings = new Pairings(array_values($activePlayers), $bye_data);
            $pairing = $pairings->pairing;
            if ($bye_data) {
                array_push($activePlayers, $bye_data);
            }
            for ($i = 0; $i < count($pairing); $i++) {
                if ($activePlayers[$i] != null && !$activePlayers[$i]['paired']) {
                    $player1 = new Standings($this->name, $activePlayers[$i]['player']);
                    if ($bye_data && $activePlayers[$pairing[$i]]['player'] == $bye_data['player']) {
                        $this->awardBye($player1);
                    } elseif ($activePlayers[$pairing[$i]] == null || $activePlayers[$pairing[$i]]['player'] == null) {
                        //In a very rare case where a player has played against all remaining players
                        //and the number of active players is even, hence no bye allowed initially
                        $this->awardBye($player1);
                    } else {
                        $player2 = new Standings($this->name, $activePlayers[$pairing[$i]]['player']);
                        $this->addPairing($player1, $player2, $this->current_round + 1, 'P');
                        $player2->matched = 1;
                        $player2->save();
                    }

                    $player1->matched = 1;
                    $player1->save();
                    $activePlayers[$i]['paired'] = true;
                    $activePlayers[$pairing[$i]]['paired'] = true;
                }
            }
        }
    }

    /** @param list<Entry> $entries */
    private function skipInvalidDecks(array $entries): void
    {
        // Invalid entries get a fake
        foreach ($entries as $entry) {
            if (is_null($entry->deck) || !$entry->deck->isValid()) {
                $playerStandings = new Standings($this->name, $entry->player->name);
                $playerStandings->matched = 1;
                $playerStandings->save();
                continue;
            }
        }
    }

    /** @param list<Entry> $entries */
    private function assignInitialByes(array $entries, int $current_round): void
    {
        foreach ($entries as $entry) {
            if ($entry->initial_byes < $current_round) {
                continue;
            }
            $player1 = new Standings($this->name, $entry->player->name);
            $this->awardBye($player1);
            $player1->matched = 1;
            $player1->save();
        }
    }

    private function getByeProxyName(): string
    {
        $byeNum = 0;
        while (true) {
            if (is_null(Player::findByName('BYE' . $byeNum))) {
                return 'BYE' . $byeNum;
            }
            $byeNum++;
        }
    }

    /** @return list<string> */
    private function getActiveOpponents(string $playername, int $subevent): array
    {
        $list_opponents = [];

        $standing = new Standings($this->name, $playername);
        $opponents = $standing->getOpponents($this->name, $subevent, 1);
        foreach ($opponents as $opponent) {
            if ($opponent->active === 1 && $opponent->player !== null) {
                $list_opponents[] = $opponent->player;
            }
        }

        return $list_opponents;
    }

    // I'm sure there is a proper algorithm to single or double elim with an arbitrary number of players
    // will look for one later, no need to reinvent the wheel. This works for now
    public function singleElimination(string $round): void
    {
        if ($round == 'final') {
            if ($this->current_round == $this->mainrounds) {
                if ($this->finalrounds == 2) {
                    $this->top4Seeding();
                } elseif ($this->finalrounds == 3) {
                    $this->top8Seeding();
                } elseif ($this->finalrounds == 1) {
                    $this->top2Seeding();
                }
            } else {
                $top_cut = (($this->finalrounds - ($this->current_round - $this->mainrounds)) * 2);
                $this->singleEliminationPairing($top_cut);
            }
        } elseif ($this->current_round == 0) {
            $this->singleEliminationByeCheck(2, 1);
        } else {
            $round = ($this->mainrounds - $this->current_round);
            $top_cut = pow(2, $round);
            $this->singleEliminationPairing($top_cut);
        }
    }

    public function singleEliminationPairing(int $top_cut): void
    {
        $players = $this->standing->getEventStandings($this->name, 2);
        $players = array_slice($players, 0, $top_cut);
        $counter = 0;
        while ($counter < (count($players) - 1)) {
            $playera = $players[$counter];
            if ($playera->player == null) {
                exit;
            }
            $counter++;
            if ($players[$counter] == null || $players[$counter]->player == null) {
                $this->awardBye($playera);
            } else {
                $this->addPairing($playera, $players[$counter], $this->current_round + 1, 'P');
            }
            $counter++;
        }
    }

    public function singleEliminationByeCheck(int $check, int $rounds): void
    {
        $seedcounter = 1;
        $players = $this->standing->getEventStandings($this->name, 2);
        if (count($players) > $check) {
            $rounds++;
            $this->singleEliminationByeCheck($check * 2, $rounds);
        } else {
            $byes_needed = $check - count($players);
            while ($byes_needed > 0) {
                $bye = rand(0, count($players) - 1);
                $this->awardBye($players[$bye]);
                Standings::writeSeed($this->name, $players[$bye]->player, $seedcounter);
                $seedcounter++;
                unset($players[$bye]);
                $players = array_values($players);
                $byes_needed--;
            }

            $counter = 0;
            while ($counter < (count($players) - 1)) {
                $playera = $players[$counter];
                $counter++;
                $playerb = $players[$counter];
                $this->addPairing($playera, $playerb, $this->current_round + 1, 'P');
                Standings::writeSeed($this->name, $playera->player, $seedcounter);
                $seedcounter++;
                Standings::writeSeed($this->name, $playerb->player, $seedcounter);
                $seedcounter++;
                $counter++;
            }
            if ($this->current_round >= $this->mainrounds) {
                $this->finalrounds = $rounds;
                $this->save();
            } else {
                $this->mainrounds = $rounds;
                $this->save();
            }
        }
    }

    // These functions need a serious DRYING out.  They are really obviously the same.
    // But we would need something in order to "order" the middle matches first.
    public function top2Seeding(): void
    {
        $players = $this->standing->getEventStandings($this->name, 3);
        $this->addPairing($players[0], $players[1], $this->current_round + 1, 'P');
        Standings::writeSeed($this->name, $players[0]->player, 1);
        Standings::writeSeed($this->name, $players[1]->player, 2);
    }

    public function top4Seeding(): void
    {
        $players = $this->standing->getEventStandings($this->name, 3);
        if (count($players) < 4) {
            $this->top2Seeding();
        } else {
            $this->addPairing($players[0], $players[3], $this->current_round + 1, 'P');
            $this->addPairing($players[1], $players[2], $this->current_round + 1, 'P');
            Standings::writeSeed($this->name, $players[0]->player, 1);
            Standings::writeSeed($this->name, $players[1]->player, 3);
            Standings::writeSeed($this->name, $players[2]->player, 2);
            Standings::writeSeed($this->name, $players[3]->player, 4);
        }
    }

    public function top8Seeding(): void
    {
        $players = $this->standing->getEventStandings($this->name, 3);
        if (count($players) < 8) {
            $this->top4Seeding();
        } else {
            $this->addPairing($players[0], $players[7], $this->current_round + 1, 'P');
            $this->addPairing($players[3], $players[4], $this->current_round + 1, 'P');
            $this->addPairing($players[1], $players[6], $this->current_round + 1, 'P');
            $this->addPairing($players[2], $players[5], $this->current_round + 1, 'P');
            Standings::writeSeed($this->name, $players[0]->player, 1);
            Standings::writeSeed($this->name, $players[7]->player, 2);
            Standings::writeSeed($this->name, $players[3]->player, 3);
            Standings::writeSeed($this->name, $players[4]->player, 4);
            Standings::writeSeed($this->name, $players[1]->player, 5);
            Standings::writeSeed($this->name, $players[6]->player, 6);
            Standings::writeSeed($this->name, $players[2]->player, 7);
            Standings::writeSeed($this->name, $players[5]->player, 8);
        }
    }

    public function awardBye(Standings $player): void
    {
        $this->addPairing($player, $player, $this->current_round + 1, 'BYE');
    }

    /** @return list<Event> */
    public static function getActiveEvents(bool $include_private = true): array
    {
        $db = Database::getConnection();
        if ($include_private) {
            $stmt = $db->prepare('SELECT name FROM events WHERE active = 1 ORDER BY start ASC');
        } else {
            $stmt = $db->prepare('SELECT name FROM events WHERE active = 1 AND `private` = 0 ORDER BY start ASC');
        }

        $stmt->execute();
        $stmt->bind_result($nextevent);
        $event_names = [];
        while ($stmt->fetch()) {
            $event_names[] = $nextevent;
        }
        $stmt->close();

        $events = [];
        foreach ($event_names as $eventname) {
            $events[] = new self($eventname);
        }

        return $events;
    }

    public function resolveRound(int $subevent, int $current_round): void
    {
        if ($this->current_round <= $this->mainrounds) {
            $round = $this->current_round;
        } else {
            $round = ($this->current_round - $this->mainrounds);
        }
        $matches_remaining = Matchup::unresolvedMatchesCheck($subevent, $round);

        if ($matches_remaining > 0) {
            // Nothing to do yet
            return;
        } else {
            if ($this->current_round > $this->mainrounds) {
                $structure = $this->finalstruct;
            } else {
                $structure = $this->mainstruct;
            }

            if ($this->current_round == $current_round) {
                $matches2 = $this->getRoundMatches($this->current_round);
                foreach ($matches2 as $match) {
                    $match->updateScores($structure);
                }
                if (strpos($structure, 'Swiss') === 0) {
                    $this->recalculateScores($structure);
                    Standings::updateStandings($this->name, $this->mainid, 1);
                } elseif ($structure == 'League') {
                    $this->recalculateScores('League');
                    Standings::updateStandings($this->name, $this->mainid, 1);
                }

                //We are at the end of the swiss round
                if (($this->current_round == $this->mainrounds) && (strpos($structure, 'Swiss') === 0)) {
                    $this->dropBlankEntries();
                }

                if ($structure != 'League' && $this->active === 1) {
                    $this->pairCurrentRound(true);
                }
            }
        }
    }

    public static function getEventBySubevent(int $subevent): self
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.name FROM events e, subevents s
        WHERE s.parent = e.name AND s.id = ? LIMIT 1');
        $stmt->bind_param('s', $subevent);
        $stmt->execute();
        $stmt->bind_result($event);
        $stmt->fetch();
        $stmt->close();
        $event = new self($event);

        return $event;
    }

    public function recalculateScores(string $structure): void
    {
        $this->resetScores();
        $matches2 = $this->getRoundMatches('ALL');
        foreach ($matches2 as $match) {
            $match->fixScores($structure);
        }
    }

    public function resetScores(): void
    {
        $standings = Standings::getEventStandings($this->name, 0);
        foreach ($standings as $standing) {
            $standing->score = 0;
            $standing->matches_played = 0;
            $standing->matches_won = 0;
            $standing->games_won = 0;
            $standing->byes = 0;
            $standing->games_played = 0;
            $standing->OP_Match = 0;
            $standing->PL_Game = 0;
            $standing->OP_Game = 0;
            $standing->draws = 0;

            $standing->save();
        }
    }

    public function resetEvent(): void
    {
        $db = Database::getConnection();

        $undropPlayer = $this->getPlayers();
        foreach ($undropPlayer as $player) {
            $this->undropPlayer($player);
        }

        $stmt = $db->prepare('DELETE FROM standings WHERE event = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->close();

        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM ratings WHERE event = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->close();

        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM matches WHERE subevent = ? OR subevent = ?');
        $stmt->bind_param('ss', $this->mainid, $this->finalid);
        $stmt->execute();
        $stmt->close();

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE entries SET medal = 'dot' WHERE event_id = ?");
        $stmt->bind_param('d', $this->id);
        $stmt->execute();
        $stmt->close();

        $this->current_round = 0;
        $this->active = 0;
        $this->save();
    }

    // This doesn't "repair" the round, it "re-pairs" the round by removing the pairings for the round.
    // It will always restart the top N if it is after the end rounds.
    public function repairRound(): void
    {
        if ($this->current_round <= $this->mainrounds) {
            $round = $this->current_round;
            $subevent = $this->mainid;
        } else {
            $round = $this->current_round - $this->mainrounds;
            $subevent = $this->finalid;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM matches WHERE subevent = ? AND round = ?');
        $stmt->bind_param('dd', $subevent, $round);
        $stmt->execute();
        $stmt->close();

        $this->current_round--;
        $this->save();
        $this->recalculateScores('Swiss');
        Standings::updateStandings($this->name, $this->mainid, 1);
        $this->pairCurrentRound(true);
    }

    public function assignMedals(): void
    {
        $structure = $this->current_round > $this->mainrounds ? $this->finalstruct : $this->mainstruct;

        if (in_array($structure, ['Swiss', 'Swiss (Blossom)', 'League', 'League Match'])) {
            $this->assignMedalsByStandings();
        } elseif ($structure === 'Single Elimination') {
            $this->assignTrophiesFromMatches();
        }
    }

    public function assignMedalsByStandings(): void
    {
        $players = $this->standing->getEventStandings($this->name, 0);
        $numberOfPlayers = count($players);

        $medalCount = $numberOfPlayers < 8 ? 2 : ($numberOfPlayers < 16 ? 4 : 8);

        $t8 = $t4 = [];
        if ($medalCount >= 8) {
            $t8 = array_values(array_filter(array_map(fn($i) => $players[$i]->player ?? null, [4, 5, 6, 7])));
        }
        if ($medalCount >= 4) {
            $t4 = array_values(array_filter(array_map(fn($i) => $players[$i]->player ?? null, [2, 3])));
        }
        $sec = $players[1]->player ?? null;
        $win = $players[0]->player;

        $this->setFinalists($win, $sec, $t4, $t8);
    }

    public function isFull(): bool
    {
        $entries = $this->getEntries();
        $players = count($entries);
        if ($this->prereg_cap == 0) {
            return false;
        }
        return $players >= $this->prereg_cap;
    }

    /** @return list<Matchup> */
    public function matchesOfType(string $type): array
    {
        $verification = '';
        if ($type == 'unfinished') {
            $verification = 'unverified';
        } elseif ($type == 'finished') {
            $verification = 'verified';
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT m.id FROM matches m, subevents s, events e
        WHERE m.subevent = s.id AND s.parent = e.name AND e.name = ? AND
        m.verification = ? AND m.round = ? AND s.timing = ? ORDER BY m.verification');
        $current_round = $this->current_round;
        $timing = 1;
        if ($current_round > $this->mainrounds) {
            $current_round -= $this->mainrounds;
            $timing = 2;
        }
        $stmt->bind_param('ssdd', $this->name, $verification, $current_round, $timing);
        $stmt->execute();
        $stmt->bind_result($matchid);

        $mids = [];
        while ($stmt->fetch()) {
            $mids[] = $matchid;
        }
        $stmt->close();

        $matches = [];
        foreach ($mids as $mid) {
            $matches[] = new Matchup($mid);
        }

        return $matches;
    }

    /** @return list<Matchup> */
    public function unfinishedMatches(): array
    {
        return $this->matchesOfType('unfinished');
    }

    public function updateDecksFormat(string $format): void
    {
        $sql = '
            UPDATE
                decks
            SET
                format = :format
            WHERE
                id IN (SELECT deck FROM entries WHERE event_id = :event_id AND deck IS NOT NULL)
        ';
        db()->execute($sql, ['format' => $format, 'event_id' => $this->id]);
    }

    public function startEvent(bool $precheck): void
    {
        $entries = $this->getRegisteredEntries($precheck);
        Standings::startEvent($entries, $this->name);
        $this->pairCurrentRound($precheck);
        $this->active = 1;
        $this->save();
    }

    public function structureSummary(): string
    {
        $ret = $this->toEnglish($this->mainstruct, $this->mainrounds, false);
        if ($this->finalrounds > 0) {
            $ret = $ret . ' followed by ' . $this->toEnglish($this->finalstruct, $this->finalrounds, true);
        }
        return $ret;
    }

    private function toEnglish(string $structure, int $rounds, bool $isfinals): string
    {
        if ($structure == 'Single Elimination' && $isfinals) {
            if ($rounds == 3) {
                return 'Top 8 cut';
            }
            if ($rounds == 2) {
                return 'Top 4 cut';
            }
            if ($rounds == 1) {
                return 'Top 2 cut';
            }
        } elseif ($structure == 'League') {
            $str = '';
            if ($rounds > 1) {
                $str = "{$rounds} runs of ";
            }
            $str .= ' 5 open matches';

            return $str;
        } elseif ($structure == 'League Match') {
            return "{$rounds} open matches";
        }
        if ($rounds == 1) {
            return "{$rounds} round of {$structure}";
        } else {
            return "{$rounds} rounds of {$structure}";
        }
    }

    public function isFinished(): bool
    {
        return $this->finalized && !$this->active;
    }

    public function isSwiss(): bool
    {
        return $this->mainstruct == 'Swiss';
    }

    public function isSingleElim(): bool
    {
        return $this->mainstruct == 'Single Elimination';
    }

    public function hasStarted(): bool
    {
        return $this->active == 1 || $this->finalized;
    }
}
