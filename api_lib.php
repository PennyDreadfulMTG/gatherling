<?php

//## Helper Functions
function populate($array, $src, $keys)
{
    foreach ($keys as $key) {
        $array[$key] = $src->{$key};
    }

    return $array;
}

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

function error($msg)
{
    $result = [];
    $result['error'] = $msg;
    $result['success'] = false;
    json_headers();
    die(json_encode($result));
}

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
function repr_json_event($event)
{
    $series = new Series($event->series);
    $json = [];
    // Event Properties
    $json = populate($json, $event, ['series', 'season', 'number', 'host', 'cohost', 'active', 'finalized', 'current_round', 'start', 'mainrounds', 'mainstruct', 'finalrounds', 'finalstruct']);

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
                $json['unreported'] = [];
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
            }
        }
    }
    if ($event->finalized) {
        $decks = $event->getDecks();
        $json['decks'] = [];
        foreach ($decks as $d) {
            $json['decks'][] = repr_json_deck($d);
        }

        $json['finalists'] = $event->getFinalists();
        $json['standings'] = [];
        foreach (Standings::getEventStandings($event->name, $event->active) as $s) {
            $json['standings'][] = populate([], $s, ['player', 'active', 'score', 'matches_played', 'matches_won', 'draws', 'games_won', 'games_played', 'byes', 'OP_Match', 'PL_Game', 'OP_Game', 'seed']);
        }
    }

    return $json;
}

function repr_json_deck($deck)
{
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

function repr_json_series($series)
{
    $json = populate([], $series, ['name', 'active', 'start_day', 'start_time', 'organizers', 'mtgo_room', 'this_season_format', 'this_season_master_link', 'this_season_season']);

    return $json;
}

//## Actions

function add_player_to_event($event, $name)
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
    } else {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    }

    return $result;
}

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

    $event = Event::CreateEvent(arg('year'), arg('month'), arg('day'), arg('hour'), $naming, $name,
                    arg('format'), arg('host', ''), arg('cohost', ''), arg('kvalue', ''), arg('series'), arg('season'),
                    arg('number'), arg('threadurl', ''), arg('metaurl', ''), arg('reporturl', ''), arg('prereg_allowed', ''),
                    arg('pkonly', ''), arg('player_reportable', ''), arg('late_entry_limit', ''),
                    arg('mainrounds', ''), arg('mainstruct', ''), arg('finalrounds', ''), arg('finalstruct', ''));

    $result = [];
    $result['success'] = true;
    $result['event'] = $event;

    return $result;
}
