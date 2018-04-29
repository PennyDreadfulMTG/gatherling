<?php

require_once 'lib.php';
session_start();

$action = '';
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
}
// The below is for backwards compat
elseif (isset($_GET['deck'])) {
    $action = 'deckinfo';
} elseif (isset($_GET['addplayer']) && isset($_GET['event'])) {
    $action = 'addplayer';
} elseif (isset($_GET['delplayer']) && isset($_GET['event'])) {
    $action = 'delplayer';
} elseif (isset($_GET['dropplayer']) && isset($_GET['event'])) {
    $action = 'dropplayer';
}

function populate($array, $src, $keys)
{
    foreach ($keys as $key) {
        $array[$key] = $src->{$key};
    }

    return $array;
}

function json_event($event)
{
    $series = new Series($event->series);
    $json = [];
    // Event Properties
    $json = populate($json, $event, ['series', 'season', 'number', 'host', 'cohost', 'active', 'finalized', 'current_round', 'start']);

    // Series Properties
    $json = populate($json, $series, ['mtgo_room']);

    $matches = $event->getMatches();
    if ($matches) {
        $json['matches'] = [];
        $json['unreported'] = [];
        $addrounds = 0;
        $roundnum = 0;
        $timing = 0;
        foreach ($matches as $m) {
            $data = populate([], $m, ['playera', 'playera_wins', 'playerb', 'playerb_wins', 'timing', 'round', 'verification']);
            if ($m->timing > $timing) {
                $timing = $m->timing;
                $addrounds += $roundnum;
            }
            if ($roundnum != $m->round) {
                $roundnum = $m->round;
            }
            $data['round'] = $m->round + $addrounds;
            $json['matches'][] = $data;
            if (!$m->reportSubmitted($m->playera)) {
                $json['unreported'][] = $m->playera;
            }
            if (!$m->reportSubmitted($m->playerb)) {
                $json['unreported'][] = $m->playerb;
            }
        }
    }
    if ($event->finalized) {
        $decks = $event->getDecks();
        $json['decks'] = [];
        foreach ($decks as $d) {
            $json['decks'][] = json_deck($d);
        }

        $json['finalists'] = $event->getFinalists();
        $json['standings'] = [];
        foreach (Standings::getEventStandings($event->name, $event->active) as $s) {
            $json['standings'][] = populate([], $s, ['player', 'active', 'score', 'matches_played', 'matches_won', 'draws', 'games_won', 'games_played', 'byes', 'OP_Match', 'PL_Game', 'OP_Game', 'seed']);
        }
    }

    return $json;
}

function json_deck($deck) {
    $json = [];
    $json['id'] = $deck->id;
    if ($deck->id != 0) {
        $json['found'] = 1;
        $json['name'] = $deck->name;
        $json['archetype'] = $deck->archetype;
        $json['maindeck'] = $deck->maindeck_cards;
        $json['sideboard'] = $deck->sideboard_cards;
    } else {
        $json['found'] = 0;
    }
    return $json;
}

$result = [];
switch ($action) {
    case 'deckinfo':
    $deckid = $_REQUEST['deck'];
    $deck = new Deck($deckid);
    $result = json_deck($deck);
    break;

    case 'eventinfo':
    $eventname = $_REQUEST['event'];
    $event = new Event($eventname);
    $result = json_event($event);
    break;

    case 'addplayer':
    $event = new Event($_GET['event']);
    if ($event->authCheck($_SESSION['username'])) {
        $new = $_GET['addplayer'];
        if ($event->addPlayer($new)) {
            $player = new Player($new);
            $result['success'] = true;
            $result['player'] = $player->name;
            $result['verified'] = $player->verified;
            $result['event_running'] = $event->active == 1;
        } else {
            $result['success'] = false;
        }
    } else {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    }
    break;

    case 'delplayer':
    $event = new Event($_GET['event']);
    if ($event->authCheck($_SESSION['username'])) {
        $old = $_GET['delplayer'];
        $result = [];
        $result['success'] = $event->removeEntry($old);
        $result['player'] = $old;
    } else {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    }
    break;

    case 'dropplayer':
    $event = new Event($_GET['event']);
    if ($event->authCheck($_SESSION['username'])) {
        $playername = $_GET['dropplayer'];
        $event->dropPlayer($playername);
        $result['success'] = true;
        $result['player'] = $playername;
        $result['eventname'] = $event->name;
        $result['round'] = $event->current_round;
    } else {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    }
    break;

    case 'active_events':
    $events = Event::getActiveEvents();
    foreach ($events as $event) {
        $result[$event->name] = json_event($event);
    }
    break;

    case 'recent_events':
    $events = [];
    $db = Database::getConnection();
    $query = $db->query('SELECT e.name as name FROM events e
                         WHERE e.finalized AND e.start < NOW()
                         ORDER BY e.start DESC LIMIT 10');
    while ($row = $query->fetch_assoc()) {
        $events[] = $row['name'];
    }
    $query->close();
    foreach ($events as $eventname) {
        $event = new Event($eventname);
        $result[$event->name] = json_event($event);
    }
    break;

    case 'api_version':
    $result['version'] = 2;
    break;

    default:
    $result['error'] = "Unknown action '{$action}'";
    break;
}

json_headers();
echo json_encode($result);
