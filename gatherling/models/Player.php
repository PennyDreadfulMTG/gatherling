<?php

class Player
{
    public $name;
    public $password;
    public $host;
    public $super;
    public $rememberMe; // if selected will record IP address. Gatherling will automatically log players in of known IP addresses.
    public $ipAddress;
    public $emailAddress;
    public $emailPrivacy;
    public $timezone;
    public $verified;
    public $theme;
    public $discord_id;
    public $discord_handle;
    public $api_key;

    public function __construct($name)
    {
        if ($name == '') {
            $this->name = '';
            $this->password = null;
            $this->super = 0;
            $this->rememberMe = 0;
            $this->verified = 0;
            $this->theme = null;

            return;
        }
        $database = Database::getConnection();
        $stmt = $database->prepare('SELECT name, password, rememberme, INET_NTOA(ipaddress), host, super,
                mtgo_confirmed, email, email_privacy, timezone, theme, discord_id, discord_handle, api_key  FROM players WHERE name = ?');
        $stmt or exit($database->error);

        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->bind_result(
            $this->name,
            $this->password,
            $this->rememberMe,
            $this->ipAddress,
            $this->host,
            $this->super,
            $this->verified,
            $this->emailAddress,
            $this->emailPrivacy,
            $this->timezone,
            $this->theme,
            $this->discord_id,
            $this->discord_handle,
            $this->api_key
        );
        if ($stmt->fetch() == null) {
            throw new Exception('Player '.$name.' is not found.');
        }
        $stmt->close();
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['username']);
    }

    public static function logOut()
    {
        unset($_SESSION['sessionname']);
        unset($_SESSION['username']);
        session_destroy();
    }

    public static function loginName()
    {
        if (self::isLoggedIn()) {
            return $_SESSION['username'];
        } else {
            return false;
        }
    }

    public static function getSessionPlayer()
    {
        if (!isset($_SESSION['username'])) {
            return;
        }

        return new self($_SESSION['username']);
    }

    public static function checkPassword($username, $password)
    {
        if ($username == '' || $password == '') {
            return false;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT password FROM players WHERE name = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($srvpass);
        if (!$stmt->fetch()) {
            return false;
        }
        $stmt->close();

        $hashpwd = hash('sha256', $password);

        return strcmp($srvpass, $hashpwd) == 0;
    }

    public static function getClientIPAddress()
    {
        // this is used with the rememberMe feature to keep players logged in
        // Test if it is a shared client
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        //Is it a proxy address
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public static function saveIPAddress($ipAddress, $player)
    {
        $ipAddress = ip2long($ipAddress);
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE players SET ipaddress = ? WHERE name = ?');
        $stmt or exit($db->error);
        $stmt->bind_param('ds', $ipAddress, $player);
        $stmt->execute();
        $stmt->close();
    }

    public static function findByName($playername)
    {
        $database = Database::getConnection();
        $stmt = $database->prepare('SELECT name FROM players WHERE name = ?');
        $stmt->bind_param('s', $playername);
        $stmt->execute();
        $stmt->bind_result($resname);
        $good = false;
        if ($stmt->fetch()) {
            $good = true;
        }
        $stmt->close();

        if ($good) {
            return new self($playername);
        } else {
            return;
        }
    }

    public static function findByDiscordID($playername)
    {
        $database = Database::getConnection();
        $stmt = $database->prepare('SELECT name FROM players WHERE discord_id = ?');
        $stmt->bind_param('i', $playername);
        $stmt->execute();
        $stmt->bind_result($resname);
        $good = false;
        if ($stmt->fetch()) {
            $good = true;
        }
        $stmt->close();

        if ($good) {
            return new self($resname);
        } else {
            return;
        }
    }

    public static function createByName($playername)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO players(name) VALUES(?)');
        $stmt->bind_param('s', $playername);
        $stmt->execute();
        $stmt->close();

        return self::findByName($playername);
    }

    public static function findOrCreateByName($playername)
    {
        $found = self::findByName($playername);
        if (is_null($found)) {
            return self::createByName($playername);
        }

        return $found;
    }

    public function save()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE players SET password = ?, rememberme = ?, host = ?, super = ?, email = ?, email_privacy = ?, timezone = ?, discord_id = ?, discord_handle = ? WHERE name = ?');
        $stmt->bind_param('sdddsdddss', $this->password, $this->rememberMe, $this->host, $this->super, $this->emailAddress, $this->emailPrivacy, $this->timezone, $this->discord_id, $this->discord_handle, $this->name);
        $stmt->execute();
        $stmt->close();
    }

