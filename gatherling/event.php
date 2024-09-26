<?php

declare(strict_types=1);

namespace Gatherling;

use Gatherling\Models\Database;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Standings;
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

require_once 'lib.php';
include 'lib_form_helper.php';

function main(): void
{
    if (!Player::isLoggedIn()) {
        linkToLogin('Host Control Panel');

        return;
    }

    $getSeriesName = $_GET['series'] ?? '';
    $season = $_GET['season'] ?? '';
    $requestEventName = $_REQUEST['name'] ?? '';
    $getEventName = $_GET['name'] ?? $_GET['event'] ?? null;
    $postEventName = $_POST['name'] ?? null;
    $action = $_GET['action'] ?? null;
    $eventId = $_GET['event_id'] ?? null;
    $player = $_GET['player'] ?? null;

    if (mode_is('Create New Event')) {
        $event = createNewEvent();
        $page = $event === false ? new AuthFailed() : new EventList($getSeriesName, $season);
    } elseif (mode_is('Create A New Event')) {
        $page = eventFrame(null, true);
    } elseif (mode_is('Create Next Event') || mode_is('Create Next Season')) {
        $newEvent = newEventFromEventName($requestEventName, mode_is('Create Next Season'));
        $page = eventFrame($newEvent, true);
    } elseif (isset($getEventName)) {
        $page = getEvent($getEventName, $action, $eventId, $player);
    } elseif (isset($postEventName)) {
        $page = postEvent($postEventName);
    } else {
        $page = new EventList($getSeriesName, $season);
    }
    $page->send();
}

function mode_is(string $str): bool
{
    $mode = '';
    if (isset($_REQUEST['mode']) and $_REQUEST['mode'] != '') {
        $mode = $_REQUEST['mode'];
    }

    return strcmp($mode, $str) == 0;
}

