<?php

declare(strict_types=1);

require_once 'lib.php';

//## Helper Functions

use Gatherling\Data\DB;
use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Database;
use Gatherling\Models\Standings;

use function Gatherling\Helpers\config;
use function Gatherling\Helpers\server;
use function Gatherling\Helpers\request;
use function Gatherling\Helpers\session;

/**
 * @param array<string, mixed> $array
 * @param object $src
 * @param array<string> $keys
 *
 * @return array<string, mixed>
 */
function populate(array $array, object $src, array $keys): array
{
    foreach ($keys as $key) {
        $array[$key] = $src->{$key};
    }

    return $array;
}

function is_admin(): bool
{
    if (!isset($_SESSION['infobot'])) {
        $_SESSION['infobot'] = false;
    }
    $userAgent = server()->string('HTTP_USER_AGENT', '');
    $requestPasskey = request()->string('passkey', 'badrequestpasskey');
    $configPasskey = config()->string('infobot_passkey', 'badconfigpasskey');
    if (strncmp($userAgent, 'infobot', 7) == 0 && $requestPasskey == $configPasskey) {
        $_SESSION['infobot'] = true;
        header('X-InfoBot: true');

        return true;
    }

    if (!Player::isLoggedIn()) {
        header('X-Logged-In: false');
    } elseif (Player::getSessionPlayer()?->isSuper() ?? false) {
        header('X-Admin: true');
        return true;
    }

    header('X-Admin: false');
    return false;
}

function auth(): string|bool
{
    $username = null;
    $apikey = null;
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $username = server()->string('PHP_AUTH_USER');
        $apikey = server()->string('PHP_AUTH_PW');
    }
    if (isset($_SERVER['HTTP_X_USERNAME']) && isset($_SERVER['HTTP_X_APIKEY'])) {
        $username = server()->string('HTTP_X_USERNAME');
        $apikey = server()->string('HTTP_X_APIKEY');
    }

    if (is_null($username) || is_null($apikey)) {
        return 'Auth not provided';
    }

    $player = Player::findByName($username);
    if (is_null($player)) {
        return "Can't find a user called $username";
    }
    if ($player->api_key == hash('sha256', $apikey)) {
        $_SESSION['username'] = $player->name;
    } else {
        return 'Invalid API Key';
    }

    return Player::isLoggedIn();
}

function error(string $msg, mixed $extra = null): never
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

function argStr(string $key, string|false $default = false): string
{
    try {
        return request()->string($key, $default);
    } catch (InvalidArgumentException $e) {
        error("missing argument '$key'");
    }
}

function arg(string $key, mixed $default = null): mixed
{
    if (!isset($_REQUEST[$key])) {
        if ($default !== null) {
            return $default;
        }

        return error("Missing argument '$key'");
    }

    return $_REQUEST[$key];
}


/** @return array<string, mixed> */
function repr_json_event(Event $event): array
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

/** @return array<string, mixed> */
function repr_json_deck(Deck $deck): array
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

/** @return array<string, mixed> */
function repr_json_series(Series $series): array
{
    $json = populate([], $series, ['name', 'active', 'start_day', 'start_time', 'organizers', 'mtgo_room', 'this_season_format', 'this_season_master_link', 'this_season_season', 'discord_guild_id', 'discord_channel_id', 'discord_channel_name', 'discord_guild_name']);
    $mostRecent = $series->mostRecentEvent();
    $json['most_recent_season'] = $mostRecent ? $mostRecent->season : null;
    $json['most_recent_number'] = $mostRecent ? $mostRecent->number : null;
    $json['most_recent_id'] = $mostRecent ? $mostRecent->id : null;

    return $json;
}

/** @return array<string, mixed> */
function repr_json_player(Player $player, int|string|null $client = null): array
{
    $json = populate([], $player, ['name', 'verified', 'discord_id', 'discord_handle', 'mtga_username', 'mtgo_username']);
    $json['display_name'] = $player->gameName($client);

    return $json;
}

//## Actions