    public function getIPAddresss()
    {
        return $this->ipAddress;
    }

    public function emailIsPublic()
    {
        return $this->emailPrivacy;
    }

    public function time_zone()
    {
        switch ($this->timezone) {
            case -12: return '[UTC - 12] Baker Island Time';
            case -11: return '[UTC - 11] Niue Time, Samoa Standard Time';
            case -10: return '[UTC - 10] Hawaii-Aleutian Standard Time, Cook Island Time';
            case -9.5: return ':[UTC - 9:30] Marquesas Islands Time';
            case -9: return '[UTC - 9] Alaska Standard Time, Gambier Island Time';
            case -8: return '[UTC - 8] Pacific Standard Time';
            case -7: return '[UTC - 7] Mountain Standard Time';
            case -6: return '[UTC - 6] Central Standard Time';
            case -5: return '[UTC - 5] Eastern Standard Time (Gatherling.com Default Time)';
            case -4.5: return '[UTC - 4:30] Venezuelan Standard Time';
            case -4: return '[UTC - 4] Atlantic Standard Time';
            case -3.5: return '[UTC - 3:30] Newfoundland Standard Time';
            case -3: return '[UTC - 3] Amazon Standard Time, Central Greenland Time';
            case -2: return '[UTC - 2] Fernando de Noronha Time, South Georgia &amp; the South Sandwich Islands Time';
            case -1: return '[UTC - 1] Azores Standard Time, Cape Verde Time, Eastern Greenland Time';
            case 0: return '[UTC] Western European Time, Greenwich Mean Time';
            case 1: return '[UTC + 1] Central European Time, West African Time';
            case 2: return '[UTC + 2] Eastern European Time, Central African Time';
            case 3: return '[UTC + 3] Moscow Standard Time, Eastern African Time';
            case 3.5: return '[UTC + 3:30] Iran Standard Time';
            case 4: return '[UTC + 4] Gulf Standard Time, Samara Standard Time';
            case 4.5: return '[UTC + 4:30] Afghanistan Time';
            case 5: return '[UTC + 5] Pakistan Standard Time, Yekaterinburg Standard Time';
            case 5.5: return '[UTC + 5:30] Indian Standard Time, Sri Lanka Time';
            case 5.75: return '[UTC + 5:45] Nepal Time';
            case 6: return '[UTC + 6] Bangladesh Time, Bhutan Time, Novosibirsk Standard Time';
            case 6.5: return '[UTC + 6:30] Cocos Islands Time, Myanmar Time';
            case 7: return '[UTC + 7] Indochina Time, Krasnoyarsk Standard Time';
            case 8: return '[UTC + 8] Chinese Standard Time, Australian Western Standard Time, Irkutsk Standard Time';
            case 8.75: return '[UTC + 8:45] Southeastern Western Australia Standard Time';
            case 9: return '[UTC + 9] Japan Standard Time, Korea Standard Time, Chita Standard Time';
            case 9.5: return '[UTC + 9:30] Australian Central Standard Time';
            case 10: return '[UTC + 10] Australian Eastern Standard Time, Vladivostok Standard Time';
            case 10.5: return '[UTC + 10:30] Lord Howe Standard Time';
            case 11: return '[UTC + 11] Solomon Island Time, Magadan Standard Time';
            case 11.5: return '[UTC + 11:30] Norfolk Island Time';
            case 12: return '[UTC + 12] New Zealand Time, Fiji Time, Kamchatka Standard Time';
            case 12.75: return '[UTC + 12:45] Chatham Islands Time';
            case 13: return '[UTC + 13] Tonga Time, Phoenix Islands Time';
            case 14: return '[UTC + 14] Line Island Time';
            }
    }

