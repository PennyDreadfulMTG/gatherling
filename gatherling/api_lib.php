<?php

require_once 'lib.php';

//## Helper Functions

use Gatherling\Database;
use Gatherling\Deck;
use Gatherling\Event;
use Gatherling\Player;
use Gatherling\Series;
use Gatherling\Standings;

function populate($array, $src, $keys)
{
    foreach ($keys as $key) {
        $array[$key] = $src->{$key};
    }

    return $array;
}

/** @return bool  */
function is_admin()
{
    if (!isset($_SESSION['infobot'])) {
        $_SESSION['infobot'] = false;
    }
    if (strncmp($_SERVER['HTTP_USER_AGENT'], 'infobot', 7) == 0 && $_REQUEST['passkey'] == $CONFIG['infobot_passkey']) {
        $_SESSION['infobot'] = true;
        header('X-InfoBot: true');

        return true;
    }

    if (!Player::isLoggedIn()) {
        header('X-Logged-In: false');
    } elseif (Player::getSessionPlayer()->isSuper()) {
        header('X-Admin: true');

        return true;
    }
    header('X-Admin: false');

    return false;
}

/** @return bool  */
function auth()
{
    if (isset($_SERVER['HTTP_X_USERNAME']) && isset($_SERVER['HTTP_X_APIKEY'])) {
        $username = $_SERVER['HTTP_X_USERNAME'];
        $apikey = $_SERVER['HTTP_X_APIKEY'];
        if (is_null($username) || is_null($apikey)) {
            return false;
        }

        $player = Player::findByName($username);
        if (is_null($player)) {
            return false;
        }
        if ($player->api_key == $apikey) {
            $_SESSION['username'] = $player->name;
        }
    }

    return Player::isLoggedIn();
}

/**
 * @param string $msg
 * @param mixed  $extra
 *
 * @return never
 */
function error($msg, $extra = null)
{
    $result = [];
    if (is_array($extra)) {
        $result = populate([], (object) $extra, array_keys($extra));
    }
    $result['error'] = $msg;
    $result['success'] = false;
    json_headers();
    exit(json_encode($result));
}

/**
 * @param string $key
 * @param mixed  $default
 *
 * @return mixed
 */
function arg($key, $default = null)
{
    if (!isset($_REQUEST[$key])) {
        if ($default !== null) {
            return $default;
        }

        return error("Missing argument '$key'");
    }

    return $_REQUEST[$key];
}

//## Models
/**
 * @param Gatherling\Event $event
 *
 * @return mixed
 */
function repr_json_event($event)
{
    $series = new Series($event->series);
    $json = [];
    // Event Properties
    $json = populate($json, $event, ['id', 'name', 'series', 'season', 'number', 'format', 'host', 'cohost', 'active', 'finalized', 'current_round', 'start', 'mainrounds', 'mainstruct', 'finalrounds', 'finalstruct']);
    if ($event->client == 1) {
        $json['client'] = 'mtgo';
    } elseif ($event->client == 2) {
        $json['client'] = 'arena';
    } elseif ($event->client == 3) {
        $json['client'] = 'paper';
    } else {
        $json['client'] = $event->client;
    }

    // Series Properties
    $json = populate($json, $series, ['mtgo_room']);

    $matches = $event->getMatches();
    if ($matches) {
        $json['matches'] = [];
        $json['unreported'] = [];
        $json['waiting_on'] = [];
        $addrounds = 0;
        $roundnum = 0;
        $timing = 0;
        foreach ($matches as $m) {
            $data = populate([], $m, ['id', 'playera', 'playera_wins', 'playerb', 'playerb_wins', 'timing', 'round', 'verification']);
            if ($m->timing > $timing) {
                $timing = $m->timing;
                $addrounds += $roundnum;
            }
            if ($roundnum != $m->round) {
                $roundnum = $m->round;
                $json['unreported'] = [];
                $json['waiting_on'] = [];
            }
            $data['round'] = $m->round + $addrounds;
            $json['matches'][] = $data;
            if ($m->verification != 'verified') {
                if (!$m->reportSubmitted($m->playera)) {
                    $json['unreported'][] = $m->playera;
                }
                if (!$m->reportSubmitted($m->playerb)) {
                    $json['unreported'][] = $m->playerb;
                }
                if (!$m->reportSubmitted($m->playera) && $m->reportSubmitted($m->playerb)) {
                    $json['waiting_on'][] = $m->playera;
                } elseif ($m->reportSubmitted($m->playera) && !$m->reportSubmitted($m->playerb)) {
                    $json['waiting_on'][] = $m->playerb;
                }
            }
        }
    }
    if ($event->finalized) {
        $decks = $event->getDecks();
        $json['decks'] = [];
        foreach ($decks as $d) {
            $json['decks'][] = repr_json_deck($d);
        }
    }

    $json['finalists'] = $event->getFinalists();
    $json['standings'] = [];
    $json['players'] = [];
    foreach (Standings::getEventStandings($event->name, 0) as $s) {
        $json['standings'][] = populate([], $s, ['player', 'active', 'score', 'matches_played', 'matches_won', 'draws', 'games_won', 'games_played', 'byes', 'OP_Match', 'PL_Game', 'OP_Game', 'seed']);
        $json['players'][] = repr_json_player(new Player($s->player), $event->client);
    }

    return $json;
}

