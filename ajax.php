<?php
require_once 'lib.php';
session_start();

$action = '';
if (isset($_REQUEST['action']))
{
    $action = $_REQUEST['action'];
}
// The below is for backwards compat
else if (isset($_GET['deck'])) {
    $action = 'deckinfo';
} elseif (isset($_GET['addplayer']) && isset($_GET['event'])) {
    $action = 'addplayer';
} elseif (isset($_GET['delplayer']) && isset($_GET['event'])) {
    $action = 'delplayer';
} elseif (isset($_GET['dropplayer']) && isset($_GET['event'])) {
    $action = 'dropplayer';
}

function populate($array, $src, $keys) {
    foreach ($keys as $key){
        $array[$key] = $src->{$key};
    }
    return $array;
}

function json_event($event) {
    $series = new Series($event->series);
    $json = array();
    // Event Properties
    $json = populate($json, $event, array('series', 'season', 'number', 'host', 'cohost', 'active', 'finalized', 'current_round', 'start'));

    // Series Properties
    $json = populate($json, $series, array('mtgo_room'));

    $matches = $event->getMatches();
    if ($matches){
        $json['matches'] = array();
        $addrounds = 0;
        $roundnum = 0;
        $timing = 0;
        foreach ($matches as $m) {
            $data = populate(array(), $m, array('playera', 'playera_wins', 'playerb', 'playerb_wins', 'timing', 'round', 'verification'));
            if ($m->timing > $timing) {
                $timing = $m->timing;
                $addrounds += $roundnum;
            }
            if ($roundnum != $m->round) {
                $roundnum = $m->round;
            }
            $data['round'] = $m->round + $addrounds;
            $json['matches'][] = $data;
        }
    }

    return $json;
}

$result = array();
switch ($action){
    case 'deckinfo':
    $deckid = $_GET['deck'];
    $deck = new Deck($deckid);
    $result["id"] = $deckid;
    if ($deck->id != 0) {
        $result["found"] = 1;
        $result["name"] = $deck->name;
        $result["archetype"] = $deck->archetype;
        $result["maindeck"] = $deck->maindeck_cards;
        $result["sideboard"] = $deck->sideboard_cards;
    } else {
        $result["found"] = 0;
    }
    break;

    case 'addplayer':
    $event = new Event($_GET['event']);
    if ($event->authCheck($_SESSION['username'])) {
        $new = $_GET['addplayer'];
        if ($event->addPlayer($new)) {
            $player = new Player($new);
            $result["success"] = true;
            $result["player"] = $player->name;
            $result["verified"] = $player->verified;
            $result["event_running"] = $event->active == 1;
        } else {
            $result["success"] = false;
        }
    }
    else
    {
        $result['error'] = 'Unauthorized';
        $result["success"] = false;
    }
    break;

    case 'delplayer':
    $event = new Event($_GET['event']);
    if ($event->authCheck($_SESSION['username'])) {
        $old = $_GET['delplayer'];
        $result = array();
        $result['success'] = $event->removeEntry($old);
        $result['player'] = $old;
    }
    else
    {
        $result['error'] = 'Unauthorized';
        $result["success"] = false;
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
    }
    else
    {
        $result['error'] = 'Unauthorized';
        $result["success"] = false;
    }
    break;

    case 'active_events':
    $events = Event::getActiveEvents();
    foreach ($events as $event) {
        $result[$event->name] = json_event($event);
    }
    break;
    case "api_version":
    $result['version'] = 2;
    break;
    default:
    $result['error'] = "Unknown action '{$action}'";
}

json_headers();
echo json_encode($result);