    /** Returns true if a player has hosted at least one event. */
    public function isHost()
    {
        return ($this->super == 1) || (count($this->organizersSeries()) > 0) || ($this->getHostedEventsCount() > 0);
    }

    /** Returns true if a player organizers a series. */
    public function isOrganizer($seriesName = '')
    {
        if ($seriesName == '') {
            return ($this->super == 1) || (count($this->organizersSeries()) > 0);
        } else {
            return Database::single_result("SELECT series
                                        FROM series_organizers
                                        WHERE player = '{$this->name}' AND series = '{$seriesName}'") > 0;
        }
    }

    public function getHostedEvents()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name FROM events WHERE host = ? OR cohost = ?');
        $stmt->bind_param('ss', $this->name, $this->name);
        $stmt->execute();
        $stmt->bind_result($evname);

        $evnames = [];
        while ($stmt->fetch()) {
            $evnames[] = $evname;
        }
        $stmt->close();

        $evs = [];
        foreach ($evnames as $evname) {
            $evs[] = new Event($evname);
        }

        return $evs;
    }

    public function getHostedEventsCount()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT count(name) FROM events WHERE host = ? OR cohost = ?');
        $stmt->bind_param('ss', $this->name, $this->name);
        $stmt->execute();
        $stmt->bind_result($evcount);

        $stmt->fetch();
        $stmt->close();