/**
 * @param Gatherling\Deck $deck
 *
 * @return mixed
 */
function repr_json_deck($deck)
{
    $json = [];
    $json['id'] = $deck->id;
    if ($deck->id != 0) {
        $json['found'] = 1;
        $json = populate($json, $deck, ['playername', 'name', 'archetype', 'notes']);
        $json['maindeck'] = (object) $deck->maindeck_cards;
        $json['sideboard'] = (object) $deck->sideboard_cards;
    } else {
        $json['found'] = 0;
    }

    return $json;
}

/**
 * @param Gatherling\Series $series
 *
 * @return mixed
 */
function repr_json_series($series)
{
    $json = populate([], $series, ['name', 'active', 'start_day', 'start_time', 'organizers', 'mtgo_room', 'this_season_format', 'this_season_master_link', 'this_season_season', 'discord_guild_id', 'discord_channel_id', 'discord_channel_name', 'discord_guild_name']);
    $mostRecent = $series->mostRecentEvent();
    $json['most_recent_season'] = $mostRecent->season;
    $json['most_recent_number'] = $mostRecent->number;
    $json['most_recent_id'] = $mostRecent->id;
    return $json;
}

/**
 * @param Gatherling\Player $player
 * @param int               $client
 *
 * @return mixed
 */
function repr_json_player($player, $client = null)
{
    $json = populate([], $player, ['name', 'verified', 'discord_id', 'discord_handle', 'mtga_username', 'mtgo_username']);
    $json['display_name'] = $player->gameName($client);

    return $json;
}

//## Actions

/**
 * @param Gatherling\Event $event
 * @param string           $name
 * @param string           $decklist
 *
 * @return (bool|string|int)[]|false[]|(string|false)[]
 */
function add_player_to_event($event, $name, $decklist)
{
    if ($event->authCheck($_SESSION['username'])) {
        if ($event->addPlayer($name)) {
            $player = new Player($name);
            $result['success'] = true;
            $result['player'] = $player->name;
            $result['verified'] = $player->verified;
            $result['event_running'] = $event->active == 1;
        } else {
            $result['success'] = false;
        }
        if (!empty($decklist))
        {
            $decklist = str_replace("|", "\n", $decklist);

            $deck = new Deck(0);
            $deck->playername = $player->name;
            $deck->eventname = $event->name;
            $deck->event_id = $event->id;
            $deck->maindeck_cards = parseCardsWithQuantity($decklist);
            $deck->sideboard_cards = parseCardsWithQuantity("");
            $deck->save();

            return $deck;
        }
    } else {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    }

    return $result;
}

/**
 * @param Gatherling\Event $event
 * @param string           $name
 *
 * @return array
 */
function delete_player_from_event($event, $name)
{
    if ($event->authCheck($_SESSION['username'])) {
        $result = [];
        $result['success'] = $event->removeEntry($name);
        $result['player'] = $name;
    } else {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    }

    return $result;
}

