<?php

use Gatherling\Database;
use Gatherling\Deck;
use Gatherling\Event;
use Gatherling\Player;
use Gatherling\Series;

require_once 'lib.php';
require_once 'api_lib.php';

$action = '';
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
}

$result = [];
switch ($action) {
    case 'deckinfo':
    case 'deck_info':
        $deckid = $_REQUEST['deck'];
        $deck = new Deck($deckid);
        $result = repr_json_deck($deck);
        break;

    case 'eventinfo':
    case 'event_info':
        if (Event::exists(arg('event'))) {
            $event = new Event(arg('event'));
            $result = repr_json_event($event);
        } else {
            $result['error'] = 'Event not found';
        }
        break;

    case 'seriesinfo':
    case 'series_info':
        $seriesname = $_REQUEST['series'];

        try {
            $series = new Series($seriesname);
            $result = repr_json_series($series);
            $result['success'] = true;
        } catch (Exception $e) {
            $result['success'] = false;
            $result['error'] = $e->getMessage();
        }
        break;

    case 'addplayer':
    case 'add_player':
        auth();
        $event = new Event(arg('event'));
        $player = arg('addplayer');
        $decklist = arg('decklist', '');
        $result = add_player_to_event($event, $player, $decklist);
        break;

    case 'delplayer':
    case 'delete_player':
        auth();
        $event = new Event($_GET['event']);
        $player = $_GET['delplayer'];
        $result = delete_player_from_event($event, $player);
        break;

    case 'dropplayer':
    case 'drop_player':
        $event = new Event($_GET['event']);
        $player = $_GET['dropplayer'];
        $result = drop_player_from_event($event, $player);
        break;

    case 'add_deck':
        $event = new Event(arg('event'));
        $player = arg('player');
        $entries = $event->getEntries();
        foreach ($entries as $entry) {
            if ($entry->player->name == $player) {
                if ($entry->deck) {
                    error('Decklist already exists');
                }
                // TODO
            }
        }

        break;

    case 'active_events':
        $events = Event::getActiveEvents();
        foreach ($events as $event) {
            $result[$event->name] = repr_json_event($event);
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
            $result[$event->name] = repr_json_event($event);
        }
        break;

    case 'upcoming_events':
        $events = [];
        $db = Database::getConnection();
        $query = $db->query('SELECT e.name as name FROM events e
                         WHERE e.start > NOW()
                         ORDER BY e.start ASC');
        while ($row = $query->fetch_assoc()) {
            $events[] = $row['name'];
        }
        $query->close();
        foreach ($events as $eventname) {
            $event = new Event($eventname);
            $result[$event->name] = repr_json_event($event);
        }
        break;

    case 'create_series':
        $series = $_REQUEST['series'];
        $active = true;
        $day = 'Monday';
        if (isset($_REQUEST['day'])) {
            $day = $_REQUEST['day'];
        }
        $result = create_series($series, $active, $day);
        break;

    case 'create_event':
        auth();
        $result = create_event();
        break;

    case 'create_pairing':
        auth();
        $event = new Event(arg('event'));
        $round = arg('round');
        $a = arg('player_a');
        $b = arg('player_b');
        $res = arg('res', 'P');

        create_pairing($event, $round, $a, $b, $res);
        break;

    case 'api_version':
        $result['version'] = 2;
        break;

    case 'whoami':
        $auth_error = auth();
        $player = Player::getSessionPlayer();
        if (is_null($player)) {
            error('Not Logged in', ['name' => null, 'auth_error' => $auth_error]);
        }

        $result = repr_json_player($player);
        break;

    case 'whois':
        $discord = arg('discordid', 0);
        $handle = arg('discord_handle', '');
        if ($discord) {
            $name = $discord;
            $player = Player::findByDiscordID($discord);
        } elseif ($handle) {
            $name = $handle;
            $player = Player::findByDiscordHandle($handle);
        } else {
            $name = arg('name');
            $player = Player::findByName($name);
        }
        if (is_null($player)) {
            error("No player identified by $name");
        }
        $result = repr_json_player($player);
        break;

    case 'find_player':
        $name = arg('name');
        $result = [];
        if ($player = Player::findByDiscordID($name)) {
            $result[] = repr_json_player($player, 'discord');
        }
        if ($player = Player::findByDiscordHandle($name)) {
            $result[] = repr_json_player($player, 'discord');
        }
        if ($player = Player::findByName($name)) {
            $result[] = repr_json_player($player, 'gatherling');
        }
        if ($player = Player::findByMTGO($name)) {
            $result[] = repr_json_player($player, 'mtgo');
        }
        if ($player = Player::findByMTGA($name)) {
            $result[] = repr_json_player($player, 'mtga');
        }

        break;

    case 'generate_apikey':
        if (!auth()) {
            error('Not Logged in');
        }
        $player = Player::getSessionPlayer();
        $result['username'] = $player->name;
        $result['key'] = $player->setApiKey();
        break;

    case 'known_cards_catalog':
        $result = card_catalog();
        break;

    case 'cardname_from_id':
        $result = cardname_from_id(arg('id'));
        break;

    case 'start_event':
        auth();
        $event = new Event(arg('event'));
        $event->startEvent(true);
        $result = repr_json_event($event);
        break;

    default:
        $result['error'] = "Unknown action '{$action}'";
        break;
}

json_headers();
echo json_encode($result);
