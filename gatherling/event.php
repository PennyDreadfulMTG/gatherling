<?php

declare(strict_types=1);

namespace Gatherling;

use Gatherling\Exceptions\ValidationException;
use Gatherling\Models\Database;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Standings;
use Gatherling\Views\LoginRedirect;
use Gatherling\Views\Pages\AuthFailed;
use Gatherling\Views\Pages\EventForm;
use Gatherling\Views\Pages\EventFrame;
use Gatherling\Views\Pages\EventList;
use Gatherling\Views\Pages\MatchList;
use Gatherling\Views\Pages\MedalList;
use Gatherling\Views\Pages\Page;
use Gatherling\Views\Pages\PlayerList;
use Gatherling\Views\Pages\PointsAdjustmentForm;
use Gatherling\Views\Pages\ReportsForm;
use Gatherling\Views\Pages\StandingsList;
use InvalidArgumentException;

use function Gatherling\Helpers\files;
use function Gatherling\Helpers\get;
use function Gatherling\Helpers\post;
use function Gatherling\Helpers\request;
use function Gatherling\Helpers\server;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\preg_replace;
use function Safe\strtotime;

require_once 'lib.php';

function main(): never
{
    if (!Player::isLoggedIn()) {
        (new LoginRedirect())->send();
    }

    $getSeriesName = get()->string('series', '');
    $season = get()->optionalInt('season');
    $requestEventName = request()->string('name', '');
    $getEventName = get()->optionalString('name') ?? get()->optionalString('event');
    $postEventName = post()->optionalString('name');
    $action = get()->optionalString('action');
    $eventId = get()->optionalString('event_id');
    $player = get()->optionalString('player');
    $format = get()->string('format', '');

    $mode = request()->string('mode', '');
    if ($mode === 'Create New Event') {
        $event = createNewEvent(post()->optionalString('series'));
        $page = $event === false ? new AuthFailed() : new EventList($getSeriesName, $format, $season);
    } elseif ($mode === 'Create A New Event') {
        $page = eventFrame(null, true);
    } elseif ($mode === 'Create Next Event' || $mode === 'Create Next Season') {
        $newEvent = newEventFromEventName($requestEventName, $mode === 'Create Next Season');
        $page = eventFrame($newEvent, true);
    } elseif (isset($getEventName)) {
        $page = getEvent($getEventName, $action, $eventId, $player);
    } elseif (isset($postEventName)) {
        $page = postEvent($postEventName);
    } else {
        $page = new EventList($getSeriesName, $format, $season);
    }
    $page->send();
}

function createNewEvent(?string $seriesName): Event|false
{
    $series = new Series($seriesName);
    $playerName = Player::loginName();
    if ($playerName !== false && $series->authCheck($playerName) && isset($_POST['insert'])) {
        return insertEvent();
    }
    return false;
}

function newEventFromEventName(string $eventName, bool $newSeason = false): Event
{
    try {
        $oldEvent = new Event($eventName);
    } catch (\Exception $exc) {
        if ($exc->getMessage() == "Event $eventName not found in DB") {
            $seriesName = preg_replace('/ 1.00$/', '', $eventName);
            $oldEvent = new Event('');
            $oldEvent->name = $eventName;
            $oldEvent->season = $newSeason ? 1 : 0;
            $oldEvent->number = 0;
            $oldEvent->series = $seriesName;
        } else {
            throw $exc;
        }
    }

    $newEvent = new Event('');
    $newEvent->season = $oldEvent->season + ($newSeason ? 1 : 0);
    $newEvent->number = $newSeason ? 1 : $oldEvent->number + 1;
    $newEvent->start = date('Y-m-d H:i:00', strtotime($oldEvent->start) + (86400 * 7));
    $newEvent->finalized = 0;

    $copiableFields = ['format', 'kvalue', 'prereg_allowed', 'threadurl', 'reporturl', 'metaurl',
        'series', 'host', 'cohost', 'mainrounds', 'mainstruct', 'finalrounds', 'finalstruct',
        'private_decks', 'private_finals', 'player_reportable', 'player_reported_draws', 'prereg_cap',
        'late_entry_limit', 'private', 'player_editdecks'];

    foreach ($copiableFields as $field) {
        $newEvent->$field = $oldEvent->$field;
    }

    $newEvent->name = sprintf('%s %d.%02d', $newEvent->series, $newEvent->season, $newEvent->number);

    return $newEvent;
}

// This is a helper function that handles all requests that have a name={eventName} in the querystring
// In some future happier time maybe it will be teased apart usefully.
function getEvent(string $eventName, ?string $action, ?string $eventId, ?string $player): Page
{
    $event = new Event($eventName);
    $playerName = Player::loginName();
    if ($playerName !== false && !$event->authCheck($playerName)) {
        return new AuthFailed();
    }
    if ($action === 'undrop') {
        $entry = new Entry((int) $eventId, $player);
        if ($entry->deck && $entry->deck->isValid()) {
            $event->undropPlayer($player);
        }
    }

    return eventFrame($event);
}