/** @return array<string, mixed> */
function add_player_to_event(Event $event, ?string $name, ?string $decklist): array
{
    $result = [];
    $username = session()->string('username', '');
    if ($username && $event->authCheck($username)) {
        $player = null;
        if ($event->addPlayer($name)) {
            $player = new Player($name);
            $result['success'] = true;
            $result['player'] = $player->name;
            $result['verified'] = $player->verified;
            $result['event_running'] = $event->active == 1;
        } else {
            $result['success'] = false;
        }
        if (!empty($decklist) && $player) {
            $decklist = str_replace('|', "\n", $decklist);

            $deck = new Deck(0);
            $deck->playername = $player->name;
            $deck->eventname = $event->name;
            $deck->event_id = $event->id;
            $deck->maindeck_cards = parseCardsWithQuantity($decklist);
            $deck->sideboard_cards = parseCardsWithQuantity('');
            $deck->save();

            $result['deck'] = repr_json_deck($deck);
        }
    } else {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    }

    return $result;
}

/** @return array<string, mixed> */
function delete_player_from_event(Event $event, ?string $name): array
{
    $username = session()->string('username', '');
    if ($username && $event->authCheck($username)) {
        $result = [];
        $result['success'] = $event->removeEntry($name);
        $result['player'] = $name;
    } else {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    }

    return $result;
}

/** @return array<string, mixed> */
function drop_player_from_event(Event $event, ?string $name): array
{
    $username = session()->string('username', '');
    if ($username && $event->authCheck($username)) {
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

/** @return array<string, mixed> */
function create_series(string $newseries, bool $active, string $day): array
{
    $result = [];
    if (!is_admin()) {
        $result['error'] = 'Unauthorized';
        $result['success'] = false;
    } else {
        $series = new Series('');
        $series->name = $newseries;
        $series->active = $active ? 1 : 0;
        $series->start_time = '0:00:00';
        $series->start_day = $day;
        $series->save();

        $result['message'] = "New series $series->name was created!";
        $result['success'] = true;
        $result['series'] = $series;
    }

    return $result;
}

/** @return array<string, mixed> */
function create_event(): array
{
    $name = argStr('name', '');
    $naming = '';
    if ($name == '') {
        $naming = 'auto';
    }

    $event = Event::createEvent(
        argStr('year'),
        argStr('month'),
        argStr('day'),
        argStr('hour'),
        $naming,
        $name,
        argStr('format'),
        argStr('host', ''),
        argStr('cohost', ''),
        argStr('kvalue', ''),
        argStr('series'),
        argStr('season'),
        argStr('number'),
        argStr('threadurl', ''),
        argStr('metaurl', ''),
        argStr('reporturl', ''),
        argStr('prereg_allowed', ''),
        argStr('player_reportable', ''),
        argStr('late_entry_limit', ''),
        argStr('private', ''),
        argStr('mainrounds', ''),
        argStr('mainstruct', ''),
        argStr('finalrounds', ''),
        argStr('finalstruct', ''),
        argStr('client', '1')
    );

    $result = [];
    $result['success'] = true;
    $result['event'] = $event;

    return $result;
}


function create_pairing(Event $event, int $round, ?string $a, ?string $b, ?string $res): void
{
    if (!is_admin()) {
        $username = Player::loginName();
        if (!$username || !$event->authCheck($username)) {
            error('Unauthorized');
        }
    }

    $playerA = new Standings($event->name, $a);
    $playerB = new Standings($event->name, $b);
    $pAWins = $pBWins = null;
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
    }
    if ($res == 'P') {
        $event->addPairing($playerA, $playerB, $round, $res);
    } else {
        $event->addMatch($playerA, $playerB, (string) $round, $res, (string) $pAWins, (string) $pBWins);
    }
}

/** @return list<string> */
function card_catalog(): array
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

function cardname_from_id(string $id): string
{
    $sql = 'SELECT c.name FROM cards c WHERE c.scryfallId = :scryfall_id';
    $name = DB::string($sql, ['scryfall_id' => $id]);
    return $name;
}