/**
 * @param Gatherling\Event $event
 * @param string           $name
 *
 * @return array
 */
function drop_player_from_event($event, $name)
{
    if ($event->authCheck($_SESSION['username'])) {
        $event->dropPlayer($name);
        $result['success'] = true;
        $result['player'] = $name;
        $result['eventname'] = $event->name;
        $result['round'] = $event->current_round;
    } else {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    }

    return $result;
}

/**
 * @param string $newseries
 * @param bool   $active
 * @param string $day
 *
 * @return array
 */
function create_series($newseries, $active, $day)
{
    $result = [];
    if (!is_admin()) {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    } else {
        $series = new Series('');
        $series->name = $newseries;
        $series->active = $active;
        $series->start_time = '0:00:00';
        $series->start_day = $day;
        $series->save();

        $result['message'] = "New series $series->name was created!";
        $result['success'] = true;
        $result['series'] = $series;
    }

    return $result;
}

function create_event()
{
    $name = arg('name', '');
    $naming = '';
    if ($name == '') {
        $naming = 'auto';
    }

    $event = Event::CreateEvent(
        arg('year'),
        arg('month'),
        arg('day'),
        arg('hour'),
        $naming,
        $name,
        arg('format'),
        arg('host', ''),
        arg('cohost', ''),
        arg('kvalue', ''),
        arg('series'),
        arg('season'),
        arg('number'),
        arg('threadurl', ''),
        arg('metaurl', ''),
        arg('reporturl', ''),
        arg('prereg_allowed', ''),
        arg('player_reportable', ''),
        arg('late_entry_limit', ''),
        arg('private', ''),
        arg('mainrounds', ''),
        arg('mainstruct', ''),
        arg('finalrounds', ''),
        arg('finalstruct', ''),
        arg('client', 1)
    );

    $result = [];
    $result['success'] = true;
    $result['event'] = $event;

    return $result;
}

/**
 * @param Gatherling\Event $event
 * @param mixed            $round
 * @param string           $a
 * @param string           $b
 * @param string           $res
 *
 * @return void
 */
function create_pairing($event, $round, $a, $b, $res)
{
    if (!is_admin() && !$event->authCheck(Player::loginName())) {
        error('Unauthorized');
    }

    $playerA = new Standings($event->name, $a);
    $playerB = new Standings($event->name, $b);
    switch ($res) {
        case '2-0':
            $pAWins = 2;
            $pBWins = 0;
            $res = 'A';
            break;
        case '2-1':
            $pAWins = 2;
            $pBWins = 1;
            $res = 'A';
            break;
        case '1-2':
            $pAWins = 1;
            $pBWins = 2;
            $res = 'B';
            break;
        case '0-2':
            $pAWins = 0;
            $pBWins = 2;
            $res = 'B';
            break;
        case 'D':
        case 'draw':
            $pAWins = 1;
            $pBWins = 1;
            $res = 'D';
            break;
        case 'P':
        case 'paired':
            $res = 'P';
            break;
        default:
            error('unexpected value for res.  Expected one of [2-0, 2-1, 1-2, 0-2, D, P].');
            break;
    }
    if ($res == 'P') {
        $event->addPairing($playerA, $playerB, $round, $res);
    } else {
        $event->addMatch($playerA, $playerB, $round, $res, $pAWins, $pBWins);
    }
}

/** @return string[]  */
function card_catalog()
{
    $result = [];
    $db = Database::getConnection();
    $query = $db->query('SELECT c.name as name FROM cards c');
    while ($row = $query->fetch_assoc()) {
        if (!in_array($row['name'], $result)) {
            $result[] = $row['name'];
        }
    }
    $query->close();

    return $result;
}

/**
 * @param string $id
 *
 * @throws Exception
 *
 * @return string
 */
function cardname_from_id($id)
{
    $sql = 'SELECT c.name as name FROM cards c WHERE c.scryfallId = ?';
    $name = Database::single_result_single_param($sql, 's', $id);

    return $name;
}