function createNewEvent(): Event|bool
{
    $series = new Series($_POST['series']);
    if ($series->authCheck(Player::loginName()) && isset($_POST['insert'])) {
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
    if (!$event->authCheck(Player::loginName())) {
        return new AuthFailed();
    }
    if ($action && strcmp($action, 'undrop') == 0) {
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

    if (!$event->authCheck(Player::loginName())) {
        return new AuthFailed();
    }

    if (mode_is('Start Event')) {
        $event->startEvent(true);
    } elseif (mode_is('Start Event (No Deck Check)')) {
        $event->startEvent(false);
    } elseif (mode_is('Recalculate Standings')) {
        $structure = $event->mainstruct;
        $event->recalculateScores($structure);
        Standings::updateStandings($event->name, $event->mainid, 1);
    } elseif (mode_is('End Current League Round')) {
        $event->recalculateScores('League');
        Standings::updateStandings($event->name, $event->mainid, 1);
        $event->pairCurrentRound();
    } elseif (mode_is('Reset Event')) {
        $event->resetEvent();
    } elseif (mode_is('Delete Matches and Re-Pair Round')) {
        $event->repairRound();
    } elseif (mode_is('Reactivate Event')) {
        $event->active = 1;
        $event->finalized = 0;
        $event->save();
    } elseif (mode_is('Assign Medals')) {
        $event->assignMedals();
    } elseif (mode_is('Set Current Round to')) {
        $event->repairRound();
    } elseif (mode_is('Update Registration')) {
        updateReg();
    } elseif (mode_is('Update Match Listing')) {
        updateMatches();
    } elseif (mode_is('Update Medals')) {
        updateMedals();
    } elseif (mode_is('Update Adjustments')) {
        updateAdjustments();
    } elseif (mode_is('Upload Trophy')) {
        if (insertTrophy()) {
            $event->hastrophy = 1;
            $_GET['view'] = 'settings';
        }
    } elseif (mode_is('Update Event Info')) {
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
        $view = $_POST['view'] ?? $_GET['view'] ?? ($event->active ? 'match' : 'reg');
    } else {
        $view = 'edit';
    }

    if (strcmp($view, 'reg') == 0) {
        return new PlayerList($event);
    } elseif (strcmp($view, 'match') == 0) {
        // Prevent warnings in php output.  TODO: make this not needed.
        if (!isset($_POST['newmatchround'])) {
            $_POST['newmatchround'] = '';
        }

        return new MatchList($event, $_POST['newmatchround']);
    } elseif (strcmp($view, 'standings') == 0) {
        return new StandingsList($event, Player::loginName());
    } elseif (strcmp($view, 'medal') == 0) {
        return new MedalList($event);
    } elseif (strcmp($view, 'points_adj') == 0) {
        return new PointsAdjustmentForm($event);
    } elseif (strcmp($view, 'reports') == 0) {
        return new ReportsForm($event);
    }

    return new EventForm($event, $edit);
}

function insertEvent(): Event
{
    if (!isset($_POST['naming'])) {
        $_POST['naming'] = '';
    }

    if (!isset($_POST['prereg_allowed'])) {
        $_POST['prereg_allowed'] = 0;
    }

    if (!isset($_POST['player_reportable'])) {
        $_POST['player_reportable'] = 0;
    }

    if (!isset($_POST['late_entry_limit'])) {
        $_POST['late_entry_limit'] = 0;
    }
    if (!isset($_POST['private'])) {
        $_POST['private'] = 0;
    }

    $event = Event::CreateEvent(
        $_POST['year'],
        $_POST['month'],
        $_POST['day'],
        $_POST['hour'],
        $_POST['naming'],
        $_POST['name'],
        $_POST['format'],
        $_POST['host'],
        $_POST['cohost'],
        $_POST['kvalue'],
        $_POST['series'],
        $_POST['season'],
        $_POST['number'],
        $_POST['threadurl'],
        $_POST['metaurl'],
        $_POST['reporturl'],
        $_POST['prereg_allowed'],
        $_POST['player_reportable'],
        $_POST['late_entry_limit'],
        $_POST['private'],
        $_POST['mainrounds'],
        $_POST['mainstruct'],
        $_POST['finalrounds'],
        $_POST['finalstruct'],
        $_POST['client']
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

    $event = new Event($_POST['name']);
    $event->start = "{$_POST['year']}-{$_POST['month']}-{$_POST['day']} {$_POST['hour']}:00";
    $event->finalized = $_POST['finalized'];
    $event->active = $_POST['active'];
    $event->current_round = $_POST['newmatchround'];
    $event->prereg_allowed = $_POST['prereg_allowed'];
    $event->player_reportable = $_POST['player_reportable'];
    $event->prereg_cap = $_POST['prereg_cap'];
    $event->private_decks = $_POST['private_decks'];
    $event->private_finals = $_POST['private_finals'];
    $event->player_reported_draws = $_POST['player_reported_draws'];
    $event->late_entry_limit = $_POST['late_entry_limit'];

    if ($event->format != $_POST['format']) {
        $event->format = $_POST['format'];
        $event->updateDecksFormat($_POST['format']);
    }

    $event->host = $_POST['host'];
    $event->cohost = $_POST['cohost'];
    $event->kvalue = (int) $_POST['kvalue'];
    $event->series = $_POST['series'];
    $event->season = $_POST['season'];
    $event->number = $_POST['number'];
    $event->threadurl = $_POST['threadurl'];
    $event->metaurl = $_POST['metaurl'];
    $event->reporturl = $_POST['reporturl'];

    if ($_POST['mainrounds'] == '') {
        $_POST['mainrounds'] = 3;
    }
    if ($_POST['mainstruct'] == '') {
        $_POST['mainstruct'] = 'Swiss';
    }
    if ($_POST['mainrounds'] >= $event->current_round) {
        $event->mainrounds = $_POST['mainrounds'];
        $event->mainstruct = $_POST['mainstruct'];
    }

    if ($_POST['finalrounds'] == '') {
        $_POST['finalrounds'] = 0;
    }
    if ($_POST['finalstruct'] == '') {
        $_POST['finalstruct'] = 'Single Elimination';
    }
    $event->finalrounds = $_POST['finalrounds'];
    $event->finalstruct = $_POST['finalstruct'];
    $event->private = $_POST['private'];
    $event->client = (int) $_POST['client'];

    $event->save();

    return $event;
}

function insertTrophy(): bool
{
    if ($_FILES['trophy']['size'] <= 0) {
        return false;
    }
    $file = $_FILES['trophy'];
    $event = $_POST['name'];

    $tmp = $file['tmp_name'];
    $size = $file['size'];
    $type = $file['type'];

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
    $event = new Event($_POST['name']);

    $dropped = [];
    if (isset($_POST['delentries'])) {
        foreach ($_POST['delentries'] as $playername) {
            $event->removeEntry($playername);
            $dropped[] = $playername;
        }
    }
    if (isset($_POST['dropplayer'])) {
        foreach ($_POST['dropplayer'] as $playername) {
            $event->dropPlayer($playername);
        }
    }
    if (isset($_POST['newentry'])) {
        $event->addPlayer($_POST['newentry']);
    }

    if (isset($_POST['initial_byes'])) {
        foreach ($_POST['initial_byes'] as $byedata) {
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
        foreach ($_POST['initial_seed'] as $seeddata) {
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
    $event = new Event($_POST['name']);
    if (isset($_POST['matchdelete'])) {
        foreach ($_POST['matchdelete'] as $matchid) {
            Matchup::destroy($matchid);
        }
    }

    if (isset($_POST['dropplayer'])) {
        foreach ($_POST['dropplayer'] as $playername) {
            $event->dropPlayer($playername);
        }
    }

    if (isset($_POST['hostupdatesmatches'])) {
        for ($ndx = 0; $ndx < count($_POST['hostupdatesmatches']); $ndx++) {
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
                $matchid = $_POST['hostupdatesmatches'][$ndx];
                Matchup::saveReport($resultForA, $matchid, 'a');
                Matchup::saveReport($resultForB, $matchid, 'b');
            }
        }
    }

    if (isset($_POST['newmatchplayerA'])) {
        $pA = $_POST['newmatchplayerA'];
    } else {
        $pA = '';
    }
    if (isset($_POST['newmatchplayerB'])) {
        $pB = $_POST['newmatchplayerB'];
    } else {
        $pB = '';
    }
    if (isset($_POST['newmatchresult'])) {
        $res = $_POST['newmatchresult'];
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
        }
    } else {
        $res = '';
    }
    if (isset($_POST['newmatchround'])) {
        $rnd = $_POST['newmatchround'];
    } else {
        $rnd = '';
    }

    if (
        strcmp($pA, '') != 0 && strcmp($pB, '') != 0
        && strcmp($res, '') != 0 && strcmp($rnd, '') != 0
    ) {
        $playerA = new Standings($event->name, $pA);
        $playerB = new Standings($event->name, $pB);
        if ($res == 'P') {
            $event->addPairing($playerA, $playerB, $rnd, $res);
        } else {
            $event->addMatch($playerA, $playerB, $rnd, $res, (string) $pAWins, (string) $pBWins);
        }
    }

    if (isset($_POST['newbyeplayer']) && (strcmp($_POST['newbyeplayer'], '') != 0)) {
        $playerBye = new Standings($event->name, $_POST['newbyeplayer']);
        $event->addMatch($playerBye, $playerBye, $rnd, 'BYE');
    }
}

function updateMedals(): void
{
    $event = new Event($_POST['name']);

    $winner = $_POST['newmatchplayer1'];
    $second = $_POST['newmatchplayer2'];
    $t4 = [$_POST['newmatchplayer3'], $_POST['newmatchplayer4']];
    $t8 = [$_POST['newmatchplayer5'], $_POST['newmatchplayer6'], $_POST['newmatchplayer7'], $_POST['newmatchplayer8']];

    $event->setFinalists($winner, $second, $t4, $t8);
}

function updateAdjustments(): void
{
    $event = new Event($_POST['name']);

    $adjustments = $_POST['adjustments'];
    $reasons = $_POST['reasons'];

    foreach ($adjustments as $name => $points) {
        if ($points != '') {
            $event->setSeasonPointAdjustment($name, $points, $reasons[$name]);
        }
    }
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