// This is a helper function that handles all the (many) requests that have a name={eventName} in the request body
// In some future happier time maybe it will be teased apart usefully.
function postEvent(string $eventName): Page
{
    $event = new Event($eventName);

    $playerName = Player::loginName();
    if ($playerName !== false && !$event->authCheck($playerName)) {
        return new AuthFailed();
    }

    $mode = request()->string('mode', '');
    if ($mode === 'Start Event') {
        $event->startEvent(true);
    } elseif ($mode === 'Start Event (No Deck Check)') {
        $event->startEvent(false);
    } elseif ($mode === 'Recalculate Standings') {
        $structure = $event->mainstruct;
        $event->recalculateScores($structure);
        Standings::updateStandings($event->name, $event->mainid, 1);
    } elseif ($mode === 'End Current League Round') {
        $event->recalculateScores('League');
        Standings::updateStandings($event->name, $event->mainid, 1);
        $event->pairCurrentRound();
    } elseif ($mode === 'Reset Event') {
        $event->resetEvent();
    } elseif ($mode === 'Delete Matches and Re-Pair Round') {
        $event->repairRound();
    } elseif ($mode === 'Reactivate Event') {
        $event->active = 1;
        $event->finalized = 0;
        $event->save();
    } elseif ($mode === 'Assign Medals') {
        $event->assignMedals();
    } elseif ($mode === 'Set Current Round to') {
        $event->repairRound();
    } elseif ($mode === 'Update Registration') {
        updateReg();
    } elseif ($mode === 'Update Match Listing') {
        updateMatches();
    } elseif ($mode === 'Update Medals') {
        updateMedals();
    } elseif ($mode === 'Update Adjustments') {
        updateAdjustments();
    } elseif ($mode === 'Upload Trophy') {
        if (insertTrophy()) {
            $event->hastrophy = 1;
            $_GET['view'] = 'settings';
        }
    } elseif ($mode === 'Update Event Info') {
        $event = updateEvent();
        $_GET['view'] = 'settings';
    }

    return eventFrame($event);
}

function eventFrame(Event $event = null, bool $forceNew = false): EventFrame
{
    $edit = !$forceNew && $event !== null && $event->name !== '';
    if (is_null($event)) {
        $event = new Event('');
    }
    if ($edit) {
        $view = post()->optionalString('view') ?? get()->optionalString('view') ?? ($event->active ? 'match' : 'reg');
    } else {
        $view = 'edit';
    }

    if ($view === 'reg') {
        return new PlayerList($event);
    } elseif ($view === 'match') {
        // Prevent warnings in php output.  TODO: make this not needed.
        if (!isset($_POST['newmatchround'])) {
            $_POST['newmatchround'] = '';
        }
        return new MatchList($event, post()->optionalString('newmatchround'));
    } elseif ($view === 'standings') {
        return new StandingsList($event, Player::loginName() ?: null);
    } elseif ($view === 'medal') {
        return new MedalList($event);
    } elseif ($view === 'points_adj') {
        return new PointsAdjustmentForm($event);
    } elseif ($view === 'reports') {
        return new ReportsForm($event);
    }

    return new EventForm($event, $edit);
}

function insertEvent(): Event
{
    $event = Event::createEvent(
        post()->string('year'),
        post()->string('month'),
        post()->string('day'),
        post()->string('hour'),
        post()->string('naming', ''),
        post()->string('name'),
        post()->string('format'),
        post()->string('host'),
        post()->string('cohost'),
        post()->string('kvalue'),
        post()->string('series'),
        post()->string('season'),
        post()->string('number'),
        post()->string('threadurl'),
        post()->string('metaurl'),
        post()->string('reporturl'),
        post()->string('prereg_allowed', '0'),
        post()->string('player_reportable', '0'),
        post()->string('late_entry_limit', '0'),
        post()->string('private', '0'),
        post()->int('mainrounds'),
        post()->string('mainstruct'),
        post()->int('finalrounds'),
        post()->string('finalstruct'),
        post()->string('client')
    );

    return $event;
}