        return $evcount;
    }

    public function isSuper()
    {
        return $this->super == 1;
    }

    public function getLastEventPlayed()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.name FROM entries n, events e
      WHERE n.player = ? AND e.id = n.event_id ORDER BY UNIX_TIMESTAMP(e.start) DESC');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $lastevname = null;
        $stmt->bind_result($lastevname);
        $stmt->fetch();
        $stmt->close();

        if ($lastevname != null) {
            return new Event($lastevname);
        } else {
            return;
        }
    }

    public function getMatchesEvent($eventname)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT m.id
      FROM matches m, subevents s
      WHERE m.subevent = s.id AND s.parent = ?
      AND (m.playera = ? OR m.playerb = ?)
      ORDER BY s.timing, m.round');
        $stmt->bind_param('sss', $eventname, $this->name, $this->name);
        $stmt->execute();
        $stmt->bind_result($matchid);

        $matchids = [];
        while ($stmt->fetch()) {
            $matchids[] = $matchid;
        }
        $stmt->close();

        $matches = [];
        foreach ($matchids as $matchid) {
            $matches[] = new Match($matchid);
        }

        return $matches;
    }

    public function getDeckEvent($event_id)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT n.deck
      FROM entries n
      WHERE n.event_id = ? AND n.player = ?');
        $stmt->bind_param('ds', $event_id, $this->name);
        $stmt->execute();
        $stmt->bind_result($deckid);
        $stmt->fetch();

        $stmt->close();
        if ($deckid == null) {
            return;
        }

        return new Deck($deckid);
    }

    public function getAllDecks()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT n.deck
      FROM entries n, events e
      WHERE n.player = ? AND n.deck IS NOT NULL AND n.event_id = e.id
      ORDER BY e.start DESC');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($deckid);

        $deckids = [];
        while ($stmt->fetch()) {
            $deckids[] = $deckid;
        }
        $stmt->close();

        $decks = [];
        foreach ($deckids as $deckid) {
            $decks[] = new Deck($deckid);
        }

        return $decks;
    }

    public function getRecentDecks($number = 5)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT n.deck FROM entries n, events e
      WHERE n.player = ? AND n.event_id = e.id AND n.deck IS NOT NULL
      ORDER BY e.start DESC LIMIT $number");
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($deckid);

        $deckids = [];
        while ($stmt->fetch()) {
            $deckids[] = $deckid;
        }
        $stmt->close();

        $decks = [];
        foreach ($deckids as $deckid) {
            $decks[] = new Deck($deckid);
        }

        return $decks;
    }

    // Gets the $number most recent matches.  Ignores matches in progress.
    public function getRecentMatches($number = 6)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT m.id
      FROM matches m, events e, subevents s
      WHERE (m.playera = ? OR m.playerb = ?) AND m.subevent = s.id
       AND s.parent = e.name AND m.result != 'P'
      ORDER BY e.start DESC, s.timing DESC, m.round DESC LIMIT $number");
        $stmt->bind_param('ss', $this->name, $this->name);
        $stmt->execute();
        $stmt->bind_result($matchid);

        $matchids = [];
        while ($stmt->fetch()) {
            $matchids[] = $matchid;
        }
        $stmt->close();

        $matches = [];
        foreach ($matchids as $matchid) {
            $matches[] = new Match($matchid);
        }

        return $matches;
    }

    // Returns all matches that are curently in progress.
    public function getCurrentMatches()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT m.id
      FROM matches m, events e, subevents s
      WHERE (m.playera = ? OR m.playerb = ?) AND m.subevent = s.id AND m.verification != 'verified'
      AND s.parent = e.name AND (m.result = 'P' OR m.result = 'BYE' or m.result = 'League')
      ORDER BY e.start DESC, s.timing DESC, m.round DESC ");
        $stmt->bind_param('ss', $this->name, $this->name);
        $stmt->execute();
        $stmt->bind_result($matchid);

        $matchids = [];
        while ($stmt->fetch()) {
            $matchids[] = $matchid;
        }
        $stmt->close();

        $matches = [];
        foreach ($matchids as $matchid) {
            $matches[] = new Match($matchid);
        }

        return $matches;
    }

    public function getAllMatches()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT m.id
      FROM matches m, events e, subevents s
      WHERE (m.playera = ? OR m.playerb = ?) AND m.subevent = s.id
       AND s.parent = e.name
      ORDER BY e.start ASC, s.timing ASC, m.round ASC');
        $stmt->bind_param('ss', $this->name, $this->name);
        $stmt->execute();
        $stmt->bind_result($matchid);

        $matchids = [];
        while ($stmt->fetch()) {
            $matchids[] = $matchid;
        }
        $stmt->close();

        $matches = [];
        foreach ($matchids as $matchid) {
            $matches[] = new Match($matchid);
        }

        return $matches;
    }

    public function getFilteredMatches($format = '%', $series = '%', $season = '%', $opponent = '%')
    {
        $matches = $this->getAllMatches();
        if ($format != '%') {
            $filteredMatches = [];
            foreach ($matches as $match) {
                if (strcasecmp($match->format, $format) == 0) {
                    $filteredMatches[] = $match;
                }
            }
            $matches = $filteredMatches;
        }

        if ($series != '%') {
            $filteredMatches = [];
            foreach ($matches as $match) {
                if (strcasecmp($match->series, $series) == 0) {
                    $filteredMatches[] = $match;
                }
            }
            $matches = $filteredMatches;
        }

        if ($season != '%') {
            $filteredMatches = [];
            foreach ($matches as $match) {
                if (strcasecmp($match->season, $season) == 0) {
                    $filteredMatches[] = $match;
                }
            }
            $matches = $filteredMatches;
        }

        if ($opponent != '%') {
            $filteredMatches = [];
            foreach ($matches as $match) {
                if (strcasecmp($match->otherPlayer($this->name), $opponent) == 0) {
                    $filteredMatches[] = $match;
                }
            }
            $matches = $filteredMatches;
        }

        return $matches;
    }

    public function getMatchesByDeckName($deckname)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT m.id FROM matches m, entries n, decks d, events e, subevents s
      WHERE d.name = ? AND n.player = ? AND n.deck = d.id
       AND n.event_id = e.id AND s.parent = e.name AND m.subevent = s.id
       AND (m.playera = ? OR m.playerb = ?)');
        $stmt->bind_param('ssss', $deckname, $this->name, $this->name, $this->name);
        $stmt->execute();
        $stmt->bind_result($mid);

        $mids = [];
        while ($stmt->fetch()) {
            $mids[] = $mid;
        }
        $stmt->close();

        $matches = [];
        foreach ($mids as $mid) {
            $matches[] = new Match($mid);
        }

        return $matches;
    }

    public function getOpponents()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT q.p as opp, COUNT(q.p) AS cnt
      FROM (SELECT playera AS p FROM matches WHERE playerb = ?
            UNION ALL
            SELECT playerb AS p FROM matches WHERE playera = ?) AS q
      GROUP BY opp ORDER BY cnt DESC');
        $stmt->bind_param('ss', $this->name, $this->name);
        $stmt->execute();
        $stmt->bind_result($opp, $cnt);

        $opponents = [];

        while ($stmt->fetch()) {
            $opponents[] = ['opp' => $opp, 'cnt' => $cnt];
        }

        return $opponents;
    }

    public function getNoDeckEntries()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT event_id FROM entries n, events e
      WHERE n.player = ? AND n.deck IS NULL AND n.event_id = e.id
      ORDER BY e.start DESC');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($event_id);

        $event_ids = [];
        while ($stmt->fetch()) {
            $event_ids[] = $event_id;
        }
        $stmt->close();

        $entries = [];
        foreach ($event_ids as $event_id) {
            $entries[] = new Entry($event_id, $this->name);
        }

        return $entries;
    }

    // TODO: remove ignore functionality for decks. Not sure what this function does. Part of it?
    public function getUnenteredCount()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT count(event_id) FROM entries n, events e
      WHERE n.player = ? AND n.deck IS NULL AND n.event_id = e.id
      AND n.ignored = false');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($noentrycount);
        $stmt->fetch();
        $stmt->close();

        return $noentrycount;
    }

    public function getRating($format = 'Composite', $date = '3000-01-01 00:00:00')
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT rating
                          FROM ratings
                          WHERE player = ?
                          AND updated < ?
                          AND format = ?
                          ORDER BY updated
                          DESC LIMIT 1');
        $stmt->bind_param('sss', $this->name, $date, $format);
        $stmt->execute();
        $stmt->bind_result($rating);

        if ($stmt->fetch() == null) {
            $rating = 0; // was set to 1600, I am going to use it to only show ratings for formats the players has played in
        }

        $stmt->close();

        return $rating;
    }

    public function getRatingRecord($format = 'Composite', $date = '3000-01-01 00:00:00')
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT wins, losses
                          FROM ratings
                          WHERE player = ?
                          AND updated < ?
                          AND format = ?
                          ORDER BY updated
                          DESC LIMIT 1');
        $stmt->bind_param('sss', $this->name, $date, $format);
        $stmt->execute();
        $wins = 0;
        $losses = 0;
        $stmt->bind_result($wins, $losses);
        $stmt->fetch();
        $stmt->close();

        return $wins.'-'.$losses;
    }

    public function getMaxRating($format = 'Composite')
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT MAX(rating) AS max
                          FROM ratings r
                          WHERE r.player = ?
                          AND r.format = ?
                          AND r.wins + r.losses >= 20');
        $stmt->bind_param('ss', $this->name, $format);
        $stmt->execute();
        $max = null;
        $stmt->bind_result($max);
        $stmt->fetch();
        $stmt->close();

        return $max;
    }

    public function getMinRating($format = 'Composite')
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT MIN(rating) AS min
                          FROM ratings r
                          WHERE r.player = ? AND r.format = ?
                          AND r.wins + r.losses >= 20');
        $stmt->bind_param('ss', $this->name, $format);
        $stmt->execute();
        $min = null;
        $stmt->bind_result($min);
        $stmt->fetch();
        $stmt->close();

        return $min;
    }

    public function getRecord()
    {
        $matches = $this->getAllMatches();

        $wins = 0;
        $losses = 0;
        $draws = 0;

        foreach ($matches as $match) {
            if ($match->playerWon($this->name)) {
                $wins = $wins + 1;
            } elseif ($match->playerLost($this->name)) {
                $losses = $losses + 1;
            } elseif ($match->playerBye($this->name)) {
                $wins = $wins + 1;
            } elseif ($match->playerMatchInProgress($this->name)) {
                // do nothing since match is in progress and there are no results
            } else {
                $draws = 1;
            }
        }

        if ($draws == 0) {
            return $wins.'-'.$losses;
        } else {
            return $wins.'-'.$losses.'-'.$draws;
        }
    }

    public function getRecordVs($opponent)
    {
        $matches = $this->getAllMatches();

        $wins = 0;
        $losses = 0;
        $draws = 0;

        foreach ($matches as $match) {
            $otherplayer = $match->otherPlayer($this->name);
            if (strcasecmp($otherplayer, $opponent) == 0) {
                if ($match->playerWon($this->name)) {
                    $wins = $wins + 1;
                } elseif ($match->playerLost($this->name)) {
                    $losses = $losses + 1;
                } else {
                    $draws = $draws + 1;
                }
            }
        }

        if ($draws == 0) {
            return $wins.'-'.$losses;
        } else {
            return $wins.'-'.$losses.'-'.$draws;
        }
    }

    public function getStreak($type = 'W')
    {
        $matches = $this->getAllMatches();

        $results = [];
        foreach ($matches as $match) {
            $thisres = 'D';
            if ($match->playerWon($this->name)) {
                $thisres = 'W';
            } elseif ($match->playerLost($this->name)) {
                $thisres = 'L';
            }

            $results[] = $thisres;
        }

        $max = 0;
        $streak = 0;
        foreach ($results as $result) {
            if ($result == $type) {
                $streak++;
            } else {
                $streak = 0;
            }
            if ($streak > $max) {
                $max = $streak;
            }
        }

        return $max;
    }

    //# changed the select statment, brings back list of opps ordered by games lost
    public function getRival()
    {
        $db = Database::getConnection();

        $stmt = $db->prepare('SELECT q.p AS opp, sum(losses) losses FROM
       (select  playera as p, playerb_losses as losses from matches where playerb = ? and playerb_losses > 0
        UNION ALL
        select  playerb as p, playera_losses as losses from matches where playera = ? and playera_losses > 0
        ) AS q GROUP BY q.p ORDER BY losses DESC LIMIT 1');
        if (!$stmt) {
            exit($db->error);
        }
        $stmt->bind_param('ss', $this->name, $this->name);
        $stmt->execute();
        $stmt->bind_result($rival, $numtimes);
        $stmt->fetch();
        $stmt->close();

        if ($rival != null) {
            return new self($rival);
        } else {
            return;
        }
    }

    public function getFavoriteNonLand()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT c.name, sum(t.qty) AS qty
      FROM cards c, deckcontents t, entries n
      WHERE n.player = ? AND t.deck = n.deck AND t.issideboard = 0
       AND t.card = c.id AND c.type NOT LIKE '%Land%'
       GROUP BY c.name ORDER BY qty DESC, c.name LIMIT 1");
        if (!$stmt) {
            exit($db->error);
        }
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $cardname = 'none';
        $qty = '0';
        $stmt->bind_result($cardname, $qty);
        $stmt->fetch();

        $stmt->close();

        return "$cardname ($qty)";
    }

    public function getFavoriteLand()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT c.name, sum(t.qty) AS qty
      FROM cards c, deckcontents t, entries n
      WHERE n.player = ? AND t.deck = n.deck AND t.issideboard = 0
       AND t.card = c.id AND c.type LIKE '%Land%'
      GROUP BY c.name ORDER BY qty DESC, c.name LIMIT 1");
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $cardname = 'none';
        $qty = '0';
        $stmt->bind_result($cardname, $qty);
        $stmt->fetch();

        $stmt->close();

        return "$cardname ($qty)";
    }

    public function getMedalCount($type = 'all')
    {
        $db = Database::getConnection();
        if ($type == 'all') {
            $stmt = $db->prepare("SELECT count(*) as c FROM entries
        WHERE player = ? AND medal != 'dot'");
            $stmt->bind_param('s', $this->name);
        } else {
            $stmt = $db->prepare('SELECT count(*) as c FROM entries
        WHERE player = ? AND medal = ?');
            $stmt->bind_param('ss', $this->name, $type);
        }
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count;
    }

    public function getLastEventWithTrophy()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.name
      FROM events e, entries n, trophies t
      WHERE n.event_id = e.id AND n.player = ?
       AND n.medal = "1st" and t.event = e.name AND t.image IS NOT NULL
       ORDER BY e.start DESC LIMIT 1');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->store_result();

        $res = null;
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($eventname);
            $stmt->fetch();
            $res = $eventname;
        }
        $stmt->close();

        return $res;
    }

    public function getEventsWithTrophies()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.name
      FROM events e, entries n, trophies t
      WHERE n.event_id = e.id AND n.player = ?
       AND n.medal = "1st" and t.event = e.name AND t.image IS NOT NULL
       ORDER BY e.start DESC');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($eventname);
        $stmt->store_result();

        $events = [];
        while ($stmt->fetch()) {
            $events[] = $eventname;
        }
        $stmt->close();

        return $events;
    }

    public function getFormatsPlayed()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.format FROM entries n, events e, formats f
      WHERE n.player = ? AND n.event_id = e.id AND e.format = f.name
      GROUP BY e.format ORDER BY f.priority DESC, f.name');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($format);

        $formats = [];
        while ($stmt->fetch()) {
            $formats[] = $format;
        }
        $stmt->close();

        return $formats;
    }

    public function getFormatsPlayedStats()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.format, count(n.event_id) AS cnt
      FROM entries n, events e
      WHERE n.player = ? AND n.event_id = e.id
      GROUP BY e.format ORDER BY cnt DESC');
        $stmt || exit($db->error);
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($format, $count);

        $formats = [];
        while ($stmt->fetch()) {
            $formats[] = ['format' => $format, 'cnt' => $count];
        }
        $stmt->close();

        return $formats;
    }

    public function getMedalStats()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT count(n.event_id) AS cnt, n.medal
      FROM entries n WHERE n.player = ? AND n.medal != 'dot'
      GROUP BY n.medal ORDER BY n.medal");
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($cnt, $medal);

        $medals = ['1st' => 0, '2nd' => 0, 't4' => 0, 't8' => 0];
        while ($stmt->fetch()) {
            $medals[$medal] = $cnt;
        }
        $stmt->close();

        return $medals;
    }

    public function getBestDeckStats()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT d.name, count(n.player) AS cnt,
      max(d.id) AS id, sum(n.medal='t8') AS t8, sum(n.medal='t4') AS t4,
      sum(n.medal='2nd') AS 2nd, sum(n.medal='1st') AS 1st,
      sum(n.medal='t8')+2*sum(n.medal='t4')
                       +4*sum(n.medal='2nd')+8*sum(n.medal='1st') AS score
      FROM decks d, entries n
      WHERE d.id = n.deck AND n.player = ?
      GROUP BY d.name
      ORDER BY score DESC, 1st DESC, 2nd DESC, t4 DESC, t8 DESC, cnt ASC");
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($name, $cnt, $id, $t8, $t4, $secnd, $first, $score);

        $res = [];
        while ($stmt->fetch()) {
            $res[] = ['name'      => $name,
                'cnt'             => $cnt,
                'id'              => $id,
                't8'              => $t8,
                't4'              => $t4,
                '2nd'             => $secnd,
                '1st'             => $first,
                'score'           => $score, ];
        }
        $stmt->close();

        return $res;
    }

    public function getSeriesPlayedStats()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.series, count(n.event_id) AS cnt
      FROM events e, entries n
      WHERE n.player = ? AND n.event_id = e.id
      GROUP BY e.series ORDER BY cnt DESC');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($series, $count);

        $res = [];
        while ($stmt->fetch()) {
            $res[] = ['series' => $series, 'cnt' => $count];
        }
        $stmt->close();

        return $res;
    }

    public function getSeriesPlayed()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.series FROM entries n, events e, series s
      WHERE n.player = ? AND n.event_id = e.id AND e.series = s.name
      GROUP BY e.series ORDER BY s.isactive DESC, s.name');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($series);

        $result = [];

        while ($stmt->fetch()) {
            $result[] = $series;
        }

        $stmt->close();

        return $result;
    }

    public function getSeasonsPlayed()
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT e.season FROM entries n, events e
      WHERE n.player = ? AND n.event_id = e.id
      GROUP BY e.season ORDER BY e.season ASC');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($season);

        $seasons = [];
        while ($stmt->fetch()) {
            $seasons[] = $season;
        }

        $stmt->close();

        return $seasons;
    }

    // TODO: Is this part of the deck ignore functionality? If so remove it.
    public function setIgnoreEvent($eventid, $ignored)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE entries SET ignored = ? WHERE event_id = ? AND player = ?');
        $stmt->bind_param('ids', $ignored, $eventid, $this->name);
        $stmt->execute();
        $stmt->close();
    }

    public function setPassword($new_password)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE players SET password = ? WHERE name = ?');
        $hash_pass = hash('sha256', $new_password);
        $stmt->bind_param('ss', $hash_pass, $this->name);
        $stmt->execute();
        $stmt->close();
    }

    public function setVerified($toset)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE players SET mtgo_confirmed = ? WHERE name = ?');
        $setint = $toset ? 1 : 0;
        $stmt->bind_param('is', $setint, $this->name);
        $stmt->execute();
        $stmt->close();
    }

    public function setChallenge($new_challenge)
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE players SET mtgo_challenge = ? WHERE name = ?');
        $stmt->bind_param('ss', $new_challenge, $this->name);
        $stmt->execute();
        $stmt->close();
    }

    public function checkChallenge($challenge)
    {
        if (strlen($challenge) == 0) {
            return false;
        }
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT name, mtgo_challenge FROM players WHERE name = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $stmt->bind_result($verifyplayer, $db_challenge);
        $stmt->fetch();
        $stmt->close();
        if ((strcasecmp($verifyplayer, $this->name) == 0) && (strcasecmp($db_challenge, $challenge) == 0)) {
            return true;
        } else {
            $error_log = "Player = '{$this->name}' Challenge = '{$challenge}' Verify = '{$verifyplayer}' DBChallenge = '{$db_challenge}'\n";
            file_put_contents('/var/www/pdcmagic.com/gatherling/challenge.log', $error_log, FILE_APPEND);

            return false;
        }
    }

    public function organizersSeries()
    {
        if ($this->isSuper()) {
            return Series::allNames();
        }
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT series FROM series_organizers WHERE player = ?');
        $stmt->bind_param('s', $this->name);
        $stmt->execute();
        $series = [];
        $stmt->bind_result($seriesname);
        while ($stmt->fetch()) {
            $series[] = $seriesname;
        }
        $stmt->close();

        return $series;
    }

    public function linkTo()
    {
        $result = "<a href=\"profile.php?player={$this->name}\">$this->name";
        if ($this->verified == 1) {
            $result .= image_tag('verified.png', ['width' => '12', 'height' => '12']);
        }
        $result .= '</a>';

        return $result;
    }

    public static function activeCount()
    {
        return Database::single_result('SELECT count(name) FROM players where password is not null');
    }

    public static function verifiedCount()
    {
        return Database::single_result('SELECT count(name) FROM players where mtgo_confirmed = 1');
    }
}
