<?php

declare(strict_types=1);

namespace Gatherling\Models;

use Exception;
use Gatherling\Data\DB;
use Gatherling\Views\Components\ReportLink;

class Event
{
    public ?string $name;
    public ?int $id;

    public ?int $season;
    public ?int $number;
    public ?string $format;

    public ?string $start;
    public ?int $kvalue;
    public ?int $active;
    public ?int $finalized;
    public ?int $prereg_allowed;
    public ?string $threadurl;
    public ?string $reporturl;
    public ?string $metaurl;
    public ?int $private;
    public ?int $client;

    public ?int $player_editdecks;

    // Class associations
    public ?string $series; // belongs to Series
    public ?string $host; // has one Player - host
    public ?string $cohost; // has one Player - cohost

    // Subevents
    public string|int $mainrounds;
    public string $mainstruct;
    public ?int $mainid; // Has one main subevent
    public string|int $finalrounds;
    public string $finalstruct;
    public ?int $finalid; // Has one final subevent

    // Pairing/event related
    public ?int $current_round;
    public Standings $standing;
    public ?int $player_reportable;
    public ?int $player_reported_draws;
    public ?int $prereg_cap; // Cap on player initiated registration
    public ?int $late_entry_limit; // How many rounds we let people perform late entries

    public ?int $private_decks; // Toggle to disable deck privacy for active events. Allows the metagame page to display during an active event and lets deck lists be viewed if disabled.
    public ?int $private_finals; // As above, but for finals

    public ?int $hastrophy;
    private ?bool $new = null;

