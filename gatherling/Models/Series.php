<?php

declare(strict_types=1);

namespace Gatherling\Models;

use PDO;
use Exception;
use InvalidArgumentException;

class Series
{
    public string $name;
    public ?int $active;
    public ?string $start_day;
    public ?string $start_time;
    /** @var list<string> */
    public array $organizers; // has many :organizers, :through => series_organizers, :class_name => Player
    /** @var list<string> */
    public array $bannedplayers;
    public ?string $mtgo_room;

    public ?string $this_season_format;
    public ?string $this_season_master_link;
    public int $this_season_season;

    public ?int $prereg_default;

    public ?string $discord_guild_id;
    public ?string $discord_channel_id;
    public ?string $discord_channel_name;
    public ?string $discord_guild_name;
    public ?string $discord_guild_invite;
    public ?int $discord_require_membership;

    public bool $new;

    public function __construct(?string $name)
    {
        // Defend against "impossible" nulls until such time as we are able to make object properties not-nullable.
        if ($name === null) {
            throw new InvalidArgumentException('Series name cannot be null');
        }
        if ($name == '') {
            $this->name = '';
            $this->start_day = '';
            $this->start_time = '';
            $this->organizers = [];
            $this->bannedplayers = [];
            $this->new = true;
            $this->prereg_default = 1;
            $this->mtgo_room = '';
            $this->discord_require_membership = 0;

            return;
        }

        $db = Database::getConnection();
        $sql = 'SELECT isactive, day, normalstart, prereg_default, mtgo_room, discord_guild_id, discord_channel_id, discord_channel_name, discord_guild_name, discord_guild_invite, discord_require_membership FROM series WHERE name = ?';
        $stmt = $db->prepare($sql);
        $stmt or exit($db->error);
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->bind_result(
            $this->active,
            $this->start_day,
            $this->start_time,
            $this->prereg_default,
            $this->mtgo_room,
            $this->discord_guild_id,
            $this->discord_channel_id,
            $this->discord_channel_name,
            $this->discord_guild_name,
            $this->discord_guild_invite,
            $this->discord_require_membership
        );
        if ($stmt->fetch() == null) {
            throw new Exception('Series ' . $name . ' not found in DB');
        }

        $stmt->close();

        $this->name = $name;

        // Organizers
        $stmt = $db->prepare('SELECT player FROM series_organizers WHERE series = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($one_player);
        $this->organizers = [];
        while ($stmt->fetch()) {
            $this->organizers[] = $one_player;
        }
        $stmt->close();

        // banned players
        $stmt = $db->prepare('SELECT player FROM playerbans WHERE series = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($one_player);
        $this->bannedplayers = [];
        while ($stmt->fetch()) {
            $this->bannedplayers[] = $one_player;
        }
        $stmt->close();

        // Most recent season
        $mostRecentEvent = $this->mostRecentEvent();
        $this_season = $mostRecentEvent->season ?? 0;
        $stmt = $db->prepare('SELECT format, master_link FROM series_seasons WHERE series = ? AND season <= ?
                              ORDER BY season DESC
                              LIMIT 1');
        $stmt->bind_param('sd', $this->name, $this_season);
        $stmt->execute();
        $stmt->bind_result($this->this_season_format, $this->this_season_master_link);
        $stmt->fetch();
        $stmt->close();
        $this->this_season_season = $this_season;

        $this->new = false;
    }

    public function save(): void
    {
        $db = Database::getConnection();
        if (strncmp($this->mtgo_room, '#', 1) == 0) {
            $this->mtgo_room = substr($this->mtgo_room, 1);
        }
        if ($this->new) {
            $stmt = $db->prepare('INSERT INTO series(name, day, normalstart, isactive, prereg_default, mtgo_room) values(?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssdds', $this->name, $this->start_day, $this->start_time, $this->active, $this->prereg_default, $this->mtgo_room);
            $stmt->execute() or exit($stmt->error);
            $stmt->close();
        } else {
            $stmt = $db->prepare('UPDATE series
                            SET day = ?, normalstart = ?, isactive = ?, prereg_default = ?, mtgo_room = ?
                            WHERE name = ?');
            $stmt or exit($db->error);
            $stmt->bind_param('ssddss', $this->start_day, $this->start_time, $this->active, $this->prereg_default, $this->mtgo_room, $this->name);
            $stmt->execute() or exit($stmt->error);
            $stmt->close();
        }
    }

    /**
     * @param string $name
     */
    public function isOrganizer(string $name): bool
    {
        return in_array(strtolower($name), array_map('strtolower', $this->organizers));
    }

    /**
     * @param string $name
     */
    public function isPlayerBanned(string $name): bool
    {
        return in_array(strtolower($name), array_map('strtolower', $this->bannedplayers));
    }

    /**
     * @param string $name
     */
    public function addOrganizer(string $name): void
    {
        if (empty($name)) {
            return;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO series_organizers(series, player) VALUES(?, ?)');
        $stmt->bind_param('ss', $this->name, $name);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @param string $name
     */
    public function addBannedPlayer(string $name, string $reason): void
    {
        if (empty($name)) {
            return;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO playerbans(series, player, date, reason) VALUES(?, ?, CURRENT_DATE(), ?)');
        $stmt->bind_param('sss', $this->name, $name, $reason);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @param string $name
     */
    public function removeOrganizer(string $name): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM series_organizers WHERE series = ? AND player = ?');
        $stmt->bind_param('ss', $this->name, $name);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @param string $name
     */
    public function removeBannedPlayer(string $name): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM playerbans WHERE series = ? AND player = ?');
        $stmt->bind_param('ss', $this->name, $name);
        $stmt->execute();
        $stmt->close();
    }

    public function getBannedPlayerDate(string $name): ?string
    {
        return Database::singleResultSingleParam('SELECT date FROM playerbans WHERE player = ?', 's', $name);
    }

    public function getBannedPlayerReason(string $name): ?string
    {
        return Database::singleResultSingleParam('SELECT reason FROM playerbans WHERE player = ?', 's', $name);
    }

    public function authCheck(string $playername): bool
    {
        $player = new Player($playername);

        if (
            $player->isSuper() ||
            $this->isOrganizer($player->name)
        ) {
            return true;
        }

        return false;
    }

    /** @return list<string> */
    public function getEvents(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM events WHERE series = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($eventname);

        $events = [];
        while ($stmt->fetch()) {
            $events[] = $eventname;
        }
        $stmt->close();

        return $events;
    }

    /** @return list<Event> */
    public function getRecentEvents(int $number = 10): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM events WHERE series = ? ORDER BY start DESC LIMIT ?');
        $stmt->bind_param('sd', $this->name, $number);
        $stmt->execute();
        $stmt->bind_result($eventname);

        $eventnames = [];
        while ($stmt->fetch()) {
            $eventnames[] = $eventname;
        }
        $stmt->close();

        $events = [];
        foreach ($eventnames as $name) {
            $events[] = new Event($name);
        }

        return $events;
    }

    public static function exists(string $name): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM series WHERE name = ?');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->store_result();
        $series_exists = $stmt->num_rows > 0;
        $stmt->close();

        return $series_exists;
    }

    /** @return list<string> */
    public static function allNames(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT series.name
                          FROM series
                          LEFT JOIN events
                          ON events.series = series.name
                          GROUP BY series.name
                          ORDER BY series.name');
        $stmt->execute();
        $stmt->bind_result($onename);
        $names = [];
        while ($stmt->fetch()) {
            $names[] = $onename;
        }
        $stmt->close();

        return $names;
    }

    /** @return list<string> */
    public static function activeNames(): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT series.name
                          FROM series
                          LEFT JOIN events
                          ON events.series = series.name
                          WHERE series.isactive = 1
                          GROUP BY series.name
                          ORDER BY count(events.name)
                          DESC, name');
        $stmt->execute();
        $stmt->bind_result($onename);
        $names = [];
        while ($stmt->fetch()) {
            $names[] = $onename;
        }
        $stmt->close();

        return $names;
    }

    // Returns a HTML image tag which displays the logo for this series.
    public static function imageTag(string $seriesname): string
    {
        return '<img src="'  . self::logoSrc($seriesname) . '" />';
    }

    public static function logoSrc(string $seriesName): string
    {
        return 'displaySeries.php?series=' . rawurlencode($seriesName);
    }

    public function mostRecentEvent(): ?Event
    {
        $result = Database::dbQuerySingle('SELECT events.name
                                         FROM events
                                         JOIN series
                                         ON series.name = events.series
                                         WHERE series.name = ?
                                         AND events.start < NOW()
                                         ORDER BY events.start
                                         DESC LIMIT 1', 's', $this->name);
        if ($result) {
            return new Event($result);
        }
        return null;
    }

    public function nextEvent(): ?Event
    {
        $result = Database::dbQuerySingle('SELECT events.name
                                         FROM events
                                         JOIN series
                                         ON series.name = events.series
                                         WHERE series.name = ?
                                         AND events.start > NOW()
                                         ORDER BY events.start
                                         LIMIT 1', 's', $this->name);
        if ($result) {
            return new Event($result);
        }
        return null;
    }

    public function setLogo(string $content_filename, string $type, int $size): void
    {
        $db = Database::getPDOConnection();
        $f = fopen($content_filename, 'rb');
        $stmt = $db->prepare('UPDATE series SET imgsize = ?, imgtype = ?, logo = ? WHERE name = ?');
        $stmt->bindParam(1, $size, PDO::PARAM_INT);
        $stmt->bindParam(2, $type, PDO::PARAM_STR);
        $stmt->bindParam(3, $f, PDO::PARAM_LOB);
        $stmt->bindParam(4, $this->name, PDO::PARAM_STR);
        $stmt->execute() or print_r($stmt->errorInfo());
        fclose($f);
    }

    public function currentSeason(): int
    {
        $seasonnum = 0;
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT season FROM events WHERE series = ? ORDER BY start DESC LIMIT 1');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($seasonnum);
        $stmt->fetch();
        $stmt->close();

        return $seasonnum;
    }

    // TODO: THESE functions are UGLY.
    /** @return array<string, int|string> */
    public function getSeasonRules(int $season_number): array
    {
        $season_rules = ['first_pts'       => 0,
            'second_pts'                   => 0,
            'semi_pts'                     => 0,
            'quarter_pts'                  => 0,
            'participation_pts'            => 0,
            'rounds_pts'                   => 0,
            'decklist_pts'                 => 0,
            'win_pts'                      => 0,
            'loss_pts'                     => 0,
            'bye_pts'                      => 0,
            'must_decklist'                => 0,
            'cutoff_ord'                   => 0,
            'master_link'                  => '',
            'format'                       => '', ];

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT series, first_pts, second_pts, semi_pts, quarter_pts, participation_pts,
                                 rounds_pts, decklist_pts, win_pts, loss_pts, bye_pts, must_decklist, cutoff_ord,
                                 master_link, format FROM series_seasons
                          WHERE series = ?
                          AND season <= ?
                          ORDER BY season DESC
                          LIMIT 1');
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result(
            $seriesname,
            $season_rules['first_pts'],
            $season_rules['second_pts'],
            $season_rules['semi_pts'],
            $season_rules['quarter_pts'],
            $season_rules['participation_pts'],
            $season_rules['rounds_pts'],
            $season_rules['decklist_pts'],
            $season_rules['win_pts'],
            $season_rules['loss_pts'],
            $season_rules['bye_pts'],
            $season_rules['must_decklist'],
            $season_rules['cutoff_ord'],
            $season_rules['master_link'],
            $season_rules['format']
        );
        $stmt->fetch();
        $stmt->close();

        return $season_rules;
    }

    /**
     * @param array<string, int|string> $new_rules
     * @return array<string, int|string>
     */
    public function setSeasonRules(int $season_number, array $new_rules): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO series_seasons(series, season, first_pts, second_pts, semi_pts, quarter_pts,
                                                     participation_pts, rounds_pts, decklist_pts, win_pts, loss_pts,
                                                     bye_pts, must_decklist, cutoff_ord, master_link, format)
                          VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE first_pts=?, second_pts=?, semi_pts=?, quarter_pts=?,
                                                  participation_pts=?, rounds_pts=?, decklist_pts=?, win_pts=?,
                                                  loss_pts=?, bye_pts=?, must_decklist=?, cutoff_ord=?, master_link=?,
                                                  format=?');
        if (!$stmt) {
            echo $db->error;
        }
        $stmt->bind_param(
            'sdddddddddddddssddddddddddddss',
            $this->name,
            $season_number,
            $new_rules['first_pts'],
            $new_rules['second_pts'],
            $new_rules['semi_pts'],
            $new_rules['quarter_pts'],
            $new_rules['participation_pts'],
            $new_rules['rounds_pts'],
            $new_rules['decklist_pts'],
            $new_rules['win_pts'],
            $new_rules['loss_pts'],
            $new_rules['bye_pts'],
            $new_rules['must_decklist'],
            $new_rules['cutoff_ord'],
            $new_rules['master_link'],
            $new_rules['format'],
            $new_rules['first_pts'],
            $new_rules['second_pts'],
            $new_rules['semi_pts'],
            $new_rules['quarter_pts'],
            $new_rules['participation_pts'],
            $new_rules['rounds_pts'],
            $new_rules['decklist_pts'],
            $new_rules['win_pts'],
            $new_rules['loss_pts'],
            $new_rules['bye_pts'],
            $new_rules['must_decklist'],
            $new_rules['cutoff_ord'],
            $new_rules['master_link'],
            $new_rules['format']
        );
        $stmt->execute();
        $stmt->close();

        return $new_rules;
    }

    // SCORE HELPER FUNCTIONS:
    //
    // Each of these functions will return a array in the form
    //  array('playername' =>
    //             array( 'eventname' => count, 'eventname2' => count, ... ),
    //        'playername2' =>
    //             array( 'eventname' => count, 'eventname2' => count, .. ),
    //        ..).
    /** @return array<string, array<string, int>> */
    private function getPlacePlayers(int $season_number, string $place): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT entries.player, events.name
                          FROM events
                          JOIN entries
                          ON events.id = entries.event_id
                          WHERE events.series = ?
                          AND events.season = ?
                          AND entries.medal = ?
                          AND events.number != 128');
        $stmt or exit($db->error);
        $stmt->bind_param('sds', $this->name, $season_number, $place);
        $stmt->execute();
        $stmt->bind_result($playername, $eventname);
        $result = [];
        while ($stmt->fetch()) {
            if (!isset($result[$playername])) {
                $result[$playername] = [];
            }
            $result[$playername][$eventname] = 1;
        }

        return $result;
    }

    /** @return array<string, array<string, int>> */
    private function getParticipations(int $season_number): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT entries.player, events.name
                          FROM events
                          JOIN entries
                          ON events.id = entries.event_id
                          WHERE events.series = ?
                          AND events.season = ?
                          AND events.number != 128');
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($playername, $eventname);
        $result = [];
        while ($stmt->fetch()) {
            if (!isset($result[$playername])) {
                $result[$playername] = [];
            }
            $result[$playername][$eventname] = 1;
        }

        return $result;
    }

    /** @return array<string, array<string, int>> */
    private function getRoundsPlayed(int $season_number): array
    {
        $db = Database::getConnection();
        $db->query("set session sql_mode='';"); // Disable ONLY_FULL_GROUP_BY

        // This is a bit complicated we have to find the number of the last round in the
        // main event which the player played in, in each event in the season, and sum those together.
        //
        // For this purpose, we build the array:
        // array('player' => array('event' => last_round))
        $player_event_array = [];

        // First, if they were playera:
        $stmt = $db->prepare('SELECT events.name, matches.playera, max(matches.round)
                          FROM events
                          JOIN subevents
                          JOIN matches
                          ON events.name = subevents.parent
                          AND subevents.id = matches.subevent
                          WHERE events.series = ?
                          AND events.season = ?
                          AND subevents.timing = 1
                          AND events.number != 128 GROUP BY events.name, matches.playera ORDER BY events.name, matches.round');
        $stmt or exit($db->error);
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($event_name, $playername, $maxround);

        while ($stmt->fetch()) {
            if (!isset($player_event_array[$playername])) {
                $player_event_array[$playername] = [];
            }
            if (!isset($player_event_array[$playername][$event_name])) {
                $player_event_array[$playername][$event_name] = $maxround;
            } else {
                $player_event_array[$playername][$event_name] = max($maxround, $player_event_array[$playername][$event_name]);
            }
        }
        $stmt->close();
        // Then, if they were playerb:
        $stmt = $db->prepare('SELECT events.name, matches.playerb, max(matches.round)
                          FROM events
                          JOIN subevents
                          JOIN matches
                          ON events.name = subevents.parent
                          AND subevents.id = matches.subevent
                          WHERE events.series = ?
                          AND events.season = ?
                          AND subevents.timing = 1
                          AND events.number != 128
                          GROUP BY events.name, matches.playerb
                          ORDER BY events.name, matches.round');
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($event_name, $playername, $maxround);

        while ($stmt->fetch()) {
            if (!isset($player_event_array[$playername])) {
                $player_event_array[$playername] = [];
            }
            if (!isset($player_event_array[$playername][$event_name])) {
                $player_event_array[$playername][$event_name] = $maxround;
            } else {
                $player_event_array[$playername][$event_name] = max($maxround, $player_event_array[$playername][$event_name]);
            }
        }
        $stmt->close();

        return $player_event_array;
    }

    /** @return array<string, array<string, int>> */
    private function getDecklistPosteds(int $season_number): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT entries.player, events.name, count(entries.deck) c
                          FROM events
                          JOIN entries
                          ON entries.event_id = events.id
                          WHERE entries.deck IS NOT NULL
                          AND events.number != 128
                          AND events.series = ?
                          AND events.season = ?
                          GROUP BY entries.player, events.name');
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($playername, $eventname, $deckcount);

        $result = [];
        while ($stmt->fetch()) {
            if (!isset($result[$playername])) {
                $result[$playername] = [];
            }
            $result[$playername][$eventname] = $deckcount;
        }

        return $result;
    }

    /** @return array<string, array<string, int>> */
    private function getRoundsWon(int $season_number): array
    {
        $db = Database::getConnection();

        $result = [];
        // They could..
        // Win as playera
        $stmt = $db->prepare("SELECT matches.playera, events.name,  count(matches.round)
                          FROM events
                          JOIN subevents
                          JOIN matches
                          ON events.name = subevents.parent
                          AND subevents.id = matches.subevent
                          WHERE subevents.timing = 1
                          AND events.number != 128
                          AND matches.result = 'A'
                          AND events.series = ?
                          AND events.season = ?
                          GROUP BY matches.playera, events.name");
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($playername, $eventname, $matcheswon);
        while ($stmt->fetch()) {
            if (!isset($result[$playername])) {
                $result[$playername] = [];
            }
            $result[$playername][$eventname] = $matcheswon;
        }
        $stmt->close();

        // Or win as playerb
        $stmt = $db->prepare("SELECT matches.playerb, events.name, count(matches.round)
                          FROM events
                          JOIN subevents
                          JOIN matches
                          ON events.name = subevents.parent
                          AND subevents.id = matches.subevent
                          WHERE subevents.timing = 1
                          AND events.number != 128
                          AND matches.result = 'B'
                          AND events.series = ?
                          AND events.season = ?
                          GROUP BY matches.playerb, events.name");
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($playername, $eventname, $matcheswon);
        while ($stmt->fetch()) {
            if (!isset($result[$playername])) {
                $result[$playername] = [];
            }
            if (isset($result[$playername][$eventname])) {
                $result[$playername][$eventname] += $matcheswon;
            } else {
                $result[$playername][$eventname] = $matcheswon;
            }
        }
        $stmt->close();

        return $result;
    }

    /** @return array<string, array<string, int>> */
    private function getRoundsLost(int $season_number): array
    {
        $db = Database::getConnection();

        $result = [];
        // They could..
        // Lose as playera
        $stmt = $db->prepare("SELECT matches.playera, events.name, count(matches.round)
                          FROM events
                          JOIN subevents
                          JOIN matches
                          ON events.name = subevents.parent
                          AND subevents.id = matches.subevent
                          WHERE subevents.timing = 1
                          AND events.number != 128
                          AND matches.result = 'B'
                          AND events.series = ?
                          AND events.season = ?
                          GROUP BY matches.playera, events.name");
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($playername, $eventname, $matches);
        while ($stmt->fetch()) {
            if (!isset($result[$playername])) {
                $result[$playername] = [];
            }
            $result[$playername][$eventname] = $matches;
        }
        $stmt->close();

        // Or lose as playerb
        $stmt = $db->prepare("SELECT matches.playerb, events.name, count(matches.round)
                          FROM events
                          JOIN subevents
                          JOIN matches
                          ON events.name = subevents.parent
                          AND subevents.id = matches.subevent
                          WHERE subevents.timing = 1
                          AND events.number != 128
                          AND matches.result = 'A'
                          AND events.series = ?
                          AND events.season = ?
                          GROUP BY matches.playerb, events.name");
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($playername, $eventname, $matches);
        while ($stmt->fetch()) {
            if (!isset($result[$playername])) {
                $result[$playername] = [];
            }
            if (isset($result[$playername][$eventname])) {
                $result[$playername][$eventname] += $matches;
            } else {
                $result[$playername][$eventname] = $matches;
            }
        }
        $stmt->close();

        return $result;
    }

    // The BYE rounds that we can DETECT are -
    // $rounds_bye = $rounds_played - $rounds_won - $rounds_lost
    /**
     * @param array<string, array<string, int>> $rounds_played
     * @param array<string, array<string, int>> $rounds_won
     * @param array<string, array<string, int>> $rounds_lost
     * @return array<string, array<string, int>>
     */
    private function getRoundsBye(array $rounds_played, array $rounds_won, array $rounds_lost): array
    {
        $result = $rounds_played;
        foreach ($rounds_won as $playername => $arrayrounds) {
            foreach ($arrayrounds as $event => $rounds) {
                $result[$playername][$event] = $result[$playername][$event] - $rounds;
            }
        }
        foreach ($rounds_lost as $playername => $arrayrounds) {
            foreach ($arrayrounds as $event => $rounds) {
                $result[$playername][$event] = $result[$playername][$event] - $rounds;
            }
        }

        return $result;
    }

    /**
     * @param array<string, array<string, mixed>> $results
     * @param array<string, array<string, int>> $thispoints
     * @param array<string, array<string, int>> $decklists
     */
    private function multiplyAndAddPoints(array &$results, array $thispoints, int $multiplier, array $decklists, bool $reqdeck): void
    {
        if ($multiplier == 0) {
            return;
        }
        foreach ($thispoints as $playername => $arraycounts) {
            foreach ($arraycounts as $eventname => $amt) {
                if (!isset($results[$playername])) {
                    $results[$playername] = [];
                }
                if ($reqdeck and !isset($decklists[$playername][$eventname])) {
                    $results[$playername][$eventname] = ['why' => 'No deck posted', 'points' => '**'];
                    continue;
                }
                if (!isset($results[$playername][$eventname])) {
                    $results[$playername][$eventname] = $amt * $multiplier;
                } else {
                    $results[$playername][$eventname] = $results[$playername][$eventname] + ($amt * $multiplier);
                }
            }
        }
    }

    /**
     * @return array<string, array<string, int|array<string, string|int>>>
     */
    public function seasonPointsTable(int $season_number): array
    {
        $rules = $this->getSeasonRules($season_number);

        $total_pointarray = [];
        $firsts = $this->getPlacePlayers($season_number, '1st');
        $seconds = $this->getPlacePlayers($season_number, '2nd');
        $semis = $this->getPlacePlayers($season_number, 't4');
        $quarters = $this->getPlacePlayers($season_number, 't8');
        $decklists_posted = $this->getDecklistPosteds($season_number);
        $rounds_played = $this->getRoundsPlayed($season_number);
        $rounds_won = $this->getRoundsWon($season_number);
        $rounds_lost = $this->getRoundsLost($season_number);
        $rounds_bye = $this->getRoundsBye($rounds_played, $rounds_won, $rounds_lost);
        $participations = $this->getParticipations($season_number);

        $reqdeck = $rules['must_decklist'] == 1;

        $this->multiplyAndAddPoints($total_pointarray, $firsts, $rules['first_pts'], $decklists_posted, $reqdeck);
        $this->multiplyAndAddPoints($total_pointarray, $seconds, $rules['second_pts'], $decklists_posted, $reqdeck);
        $this->multiplyAndAddPoints($total_pointarray, $semis, $rules['semi_pts'], $decklists_posted, $reqdeck);
        $this->multiplyAndAddPoints($total_pointarray, $quarters, $rules['quarter_pts'], $decklists_posted, $reqdeck);
        $this->multiplyAndAddPoints($total_pointarray, $decklists_posted, $rules['decklist_pts'], $decklists_posted, $reqdeck);
        $this->multiplyAndAddPoints($total_pointarray, $rounds_played, $rules['rounds_pts'], $decklists_posted, $reqdeck);
        $this->multiplyAndAddPoints($total_pointarray, $rounds_won, $rules['win_pts'], $decklists_posted, $reqdeck);
        $this->multiplyAndAddPoints($total_pointarray, $rounds_lost, $rules['loss_pts'], $decklists_posted, $reqdeck);
        $this->multiplyAndAddPoints($total_pointarray, $rounds_bye, $rules['bye_pts'], $decklists_posted, $reqdeck);
        $this->multiplyAndAddPoints($total_pointarray, $participations, $rules['participation_pts'], $decklists_posted, $reqdeck);

        // Include adjustments.
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT player, event, adjustment, reason FROM season_points WHERE series = ? AND season = ?');
        $stmt or exit($db->error);
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($player, $event, $adjustment, $reason);
        while ($stmt->fetch()) {
            if (!isset($total_pointarray[$player])) {
                $total_pointarray[$player] = [];
            }
            if (!isset($total_pointarray[$player][$event])) {
                $total_pointarray[$player][$event] = 0;
            }
            if (!is_array($total_pointarray[$player][$event])) {
                $total_pointarray[$player][$event] = ['why' => $reason, 'points' => $total_pointarray[$player][$event] + $adjustment];
            }
        }

        // Make totals
        foreach ($total_pointarray as $player => $eventarray) {
            $total_pointarray[$player]['.total'] = 0;
            foreach ($eventarray as $event => $points) {
                if (is_array($points)) {
                    if (is_int($points['points'])) {
                        $total_pointarray[$player]['.total'] += $points['points'];
                    }
                } else {
                    $total_pointarray[$player]['.total'] += $points;
                }
            }
        }

        return $total_pointarray;
    }

    /**
     * @return list<string>
     */
    public function getSeasonEventNames(int $season_number): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM events WHERE series = ? AND season = ? AND events.number != 128 ORDER BY start');
        $stmt or exit($db->error);
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $stmt->bind_result($event);
        $eventnames = [];
        while ($stmt->fetch()) {
            $eventnames[] = $event;
        }
        $stmt->close();

        return $eventnames;
    }

    public function getSeasonCutoff(int $season_number): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT cutoff_ord FROM series_seasons WHERE series = ? AND season = ?');
        $stmt or exit($db->error);
        $stmt->bind_param('sd', $this->name, $season_number);
        $stmt->execute();
        $cutoff = 0;
        $stmt->bind_result($cutoff);
        $stmt->fetch();
        $stmt->close();

        return $cutoff;
    }
}