function updateEvent(): Event
{
    if (!isset($_POST['finalized'])) {
        $_POST['finalized'] = 0;
    }
    if (!isset($_POST['active'])) {
        $_POST['active'] = 0;
    }
    if (!isset($_POST['prereg_allowed'])) {
        $_POST['prereg_allowed'] = 0;
    }
    if (!isset($_POST['player_reportable'])) {
        $_POST['player_reportable'] = 0;
    }
    if (!isset($_POST['prereg_cap'])) {
        $_POST['prereg_cap'] = 0;
    }
    if (!isset($_POST['private_decks'])) {
        $_POST['private_decks'] = 0;
    }
    if (!isset($_POST['private_finals'])) {
        $_POST['private_finals'] = 0;
    }
    if (!isset($_POST['player_reported_draws'])) {
        $_POST['player_reported_draws'] = 0;
    }
    if (!isset($_POST['late_entry_limit'])) {
        $_POST['late_entry_limit'] = 0;
    }
    if (!isset($_POST['private'])) {
        $_POST['private'] = 0;
    }

    $event = new Event(post()->string('name'));
    $event->start = "{$_POST['year']}-{$_POST['month']}-{$_POST['day']} {$_POST['hour']}:00";
    $event->finalized = post()->int('finalized');
    $event->active = post()->int('active');
    $event->current_round = post()->int('newmatchround');
    $event->prereg_allowed = post()->int('prereg_allowed');
    $event->player_reportable = post()->int('player_reportable');
    $event->prereg_cap = post()->int('prereg_cap');
    $event->private_decks = post()->int('private_decks');
    $event->private_finals = post()->int('private_finals');
    $event->player_reported_draws = post()->int('player_reported_draws');
    $event->late_entry_limit = post()->int('late_entry_limit');

    if ($event->format != post()->string('format')) {
        $event->format = post()->string('format');
        $event->updateDecksFormat(post()->string('format'));
    }

    $event->host = post()->string('host');
    $event->cohost = post()->string('cohost');
    $event->kvalue = post()->int('kvalue');
    $event->series = post()->string('series');
    $event->season = post()->int('season');
    $event->number = post()->int('number');
    $event->threadurl = post()->string('threadurl');
    $event->metaurl = post()->string('metaurl');
    $event->reporturl = post()->string('reporturl');

    if (post()->string('mainrounds') == '') {
        post()->string('mainrounds', '3');
    }
    if (post()->string('mainstruct') == '') {
        $_POST['mainstruct'] = 'Swiss';
    }
    if (post()->int('mainrounds') >= $event->current_round) {
        $event->mainrounds = post()->int('mainrounds');
        $event->mainstruct = post()->string('mainstruct');
    }

    if (post()->string('finalrounds') == '') {
        $_POST['finalrounds'] = 0;
    }
    if (post()->string('finalstruct') == '') {
        $_POST['finalstruct'] = 'Single Elimination';
    }
    $event->finalrounds = post()->int('finalrounds');
    $event->finalstruct = post()->string('finalstruct');
    $event->private = post()->int('private');
    $event->client = post()->int('client');

    $event->save();

    return $event;
}

function insertTrophy(): bool
{
    $file = files()->optionalFile('trophy');
    if ($file === null || $file->size <= 0) {
        return false;
    }
    $event = $_POST['name'];

    $tmp = $file->tmp_name;
    $size = $file->size;
    $type = $file->type;
    $f = fopen($tmp, 'rb');

    $db = Database::getPDOConnection();
    $stmt = $db->prepare('DELETE FROM trophies WHERE event = ?');
    $stmt->bindParam(1, $event, \PDO::PARAM_STR);
    if (!$stmt->execute()) {
        throw new \Exception($stmt->errorInfo()[2], 1);
    }
    $stmt = $db->prepare('INSERT INTO trophies(event, size, type, image) VALUES(?, ?, ?, ?)');
    $stmt->bindParam(1, $event, \PDO::PARAM_STR);
    $stmt->bindParam(2, $size, \PDO::PARAM_INT);
    $stmt->bindParam(3, $type, \PDO::PARAM_STR);
    $stmt->bindParam(4, $f, \PDO::PARAM_LOB);
    if (!$stmt->execute()) {
        throw new \Exception($stmt->errorInfo()[2], 1);
    }
    fclose($f);

    return true;
}

function updateReg(): void
{
    $event = new Event(post()->string('name'));

    $dropped = [];
    if (isset($_POST['delentries'])) {
        foreach (post()->listString('delentries') as $playername) {
            $event->removeEntry($playername);
            $dropped[] = $playername;
        }
    }
    if (isset($_POST['dropplayer'])) {
        foreach (post()->listString('dropplayer') as $playername) {
            $event->dropPlayer($playername);
        }
    }
    if (isset($_POST['newentry'])) {
        $event->addPlayer(post()->string('newentry'));
    }

    if (isset($_POST['initial_byes'])) {
        foreach (post()->listString('initial_byes') as $byedata) {
            if (!empty(trim($byedata))) {
                $array_data = explode(' ', $byedata);
                $bye_qty = intval($array_data[count($array_data) - 1]);
                unset($array_data[count($array_data) - 1]);
                $playername = implode(' ', $array_data);
                if (in_array($playername, $dropped)) {
                    continue;
                }
                $entry = new Entry($event->id, $playername);
                $entry->setInitialByes($bye_qty);
            }
        }
    }

    if (isset($_POST['initial_seed'])) {
        foreach (post()->listString('initial_seed') as $seeddata) {
            if (!empty(trim($seeddata))) {
                $array_data = explode(' ', $seeddata);
                $seed = intval($array_data[count($array_data) - 1]);
                unset($array_data[count($array_data) - 1]);
                $playername = implode(' ', $array_data);
                if (in_array($playername, $dropped)) {
                    continue;
                }
                $entry = new Entry($event->id, $playername);
                $entry->setInitialSeed($seed);
            }
        }
    }
}