    public function __construct(int|string $name)
    {
        if ($name == '') {
            $this->id = 0;
            $this->name = '';
            $this->mainrounds = '';
            $this->mainstruct = '';
            $this->finalrounds = '';
            $this->finalstruct = '';
            $this->host = null;
            $this->cohost = null;
            $this->threadurl = null;
            $this->reporturl = null;
            $this->metaurl = null;
            $this->start = null;
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

        $db = Database::getConnection();

        if (!$this->new) {
            $sql = 'SELECT id, name, format, host, cohost, series, season, number,
                           start, kvalue, finalized, prereg_allowed, threadurl,
                           metaurl, reporturl, active, current_round, player_reportable, player_editdecks,
                           prereg_cap, private_decks, private_finals, player_reported_draws, late_entry_limit, `private`, client FROM events WHERE ';
            if (is_numeric($name)) {
                $sql .= 'id = ?';
                $pt = 'd';
            } else {
                $sql .= 'name = ?';
                $pt = 's';
            }
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                exit($db->error);
            }
            $stmt->bind_param($pt, $name);
            $stmt->execute();
            $stmt->bind_result(
                $this->id,
                $this->name,
                $this->format,
                $this->host,
                $this->cohost,
                $this->series,
                $this->season,
                $this->number,
                $this->start,
                $this->kvalue,
                $this->finalized,
                $this->prereg_allowed,
                $this->threadurl,
                $this->metaurl,
                $this->reporturl,
                $this->active,
                $this->current_round,
                $this->player_reportable,
                $this->player_editdecks,
                $this->prereg_cap,
                $this->private_decks,
                $this->private_finals,
                $this->player_reported_draws,
                $this->late_entry_limit,
                $this->private,
                $this->client
            );
            if ($stmt->fetch() == null) {
                throw new Exception('Event '.$name.' not found in DB');
            }
            $stmt->close();
        }

        $this->standing = new Standings($this->name, '0');

        // Main rounds
        $this->mainid = null;
        $this->mainrounds = '';
        $this->mainstruct = '';
        $stmt = $db->prepare('SELECT id, rounds, type FROM subevents
      WHERE parent = ? AND timing = 1');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($this->mainid, $this->mainrounds, $this->mainstruct);
        $stmt->fetch();
        $stmt->close();

        // Final rounds
        $this->finalid = null;
        $this->finalrounds = '';
        $this->finalstruct = '';
        $stmt = $db->prepare('SELECT id, rounds, type FROM subevents
      WHERE parent = ? AND timing = 2');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($this->finalid, $this->finalrounds, $this->finalstruct);
        $stmt->fetch();
        $stmt->close();

        // Trophy count
        $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM trophies WHERE event = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($this->hastrophy);
        $stmt->fetch();
        $stmt->close();

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
        string $mainrounds,
        string $mainstruct,
        string $finalrounds,
        string $finalstruct,
        string $client
    ): Event {
        $event = new self('');
        $event->start = "{$year}-{$month}-{$day} {$hour}:00";

        if (empty($season) && empty($number)) {
            $_series = new Series($series);
            $mostRecentEvent = $_series->mostRecentEvent();
            $season = $mostRecentEvent ? $mostRecentEvent->season : 0;
            $number = $mostRecentEvent ? $mostRecentEvent->number + 1 : 1;
        }

        if (strcmp($naming, 'auto') == 0) {
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

        if ($mainrounds == '') {
            $mainrounds = 3;
        }
        if ($mainstruct == '') {
            $mainstruct = 'Swiss';
        }
        $event->mainrounds = $mainrounds;
        $event->mainstruct = $mainstruct;
        if ($finalrounds == '') {
            $finalrounds = 0;
        }
        if ($finalstruct == '') {
            $finalstruct = 'Single Elimination';
        }
        $event->finalrounds = $finalrounds;
        $event->finalstruct = $finalstruct;
        $event->save();

        return $event;
    }

    public function save(): void
    {
        $db = Database::getConnection();

        if ($this->cohost == '') {
            $this->cohost = null;
        }
        if ($this->host == '') {
            $this->host = null;
        }
        if ($this->finalized) {
            $this->active = 0;
        }

        if ($this->new) {
            $stmt = $db->prepare('INSERT INTO events(name, start, format, host, cohost, kvalue,
                                               number, season, series, threadurl, reporturl,
                                               metaurl, prereg_allowed, finalized, player_reportable,
                                               prereg_cap, player_editdecks, private_decks, private_finals, player_reported_draws, late_entry_limit, `private`, client)
                            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param(
                'sssssdddssssdddddddddd',
                $this->name,
                $this->start,
                $this->format,
                $this->host,
                $this->cohost,
                $this->kvalue,
                $this->number,
                $this->season,
                $this->series,
                $this->threadurl,
                $this->reporturl,
                $this->metaurl,
                $this->prereg_allowed,
                $this->player_reportable,
                $this->prereg_cap,
                $this->player_editdecks,
                $this->private_decks,
                $this->private_finals,
                $this->player_reported_draws,
                $this->late_entry_limit,
                $this->private,
                $this->client
            );
            if (!$stmt->execute()) {
                throw new Exception($stmt->error, 1);
            }
            $stmt->close();

            $this->newSubevent((int) $this->mainrounds, 1, $this->mainstruct);
            $this->newSubevent((int) $this->finalrounds, 2, $this->finalstruct);
        } else {
            $stmt = $db->prepare('UPDATE events SET
      start = ?, format = ?, host = ?, cohost = ?, kvalue = ?,
      number = ?, season = ?, series = ?, threadurl = ?, reporturl = ?,
      metaurl = ?, finalized = ?, prereg_allowed = ?, active = ?,
      current_round = ?, player_reportable = ?, prereg_cap = ?,
      player_editdecks = ?, private_decks = ?, private_finals = ?, player_reported_draws = ?, late_entry_limit = ?, `private` = ?, client = ?
      WHERE name = ?');
            $stmt or exit($db->error);
            $stmt->bind_param(
                'ssssdddssssddddddddddddds',
                $this->start,
                $this->format,
                $this->host,
                $this->cohost,
                $this->kvalue,
                $this->number,
                $this->season,
                $this->series,
                $this->threadurl,
                $this->reporturl,
                $this->metaurl,
                $this->finalized,
                $this->prereg_allowed,
                $this->active,
                $this->current_round,
                $this->player_reportable,
                $this->prereg_cap,
                $this->player_editdecks,
                $this->private_decks,
                $this->private_finals,
                $this->player_reported_draws,
                $this->late_entry_limit,
                $this->private,
                $this->client,
                $this->name
            );

            if (!$stmt->execute()) {
                throw new Exception($stmt->error, 1);
            }
            $stmt->close();

            if ($this->mainid == null) {
                $this->newSubevent($this->mainrounds, 1, $this->mainstruct);
            } else {
                $main = new Subevent($this->mainid);
                $main->rounds = (int) $this->mainrounds;
                $main->type = $this->mainstruct;
                $main->save();
            }

            if ($this->finalid == null) {
                $this->newSubevent($this->finalrounds, 2, $this->finalstruct);
            } else {
                $final = new Subevent($this->finalid);
                $final->rounds = (int) $this->finalrounds;
                $final->type = $this->finalstruct;
                $final->save();
            }
        }
    }

    private function newSubevent(int $rounds, int $timing, string $type): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO subevents(parent, rounds, timing, type)
      VALUES(?, ?, ?, ?)');
        $stmt->bind_param('sdds', $this->name, $rounds, $timing, $type);
        $stmt->execute();
        $stmt->close();
    }

    public function getPlaceDeck(string $placing = '1st'): ?Deck
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT n.deck from entries n, events e
      WHERE n.event_id = e.id AND n.medal = ? AND e.name = ?');
        $stmt->bind_param('ss', $placing, $this->name);
        $stmt->execute();
        $stmt->bind_result($deckid);
        $result = $stmt->fetch();
        $stmt->close();
        if ($result == null) {
            $deck = null;
        } else {
            $deck = new Deck($deckid);
        }

        return $deck;
    }

    public function getPlacePlayer(string $placing = '1st'): ?string
    {
        $playername = Database::db_query_single('SELECT n.player from entries n, events e
                                             WHERE n.event_id = e.id
                                             AND n.medal = ?
                                             AND e.name = ?', 'ss', $placing, $this->name);

        return $playername;
    }

    public function decklistsVisible(): bool
    {
        return ($this->finalized && !$this->active) || $this->private_decks == 0 || ($this->current_round > $this->mainrounds && !$this->private_finals);
    }

    /** @return list<Deck> */
    public function getDecks(): array
    {
        $decks = [];
        $deckids = Database::list_result_single_param('SELECT deck FROM entries WHERE event_id = ? AND deck IS NOT NULL', 'd', $this->id);

        foreach ($deckids as $deckid) {
            $decks[] = new Deck($deckid);
        }

        return $decks;
    }

    /** @return array<array{medal: string, player: string, deck: Deck}> */
    public function getFinalists(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT medal, player, deck FROM entries
      WHERE event_id = ? AND medal != 'dot' ORDER BY medal, player");
        $stmt->bind_param('s', $this->id);
        $stmt->execute();
        $stmt->bind_result($medal, $player, $deck);

        $finalists = [];
        while ($stmt->fetch()) {
            $finalists[] = ['medal'       => $medal,
                'player'                  => $player,
                'deck'                    => $deck, ];
        }
        $stmt->close();

        return $finalists;
    }

    /**
     * @param ?array<int, ?string> $t4
     * @param ?array<int, ?string> $t8
     */
    public function setFinalists(string $win, ?string $sec, ?array $t4 = null, ?array $t8 = null): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE entries SET medal = 'dot' WHERE event_id = ?");
        $stmt->bind_param('d', $this->id);
        $stmt->execute();
        $stmt->close();
        $stmt = $db->prepare('UPDATE entries SET medal = ? WHERE event_id = ? AND player = ?');
        $medal = '1st';
        $stmt->bind_param('sds', $medal, $this->id, $win);
        $stmt->execute();
        $medal = '2nd';
        $stmt->bind_param('sds', $medal, $this->id, $sec);
        $stmt->execute();
        if (!is_null($t4)) {
            $medal = 't4';
            $stmt->bind_param('sds', $medal, $this->id, $t4[0]);
            $stmt->execute();
            $stmt->bind_param('sds', $medal, $this->id, $t4[1]);
            $stmt->execute();
        }
        if (!is_null($t8)) {
            $medal = 't8';
            $stmt->bind_param('sds', $medal, $this->id, $t8[0]);
            $stmt->execute();
            $stmt->bind_param('sds', $medal, $this->id, $t8[1]);
            $stmt->execute();
            $stmt->bind_param('sds', $medal, $this->id, $t8[2]);
            $stmt->execute();
            $stmt->bind_param('sds', $medal, $this->id, $t8[3]);
            $stmt->execute();
        }
        $stmt->close();
    }

    public function getTrophyImageLink(): string
    {
        return "<a href=\"deck.php?mode=view&event={$this->id}\" class=\"borderless\">\n"
           .self::trophyImageTag($this->name)."\n</a>\n";
    }

    public function isHost(string $name): bool
    {
        $ishost = !is_null($this->host) && strcasecmp($name, $this->host) == 0;
        $iscohost = !is_null($this->cohost) && strcasecmp($name, $this->cohost) == 0;

        return $ishost || $iscohost;
    }

    public function isFinalized(): bool
    {
        return $this->finalized != 0;
    }

    public function isOrganizer(string $name): bool
    {
        $isOrganizer = false;
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT player FROM series_organizers WHERE series = ? and player = ?');
        $stmt->bind_param('ss', $this->series, $name);
        $stmt->execute();
        $stmt->bind_result($aname);
        while ($stmt->fetch()) {
            $isOrganizer = true;
        }
        $stmt->close();

        return $isOrganizer;
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
        return Database::single_result_single_param('SELECT count(*) FROM entries WHERE event_id = ?', 'd', $this->id);
    }

    /** @return list<string> */
    public function getPlayers(): array
    {
        return Database::list_result_single_param('SELECT player FROM entries WHERE event_id = ? ORDER BY medal, player', 'd', $this->id);
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
        return Database::list_result_single_param(
            'SELECT player
                                                 FROM entries
                                                 WHERE event_id = ?
                                                 AND deck ORDER BY DATE(`registered_at`) ASC',
            'd',
            $this->id
        );
    }

    /** @return list<string> */
    public function getEntriesByMedal(): array
    {
        return Database::list_result_single_param(
            'SELECT player
                                             FROM entries
                                             WHERE event_id = ?
                                             AND deck ORDER BY medal, player ',
            'd',
            $this->id
        );
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
        $entry = new Entry($this->id, $playername);

        return $entry->removeEntry();
    }

    public function addPlayer(string $playername): bool
    {
        $playername = trim($playername);
        if (strcmp($playername, '') == 0) {
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
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO entries(event_id, player, registered_at) VALUES(?, ?, NOW())');
            $stmt->bind_param('ds', $this->id, $player->name);
            if (!$stmt->execute()) {
                print_r($stmt->error);

                return false;
            }
            $stmt->close();
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
        Database::db_query('UPDATE entries SET drop_round = ? WHERE event_id = ? AND player = ?', 'dds', $round, $this->id, $playername);
        Database::db_query('UPDATE standings SET active = 0 WHERE event = ? AND player = ?', 'ss', $this->name, $playername);
    }

    public function undropPlayer(string $playername): void
    {
        Database::db_query('UPDATE entries SET drop_round = 0 WHERE event_id = ? AND player = ?', 'ds', $this->id, $playername);
        Database::db_query('UPDATE standings SET active = 1 WHERE event = ? AND player = ?', 'ss', $this->name, $playername);
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
    public function getRoundMatches(string|int $roundnum): array
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

        $mids = DB::values($sql, 'int', $params);

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
        $count = Database::db_query_single('SELECT COUNT(m.id) FROM matches m, subevents s, events e
            WHERE m.subevent = s.id AND s.parent = e.name AND e.name = ? AND
            s.timing = ? AND m.round = ? AND (m.playera = ? OR m.playerb = ?)', 'sddss', $this->name, $subevnum, $roundnum, $player_name, $player_name);

        return $count;
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
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO matches(playera, playerb, round, subevent, result, verification) VALUES(?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssddss', $playera->player, $playerb->player, $round, $id, $result, $verification);
        $stmt->execute();
        $newmatch = (int) $stmt->insert_id;
        $stmt->close();

        return $newmatch;
    }

    public function addMatch(Standings $playera, Standings $playerb, string $round = '99', string $result = 'P', string $playera_wins = '0', string $playerb_wins = '0'): void
    {
        $draws = 0;
        $id = $this->mainid;

        if ($round > $this->mainrounds) {
            $id = $this->finalid;
            $round = (int) $round - (int) $this->mainrounds;
        }

        if ($round == 99) {
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
    public function assignTropiesFromMatches(): void
    {
        $t8 = [];
        $t4 = [];
        $sec = '';
        $win = '';
        if ($this->finalrounds > 0) {
            $quarter_finals = $this->finalrounds >= 3;
            if ($quarter_finals) {
                $quart_round = $this->mainrounds + $this->finalrounds - 2;
                $matches = $this->getRoundMatches($quart_round);
                foreach ($matches as $match) {
                    $loser = $match->getLoser();
                    if ($loser !== null) {
                        $t8[] = $loser;
                    }
                }
            }
            $semi_finals = $this->finalrounds >= 2;
            if ($semi_finals) {
                $semi_round = $this->mainrounds + $this->finalrounds - 1;
                $matches = $this->getRoundMatches($semi_round);
                foreach ($matches as $match) {
                    $loser = $match->getLoser();
                    if ($loser !== null) {
                        $t4[] = $loser;
                    }
                }
            }

            $finalmatches = $this->getRoundMatches($this->mainrounds + $this->finalrounds);
            $finalmatch = $finalmatches[0];
            $sec = $finalmatch->getLoser();
            $win = $finalmatch->getWinner();
        } else {
            $quarter_finals = $this->mainrounds >= 3;
            if ($quarter_finals) {
                $quart_round = $this->mainrounds - 2;
                $matches = $this->getRoundMatches($quart_round);
                foreach ($matches as $match) {
                    $loser = $match->getLoser();
                    if ($loser !== null) {
                        $t8[] = $loser;
                    }
                }
            }
            $semi_finals = $this->mainrounds >= 2;
            if ($semi_finals) {
                $semi_round = $this->mainrounds - 1;
                $matches = $this->getRoundMatches($semi_round);
                foreach ($matches as $match) {
                    $loser = $match->getLoser();
                    if ($loser !== null) {
                        $t4[] = $loser;
                    }
                }
            }

            $finalmatches = $this->getRoundMatches($this->mainrounds);
            $finalmatch = $finalmatches[0];
            $sec = $finalmatch->getLoser();
            $win = $finalmatch->getWinner();
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

    /** @return array{link: string, text: string} */
    public function makeLinkArgs(string $text): array
    {
        return [
            'link' => 'event.php?name='.rawurlencode($this->name),
            'text' => $text,
        ];
    }

    public static function count(): int
    {
        return Database::single_result('SELECT count(name) FROM events');
    }

    public static function largestEventNum(): int
    {
        return Database::single_result('SELECT max(number) FROM events where number != 128'); // 128 is "special"
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
    public function getSeasonPointAdjustment(string $player): ?array
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
        return null;
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

    public static function trophyImageTag(string $eventname): string
    {
        return "<img style=\"border-width: 0px; max-width: 260px\" src=\"{self::trophySrc($eventname)}\" />";
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
    public function pairCurrentRound(bool $skip_invalid = false): bool
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

                $lock_db = Database::get_lock((string) $subevent_id);
                if ($lock_db !== 1) {
                    return false;
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
                    case 'Round Robin':
                        //Do later
                        break;
                }

                Database::release_lock((string) $subevent_id);
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

        return true;
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

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT player, byes, score FROM standings WHERE event = ? AND active = 1 AND matched = 0 ORDER BY RAND()');
        $stmt or exit($db->error);
        $stmt->bind_param('s', $this->name);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error, 1);
        }
        $resultSet = $stmt->get_result();
        $active_players = $resultSet->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (count($active_players) > 0) {
            $bye_data = [];
            if (count($active_players) % 2 != 0) {
                $bye_data = ['player' => $this->getByeProxyName(), 'score' =>  0, 'opponents' => [], 'paired' => false];
            }

            for ($i = 0; $i < count($active_players); $i++) {
                $list_opponents = $this->getActiveOpponents($active_players[$i]['player'], $subevent_id);

                $standing = new Standings($this->name, $active_players[$i]['player']);

                if (count($bye_data) > 0 && $standing->byes > 0 && count($list_opponents) < (count($active_players) - 1)) {
                    //This player hasn't played against the remaining active players
                    //So they aren't allowed to get another bye this round
                    $bye_data['opponents'][] = $active_players[$i]['player'];
                    $list_opponents[] = $bye_data['player'];
                }

                $active_players[$i]['opponents'] = $list_opponents;
                $active_players[$i]['paired'] = false;
            }

            $pairings = new Pairings($active_players, $bye_data);
            $pairing = $pairings->pairing;
            if (count($bye_data) > 0) {
                array_push($active_players, $bye_data);
            }
            for ($i = 0; $i < count($pairing); $i++) {
                if ($active_players[$i] != null && !$active_players[$i]['paired']) {
                    $player1 = new Standings($this->name, $active_players[$i]['player']);
                    if (count($bye_data) > 0 && $active_players[$pairing[$i]]['player'] == $bye_data['player']) {
                        $this->awardBye($player1);
                    } elseif ($active_players[$pairing[$i]] == null || $active_players[$pairing[$i]]['player'] == null) {
                        //In a very rare case where a player has played against all remaining players
                        //and the number of active players is even, hence no bye allowed initially
                        $this->awardBye($player1);
                    } else {
                        $player2 = new Standings($this->name, $active_players[$pairing[$i]]['player']);
                        $this->addPairing($player1, $player2, $this->current_round + 1, 'P');
                        $player2->matched = 1;
                        $player2->save();
                    }

                    $player1->matched = 1;
                    $player1->save();
                    $active_players[$i]['paired'] = true;
                    $active_players[$pairing[$i]]['paired'] = true;
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
        if ($opponents != null) {
            foreach ($opponents as $opponent) {
                if ($opponent->active === 1) {
                    $list_opponents[] = $opponent->player;
                }
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
            //echo "There are still {$matches_remaining} unresolved matches";
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
                    //echo "about to update scores";
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
            //echo "about to update scores";
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
        $removed = $stmt->affected_rows > 0;
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
        $removed = $stmt->affected_rows > 0;
        $stmt->close();

        $this->current_round--;
        $this->save();
        $this->recalculateScores('Swiss');
        Standings::updateStandings($this->name, $this->mainid, 1);
        $this->pairCurrentRound(true);
    }

    public function assignMedals(): void
    {
        if ($this->current_round > $this->mainrounds) {
            $structure = $this->finalstruct;
            $subevent_id = $this->finalid;
            $round = 'final';
        } else {
            $structure = $this->mainstruct;
            $subevent_id = $this->mainid;
            $round = 'main';
        }

        switch ($structure) {
            case 'Swiss':
            case 'Swiss (Blossom)':
                $this->AssignMedalsbyStandings();
                break;
            case 'Single Elimination':
                $this->assignTropiesFromMatches();
                break;
            case 'League':
            case 'League Match':
                $this->AssignMedalsbyStandings();
                break;
            case 'Round Robin':
                //Do later
                break;
        }
    }

    public function assignMedalsByStandings(): void
    {
        $players = $this->standing->getEventStandings($this->name, 0);
        $numberOfPlayers = count($players);

        if ($numberOfPlayers < 8) {
            $medalCount = 2; // only give 2 medals if there are less than 8 players
        } elseif ($numberOfPlayers < 16) {
            $medalCount = 4; // only give 4 medals if there are less than 16 players
        } else {
            $medalCount = 8;
        }

        $t8 = [];
        $t4 = [];
        $sec = null;
        $win = null;

        switch ($medalCount) {
            case 8:
                $t8[3] = $players[7]->player;
                // Intentional fallthrough
            case 7:
                $t8[2] = $players[6]->player;
                // Intentional fallthrough
            case 6:
                $t8[1] = $players[5]->player;
                // Intentional fallthrough
            case 5:
                $t8[0] = $players[4]->player;
                // Intentional fallthrough
            case 4:
                $t4[1] = $players[3]->player;
                // Intentional fallthrough
            case 3:
                $t4[0] = $players[2]->player;
                // Intentional fallthrough
            case 2:
                $sec = $players[1]->player;
                // Intentional fallthrough
            case 1:
                $win = $players[0]->player;
        }
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
        $deckIDs = Database::list_result_single_param('SELECT deck FROM entries WHERE event_id = ? AND deck IS NOT NULL', 'd', $this->id);

        if (count($deckIDs)) {
            $db = Database::getConnection();
            foreach ($deckIDs as $deckID) {
                $stmt = $db->prepare('UPDATE decks SET format = ? WHERE id = ?');
                $stmt->bind_param('ss', $format, $deckID);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    public function startEvent(bool $precheck): void
    {
        $entries = $this->getRegisteredEntries($precheck);
        Standings::startEvent($entries, $this->name);
        // $this->dropInvalidEntries();
        $this->pairCurrentRound($precheck);
        $this->active = 1;
        $this->save();
    }

    public function structureSummary(): string
    {

        $ret = $this->toEnglish($this->mainstruct, $this->mainrounds, false);
        if ($this->finalrounds > 0) {
            $ret = $ret.' followed by ' . $this->toEnglish($this->finalstruct, $this->finalrounds, true);
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