function updateMatches(): void
{
    $event = new Event(post()->string('name'));
    $deleteMatchIds = post()->listInt('matchdelete');
    foreach ($deleteMatchIds as $matchId) {
        Matchup::destroy($matchId);
    }

    if (isset($_POST['dropplayer'])) {
        foreach (post()->listString('dropplayer') as $playername) {
            $event->dropPlayer($playername);
        }
    }

    if (isset($_POST['hostupdatesmatches'])) {
        for ($ndx = 0; $ndx < count(post()->listInt('hostupdatesmatches')); $ndx++) {
            $result = $_POST['matchresult'][$ndx];
            $resultForA = 'notset';
            $resultForB = 'notset';

            if ($result == '2-0') {
                $resultForA = 'W20';
                $resultForB = 'L20';
            } elseif ($result == '2-1') {
                $resultForA = 'W21';
                $resultForB = 'L21';
            } elseif ($result == '1-2') {
                $resultForA = 'L21';
                $resultForB = 'W21';
            } elseif ($result == '0-2') {
                $resultForA = 'L20';
                $resultForB = 'W20';
            } elseif ($result == 'D') {
                $resultForA = 'D';
                $resultForB = 'D';
            }

            if ((strcasecmp($resultForA, 'notset') != 0) && (strcasecmp($resultForB, 'notset') != 0)) {
                $matchid = (int) $_POST['hostupdatesmatches'][$ndx];
                Matchup::saveReport($resultForA, $matchid, 'a');
                Matchup::saveReport($resultForB, $matchid, 'b');
            }
        }
    }

    $pA = post()->string('newmatchplayerA', '');
    $pB = post()->string('newmatchplayerB', '');
    $res = post()->string('newmatchresult', '');
    if ($res == '2-0') {
        $pAWins = 2;
        $pBWins = 0;
        $res = 'A';
    } elseif ($res == '2-1') {
        $pAWins = 2;
        $pBWins = 1;
        $res = 'A';
    } elseif ($res == '1-2') {
        $pAWins = 1;
        $pBWins = 2;
        $res = 'B';
    } elseif ($res == '0-2') {
        $pAWins = 0;
        $pBWins = 2;
        $res = 'B';
    } elseif ($res == 'D') {
        $pAWins = 1;
        $pBWins = 1;
        $res = 'D';
    } elseif ($res != 'P') {
        throw new InvalidArgumentException('Invalid result for match: $res');
    }
    $rnd = post()->int('newmatchround');

    if ($pA !== '' && $pB !== '') {
        if ($rnd === 0) {
            throw new InvalidArgumentException('Cannot add match to round 0');
        }
        $playerA = new Standings($event->name, $pA);
        $playerB = new Standings($event->name, $pB);
        if ($res == 'P') {
            $event->addPairing($playerA, $playerB, $rnd, $res);
        } else {
            $event->addMatch($playerA, $playerB, $rnd, $res, $pAWins, $pBWins);
        }
    }

    if (post()->string('newbyeplayer', '') !== '') {
        $playerBye = new Standings($event->name, post()->string('newbyeplayer'));
        $event->addMatch($playerBye, $playerBye, $rnd, 'BYE');
    }
}

function updateMedals(): void
{
    $event = new Event(post()->string('name'));

    $winner = post()->string('newmatchplayer1');
    $second = post()->optionalString('newmatchplayer2');
    $t4 = [post()->optionalString('newmatchplayer3'), post()->optionalString('newmatchplayer4')];
    $t8 = [post()->optionalString('newmatchplayer5'), post()->optionalString('newmatchplayer6'), post()->optionalString('newmatchplayer7'), post()->optionalString('newmatchplayer8')];

    $event->setFinalists($winner, $second, $t4, $t8);
}

function updateAdjustments(): void
{
    $event = new Event(post()->string('name'));

    $adjustments = post()->dictInt('adjustments');
    $reasons = post()->dictString('reasons');

    foreach ($adjustments as $name => $points) {
        if ($points != '') {
            $event->setSeasonPointAdjustment($name, $points, $reasons[$name]);
        }
    }
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
