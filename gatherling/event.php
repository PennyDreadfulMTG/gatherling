<?php

use Gatherling\Database;
use Gatherling\Entry;
use Gatherling\Event;
use Gatherling\Format;
use Gatherling\Matchup;
use Gatherling\Player;
use Gatherling\Series;
use Gatherling\Standings;

include 'lib.php';
include 'lib_form_helper.php';

if (!Player::isLoggedIn()) {
    linkToLogin('Host Control Panel');
}

print_header('Event Host Control Panel', true);
?>
<div class="grid_10 suffix_1 prefix_1">
    <div id="gatherling_main" class="box">
        <div class="uppertitle">Host Control Panel</div>
            <?php content(); ?>
        <div class="clear"></div>
    </div>
</div>
<?php
print_footer();

function mode_is(string $str): bool
{
    $mode = '';
    if (isset($_REQUEST['mode']) and $_REQUEST['mode'] != '') {
        $mode = $_REQUEST['mode'];
    }
    return strcmp($mode, $str) == 0;
}

function content(): void
{
    $getSeriesName = $_GET['series'] ?? '';
    $season = $_GET['season'] ?? '';
    $requestEventName = $_REQUEST['name'] ?? '';
    $getEventName = $_GET['name'] ?? $_GET['event'] ?? null;
    $postEventName = $_POST['name'] ?? null;
    $action = $_GET['action'] ?? null;
    $eventId = $_GET['event_id'] ?? null;
    $player = $_GET['player'] ?? null;

    if (mode_is('Create New Event')) {
        echo createNewEvent($getSeriesName, $season);
    } elseif (mode_is('Create A New Event')) {
        echo eventFrame(null, true);
    } elseif (mode_is('Create Next Event')) {
        $newEvent = newEventFromEventName($requestEventName);
        echo eventFrame($newEvent, true);
    } elseif (mode_is('Create Next Season')) {
        $newEvent = newEventFromEventName($requestEventName, true);
        echo eventFrame($newEvent, true);
    } elseif (isset($getEventName)) {
        getEvent($getEventName, $action, $eventId, $player);
    } elseif (isset($postEventName)) {
        postEvent($postEventName);
    } else {
        echo eventList($getSeriesName, $season);
    }
}

function createNewEvent(string $seriesName, string $season): string
{
    $series = new Series($_POST['series']);
    if (($series->authCheck(Player::loginName())) && isset($_POST['insert'])) {
        insertEvent();
        return eventList($seriesName, $season);
    }
    return authFailed();
}

function newEventFromEventName(string $eventName, bool $newSeason = false): Event
{
    try {
        $oldEvent = new Event($eventName);
    } catch (Exception $exc) {
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
function getEvent(string $eventName, ?string $action, ?string $eventId, ?string $player): void
{
    $event = new Event($eventName);
    if (!$event->authCheck(Player::loginName())) {
        echo authFailed();
        return;
    }
    if ($action && strcmp($action, 'undrop') == 0) {
        $entry = new Entry($eventId, $player);
        if ($entry->deck && $entry->deck->isValid()) {
            $event->undropPlayer($player);
        }
    }
    echo eventFrame($event);
}

// This is a helper function that handles all the (many) requests that have a name={eventName} in the request body
// In some future happier time maybe it will be teased apart usefully.
function postEvent(string $eventName): void
{
    $event = new Event($eventName);

    if (!$event->authCheck(Player::loginName())) {
        echo authFailed();
        return;
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
    echo eventFrame($event);
}

function eventList(string $seriesName, string $season): string
{
    $player = Player::getSessionPlayer();
    $playerSeries = $player->organizersSeries();

    $result = queryEvents($player, $playerSeries, $seriesName);
    $seriesShown = $results = $finalizedResults = [];

    while ($event = $result->fetch_assoc()) {
        if ($event['finalized'] == 1) {
            $finalizedResults[] = $event;
        } else {
            $results[] = $event;
        }
        $seriesShown[] = $event['series'];
    }
    $results = array_merge($results, $finalizedResults);

    $hasMore = $result->num_rows == 100;
    $result->close();

    if ($seriesName) {
        $seriesShown = $playerSeries;
    } else {
        $seriesShown = array_unique($seriesShown);
    }

    if (!isset($_GET['format'])) {
        $_GET['format'] = '';
    }

    $kvalueMap = [
        0 => 'none',
        8 => 'Casual',
        16 => 'Regular',
        24 => 'Large',
        32 => 'Championship'
    ];

    foreach ($results as &$event) {
        $event['kvalueDisplay'] = $kvalueMap[$event['kvalue']] ?? '';
        $event['url'] = 'event.php?name=' . rawurlencode($event['name']);
    }

    return render_name('partials/eventList', [
        'formatDropMenu' => formatDropMenuArgs($_GET['format'], true),
        'seriesDropMenu' => Series::dropMenuArgs($seriesName, true, $seriesShown),
        'seasonDropMenu' => seasonDropMenuArgs($season, true),
        'hasPlayerSeries' => count($playerSeries) > 0,
        'results' => $results,
        'hasMore' => $hasMore,
    ]);
}

function queryEvents(Player $player, array $playerSeries, string $seriesName): mysqli_result|bool
{
    $db = Database::getConnection();
    $seriesEscaped = [];
    foreach ($playerSeries as $oneSeries) {
        $seriesEscaped[] = $db->escape_string($oneSeries);
    }
    $seriesString = '"' . implode('","', $seriesEscaped) . '"';

    $query = "SELECT e.name AS name, e.format AS format,
        COUNT(DISTINCT n.player) AS players, e.host AS host, e.start AS start,
        e.finalized, e.cohost, e.series, e.kvalue
        FROM events e
        LEFT OUTER JOIN entries AS n ON n.event_id = e.id
        WHERE (e.host = \"{$db->escape_string($player->name)}\"
            OR e.cohost = \"{$db->escape_string($player->name)}\"
            OR e.series IN (" . $seriesString . '))';
    if (isset($_GET['format']) && strcmp($_GET['format'], '') != 0) {
        $query = $query . " AND e.format=\"{$db->escape_string($_GET['format'])}\" ";
    }
    if (strcmp($seriesName, '') != 0) {
        $query = $query . " AND e.series=\"{$db->escape_string($seriesName)}\" ";
    }
    if (isset($_GET['season']) && strcmp($_GET['season'], '') != 0) {
        $query = $query . " AND e.season=\"{$db->escape_string($_GET['season'])}\" ";
    }
    $query = $query . ' GROUP BY e.name ORDER BY e.start DESC LIMIT 100';
    return $db->query($query);
}

function eventFrame(Event $event = null, bool $forceNew = false): string
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
        $contentSafe = playerList($event);
    } elseif (strcmp($view, 'match') == 0) {
        // Prevent warnings in php output.  TODO: make this not needed.
        if (!isset($_POST['newmatchround'])) {
            $_POST['newmatchround'] = '';
        }
        $contentSafe = matchList($event, $_POST['newmatchround']);
    } elseif (strcmp($view, 'standings') == 0) {
        $contentSafe = standingsList($event);
    } elseif (strcmp($view, 'medal') == 0) {
        $contentSafe = medalList($event);
    } elseif (strcmp($view, 'points_adj') == 0) {
        $contentSafe = pointsAdjustmentForm($event);
    } elseif (strcmp($view, 'reports') == 0) {
        $contentSafe = reportsForm($event);
    } else {
        $contentSafe = render_name('partials/editEventForm', editEventFormArgs($event, $edit));
    }

    return render_name('partials/eventForm', [
        'controlPanelLinks' => controlPanelLinks($event->name),
        'contentSafe' => $contentSafe,
    ]);
}

function controlPanelLinks(string $eventName): array
{
    $views = [
        'settings' => 'Event Settings',
        'reg' => 'Registration',
        'match' => 'Match Listing',
        'standings' => 'Standings',
        'medal' => 'Medals',
        'points_adj' => 'Season Points Adj.',
        'reports' => 'Reports',
    ];
    $links = [];
    foreach ($views as $view => $text) {
        $links[] = [
            'href' => 'event.php?name=' . rawurlencode($eventName) . '&view=' . $view,
            'text' => $text,
        ];
    }
    return $links;
}

function editEventFormArgs(Event $event, bool $edit): array
{
    if ($event->start != null) {
        $date = $event->start;
        preg_match('/([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+):.*/', $date, $datearr);
        $year = $datearr[1];
        $month = $datearr[2];
        $day = $datearr[3];
        $hour = $datearr[4];
        $minutes = $datearr[5];
    } else {
        $year = date('Y', time());
        $month = date('n', time());
        $day = date('j', time());
        $hour = date('H', time());
        $minutes = date('i', time());
    }

    $navLinks = [];
    $prevEvent = $event->findPrev();
    if ($prevEvent) {
        $navLinks[] = $prevEvent->makeLinkArgs('« Previous');
    }
    $nextEvent = $event->findNext();
    if ($nextEvent) {
        $navLinks[] = $nextEvent->makeLinkArgs('Next »');
    }
    $yearDropMenu = numDropMenuArgs('year', '- Year -', (int)date('Y') + 1, $year, 2011);
    $monthDropMenu = monthDropMenuArgs($month);
    $dayDropMenu = numDropMenuArgs('day', '- Day- ', 31, $day, 1);
    $timeDropMenu = timeDropMenuArgs($hour, $minutes);

    $seriesList = Player::getSessionPlayer()->organizersSeries();
    if ($event->series) {
        $seriesList[] = $event->series;
    }
    $seriesList = array_unique($seriesList);
    $seriesDropMenu = Series::dropMenuArgs($event->series, false, $seriesList);

    $seasonDropMenu = seasonDropMenuArgs($event->season);
    $numberDropMenu = numDropMenuArgs('number', '- Event Number -', Event::largestEventNum() + 5, $event->number, 0, 'Custom');
    $formatDropMenu = formatDropMenuArgs($event->format);

    if (is_null($event->kvalue)) {
        $event->kvalue = 16;
    }
    $kValueDropMenu = kValueSelectInput($event->kvalue);
    $hostField = stringFieldArgs('host', $event-> host, 20);
    $cohostField = stringFieldArgs('cohost', $event-> host, 20);
    $eventThreadUrlField = textInputArgs('Event Thread URL', 'threadurl', $event->threadurl, 60);
    $metagameUrlField = textInputArgs('Metagame URL', 'metaurl', $event->metaurl, 60);
    $reportUrlField = textInputArgs('Report URL', 'reporturl', $event->reporturl, 60);
    $mainRoundsNumDropMenu = numDropMenuArgs('mainrounds', '- No. of Rounds -', 10, $event->mainrounds, 1);
    $mainRoundsStructDropMenu = structDropMenuArgs('mainstruct', $event->mainstruct);
    $finalRoundsNumDropMenu = numDropMenuArgs('finalrounds', '- No. of Rounds -', 10, $event->finalrounds, 0);
    $finalRoundsStructDropMenu = structDropMenuArgs('finalstruct', $event->finalstruct);
    $preregistrationAllowedCheckbox = checkboxInputArgs('Allow Pre-Registration', 'prereg_allowed', $event->prereg_allowed, null, true);
    $lateEntryLimitField = textInputArgs('Late Entry Limit', 'late_entry_limit', $event->late_entry_limit, 4, 'The event host may still add players after this round.');
    $playerReportedResultsCheckbox = checkboxInputArgs('Allow Players to Report Results', 'player_reportable', $event->player_reportable);
    $registrationCapField = textInputArgs('Player initiatied registration cap', 'prereg_cap', $event->prereg_cap, 4, 'The event host may still add players beyond this limit. 0 is disabled.', null);
    $deckPrivacyCheckbox = checkboxInputArgs('Deck List Privacy', 'private_decks', $event->private_decks);
    $finalsListPrivacyCheckbox = checkboxInputArgs('Finals List Privacy', 'private_finals', $event->private_finals);
    $playerReportedDrawsCheckbox = checkboxInputArgs('Allow Player Reported Draws', 'player_reported_draws', $event->player_reported_draws, 'This allows players to report a draw result for matches.');
    $privateEventCheckbox = checkboxInputArgs('Private Event', 'private', $event->private, 'This event is invisible to non-participants');
    $clientDropMenu = clientDropMenuArgs('client', $event->client);

    $finalizeEventCheckbox = $eventActiveCheckbox = $currentRoundDropMenu = $trophyField = null;
    $showCreateNextEvent = $showCreateNextSeason = false;
    if ($edit) {
        $finalizeEventCheckbox = checkboxInputArgs('Finalize Event', 'finalized', $event->finalized);
        $eventActiveCheckbox = checkboxInputArgs('Event Active', 'active', $event->active);
        $currentRoundDropMenu = roundDropMenuArgs($event, $event->current_round);
        $trophyField = trophyFieldArgs($event);
        $nextEventName = sprintf('%s %d.%02d', $event->series, $event->season, $event->number + 1);
        $nextSeasonName = sprintf('%s %d.%02d', $event->series, $event->season + 1, 1);
        $showCreateNextEvent = !Event::exists($nextEventName);
        $showCreateNextSeason = !Event::exists($nextSeasonName);
    }

    return [
        'currentlyEditing' => $edit,
        'event' => getObjectVarsCamelCase($event),
        'navLinks' => $navLinks,
        'yearDropMenu' => $yearDropMenu,
        'monthDropMenu' => $monthDropMenu,
        'dayDropMenu' => $dayDropMenu,
        'timeDropMenu' => $timeDropMenu,
        'seriesDropMenu' => $seriesDropMenu,
        'seasonDropMenu' => $seasonDropMenu,
        'numberDropMenu' => $numberDropMenu,
        'formatDropMenu' => $formatDropMenu,
        'kValueDropMenu' => $kValueDropMenu,
        'hostField' => $hostField,
        'cohostField' => $cohostField,
        'eventThreadUrlField' => $eventThreadUrlField,
        'metagameUrlField' => $metagameUrlField,
        'reportUrlField' => $reportUrlField,
        'mainRoundsNumDropMenu' => $mainRoundsNumDropMenu,
        'mainRoundsStructDropMenu' => $mainRoundsStructDropMenu,
        'finalRoundsNumDropMenu' => $finalRoundsNumDropMenu,
        'finalRoundsStructDropMenu' => $finalRoundsStructDropMenu,
        'preregistrationAllowedCheckbox' => $preregistrationAllowedCheckbox,
        'lateEntryLimitField' => $lateEntryLimitField,
        'playerReportedResultsCheckbox' => $playerReportedResultsCheckbox,
        'registrationCapField' => $registrationCapField,
        'deckPrivacyCheckbox' => $deckPrivacyCheckbox,
        'finalsListPrivacyCheckbox' => $finalsListPrivacyCheckbox,
        'playerReportedDrawsCheckbox' => $playerReportedDrawsCheckbox,
        'privateEventCheckbox' => $privateEventCheckbox,
        'clientDropMenu' => $clientDropMenu,
        'finalizeEventCheckbox' => $finalizeEventCheckbox,
        'eventActiveCheckbox' => $eventActiveCheckbox,
        'currentRoundDropMenu' => $currentRoundDropMenu,
        'trophyField' => $trophyField,
        'showCreateNextEvent' => $showCreateNextEvent,
        'showCreateNextSeason' => $showCreateNextSeason,
    ];
}

function reportsForm(Event $event): string
{
    $entriesByDateTime = $event->getEntriesByDateTime();
    $entriesByMedal = $event->getEntriesByMedal();
    $hasEntries = count($entriesByDateTime) > 0;

    $assembleEntries = function ($entries) {
        $count = 1;
        $result = [];
        foreach ($entries as $entryName) {
            $player = new Player($entryName);
            $result[] = [
                'n' => $count,
                'entryName' => $entryName,
                'emailAd' => $player->emailAddress != '' ? $player->emailAddress : '---------'
            ];
            $count++;
        }
        return $result;
    };

    return render_name('partials/reportsForm', [
        'hasEntries' => $hasEntries,
        'standings' => $assembleEntries($entriesByMedal),
        'registrants' => $assembleEntries($entriesByDateTime),
    ]);
}

function playerList(Event $event): string
{
    $isActive = $event->active == 1;
    $isOngoing = $event->active == 1 && !$event->finalized;
    $notYetStarted = $event->active == 0 && !$event->finalized;
    $entries = $event->getEntries();
    $numEntries = count($entries);
    $format = new Format($event->format);

    $deckless = $entryInfoList = [];
    foreach ($entries as $entry) {
        $entryInfoList[] = entryListArgs($entry, $numEntries, (bool)$format->tribal);
        if (!$entry->deck) {
            $deckless[] = $entry->player->gameNameArgs($entry->event->client);
        }
    }

    $newEntry = false;
    if ($notYetStarted || $isOngoing) {
        $newEntry = stringFieldArgs('newentry', '', 40);
    }

    $showCreateNextEvent = $showCreateNextSeason = false;
    if ($event->isFinished()) {
        $nextEventName = sprintf('%s %d.%02d', $event->series, $event->season, $event->number + 1);
        $nextSeasonName = sprintf('%s %d.%02d', $event->series, $event->season + 1, 1);
        $showCreateNextEvent = Event::exists($nextEventName);
        $showCreateNextSeason = Event::exists($nextSeasonName);
    }

    return render_name('partials/playerList', [
        'event' => $event,
        'isActive' => $isActive,
        'isOngoing' => $isOngoing,
        'isFinished' => $event->isFinished(),
        'notYetStarted' => $notYetStarted,
        'hasStarted' => $event->hasStarted(),
        'hasEntries' => $numEntries > 0,
        'numEntries' => $numEntries,
        'entries' => $entryInfoList,
        'isSwiss' => $event->isSwiss(),
        'isSingleElim' => $event->isSingleElim(),
        'isNeitherSwissNorSingleElim' => !$event->isSwiss() && !$event->isSingleElim(),
        'format' => $format,
        'newEntry' => $newEntry,
        'showCreateNextEvent' => $showCreateNextEvent,
        'showCreateNextSeason' => $showCreateNextSeason,
        'deckless' => $deckless,
    ]);
}

function entryListArgs(Entry $entry, int $numEntries, bool $isTribal): array
{
    $entryInfo = getObjectVarsCamelCase($entry);
    if ($entry->event->active == 1) {
        $playerActive = Standings::playerActive($entry->event->name, $entry->player->name);
        $entryInfo['canDrop'] = $playerActive;
        $entryInfo['canUndrop'] = !$playerActive;
    }
    if ($entry->event->isFinished() && strcmp('', $entry->medal) != 0) {
        $entryInfo['medalImg'] = theme_file("images/{$entry->medal}.png");
    }
    $entryInfo['gameName'] = $entry->player->gameNameArgs($entry->event->client);
    if ($entry->deck) {
        $entryInfo['linkTo'] = $entry->deck->linkToArgs();
    } else {
        $entryInfo['createDeckLink'] = $entry->createDeckLinkArgs();
    }
    $entryInfo['invalidRegistration'] = $entry->deck != null && !$entry->deck->isValid();
    $entryInfo['tribe'] = $isTribal && $entry->deck != null ? $entry->deck->tribe : '';
    if ($entry->event->isSwiss() && !$entry->event->hasStarted()) {
        $entryInfo['initialByeDropMenu'] = initialByeDropMenuArgs('initial_byes[]', $entry->player->name, $entry->initial_byes);
    } elseif ($entry->event->isSingleElim() && !$entry->event->hasStarted()) {
        $entryInfo['initialSeedDropMenu'] = initialSeedDropMenuArgs('initial_seed[]', $entry->player->name, $entry->initial_seed, $numEntries);
    }
    if ($entry->canDelete()) {
        $entryInfo['canDelete'] = $entry->canDelete();
    } else {
        $entryInfo['notAllowed'] = notAllowedArgs("Can't delete player, they have matches recorded.");
    }
    return $entryInfo;
}

function pointsAdjustmentForm(Event $event): string
{
    $eventEntries = $event->getEntries();
    $entries = [];
    foreach ($eventEntries as $entry) {
        $player = getObjectVarsCamelCase($entry);
        $player['player'] = $entry->player;
        $player['adjustment'] = $event->getSeasonPointAdjustment($entry->player->name);
        if ($entry->medal != '') {
            $player['medalImg'] = theme_file("images/{$entry->medal}.png");
        }
        if ($entry->deck != null) {
            $player['verifiedImg'] = theme_file('images/verified.png');
        }
        $entries[] = $player;
    }
    return render_name('partials/pointsAdjustmentForm', [
        'eventName' => $event->name,
        'entries' => $entries,
    ]);
}

function unverifiedPlayerCellArgs(Event $event, Matchup $match, Player $player): array
{
    $playerName = $player->name;
    $wins = $match->getPlayerWins($playerName);
    $losses = $match->getPlayerLosses($playerName);
    $matchResult = ($wins + $losses > 0) ? ($wins > $losses ? 'W' : 'L') : null;

    return [
        'playerName' => $playerName,
        'displayName' => $player->gameNameArgs($event->client),
        'displayNameText' => $player->gameName($event->client, false),
        'hasDropped' => $match->playerDropped($playerName),
        'hasGames' => ($wins + $losses > 0),
        'matchResult' => $matchResult,
        'isDraw' => ($wins == 1 && $losses == 1),
        'verification' => $match->verification,
        'wins' => $wins,
        'losses' => $losses,
    ];
}

function matchList(Event $event, string|int|null $newMatchRound): string
{
    $matches = $event->getMatches();
    $roundLinks = [];
    for ($n = 1; $n <= $event->current_round; $n++) {
        $roundLinks[] = [
            'text' => "Round $n",
            'href' => 'event.php?view=match&name=' . rawurlencode($event->name) . "#round-{$n}"
        ];
    }
    $hasMatches = count($matches) > 0;
    $first = 1;
    $rndAdd = 0;
    $playersInMatches = [];
    $rounds = [];
    foreach ($matches as $match) {
        $matchInfo = getObjectVarsCamelCase($match);
        if ($first && $match->timing == 1) {
            $rndAdd = $match->rounds;
        }
        $first = 0;
        // add final round to main round if in extra rounds to keep round correct
        if ($match->timing == 2) {
            $printRnd = $match->round + $rndAdd;
        } else {
            $printRnd = $match->round;
        }
        $matchInfo['printRnd'] = $printRnd;
        $matchInfo['showStar'] = $match->timing > 1;
        if (!isset($rounds[$printRnd])) {
            $extraRoundTitle = '';
            if ($match->timing > 1) {
                $extraRoundTitle = "(Finals Round {$match->round})";
            }
            $rounds[$printRnd] = ['round' => $printRnd, 'extraRoundTitle' => $extraRoundTitle, 'matches' => []];
        }

        if (!isset($playersInMatches[$match->playera])) {
            $playersInMatches[$match->playera] = new Player($match->playera);
        }
        if (!isset($playersInMatches[$match->playerb])) {
            $playersInMatches[$match->playerb] = new Player($match->playerb);
        }
        $playerA = $playersInMatches[$match->playera];
        $playerB = $playersInMatches[$match->playerb];
        $matchInfo['gameNameA'] = $playerA->gameNameArgs($event->client);
        $matchInfo['gameNameB'] = $playerB->gameNameArgs($event->client);

        $isActiveUnverified = strcasecmp($match->verification, 'verified') != 0 && $event->finalized == 0;
        if ($isActiveUnverified) {
            $matchInfo['unverifiedPlayerCellA'] = unverifiedPlayerCellArgs($event, $match, $playerA);
            $matchInfo['resultDropMenu'] = resultDropMenuArgs('matchresult[]');
            $matchInfo['unverifiedPlayerCellB'] = unverifiedPlayerCellArgs($event, $match, $playerB);
        } else {
            $playerAWins = $match->getPlayerWins($match->playera);
            $playerBWins = $match->getPlayerWins($match->playerb);
            $matchInfo['playerAWins'] = $playerAWins;
            $matchInfo['playerBWins'] = $playerBWins;
            $matchInfo['hasPlayerADropped'] = $match->playerDropped($match->playera);
            $matchInfo['hasPlayerBDropped'] = $match->playerDropped($match->playerb);
            $isBye = $match->playera == $match->playerb;
            $isDraw = ($match->getPlayerWins($match->playera) == 1) && ($match->getPlayerWins($match->playerb) == 1);
            $matchInfo['hasResult'] = !$isBye && !$isDraw;
            $matchInfo['isBye'] = $isBye;
            $matchInfo['isDraw'] = $isDraw;
        }
        $matchInfo['isActiveUnverified'] = $isActiveUnverified;
        $rounds[$printRnd]['matches'][] = $matchInfo;
    }
    // 0-index $rounds for mustache, if they start at 1 it will fail to loop over them.
    $rounds = array_values($rounds);

    $lastRound = $rounds ? $rounds[count($rounds) - 1] : [];

    $playerADropMenu = playerDropMenuArgs($event, 'A');
    $playerBDropMenu = playerDropMenuArgs($event, 'B');
    $playerByeMenu = $roundDropMenu = $resultDropMenu = null;
    if ($event->active) {
        $playerByeMenu = playerByeMenuArgs($event);
    } else {
        $roundDropMenu = roundDropMenuArgs($event, $newMatchRound);
        $resultDropMenu = resultDropMenuArgs('newmatchresult');
    }

    $structure = $event->current_round > $event->mainrounds ? $event->finalstruct : $event->mainstruct;
    $isLeague = $structure == 'League';

    return render_name('partials/matchList', [
        'roundLinks' => $roundLinks,
        'hasMatches' => $hasMatches,
        'rounds' => $rounds,
        'lastRound' => $lastRound,
        'event' => getObjectVarsCamelCase($event),
        'playerADropMenu' => $playerADropMenu,
        'playerBDropMenu' => $playerBDropMenu,
        'playerByeMenu' => $playerByeMenu,
        'roundDropMenu' => $roundDropMenu,
        'resultDropMenu' => $resultDropMenu,
        'isBeforeRoundTwo' => $event->current_round <= 1,
        'structureSummary' => $event->structureSummary(),
        'isLeague' => $isLeague,
    ]);
}

function standingsList(Event $event): string
{
    return Standings::eventStandings($event->name, Player::loginName());
}

function medalList(Event $event): string
{
    $finalists = $event->getFinalists();
    $pos = 0;
    foreach ($finalists as &$finalist) {
        $finalist['playerDropMenu'] = playerDropMenuArgs($event, "$pos", $finalist['player']);
        $finalist['img'] = theme_file("images/{$finalist['medal']}.png");
        $pos++;
    }
    return render_name('partials/medalList', [
        'eventName' => $event->name,
        'finalists' => $finalists,
    ]);
}

function kValueSelectInput(int $kvalue): array
{
    $names = [
        ''            => '- K-Value -', 8 => 'Casual (Alt Event)', 16 => 'Regular (less than 24 players)',
        24            => 'Large (24 or more players)', 32 => 'Championship',
    ];
    return selectInputArgs('K-Value', 'kvalue', $names, $kvalue);
}

function monthDropMenuArgs(string|int $month): array
{
    if (strcmp($month, '') == 0) {
        $month = -1;
    }
    $names = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];
    $options = [];
    for ($m = 1; $m <= 12; $m++) {
        $options[] = [
            'isSelected' => $month == $m,
            'value' => $m,
            'text' => $names[$m - 1],
        ];
    }
    return [
       'name' => 'month',
       'default' => '- Month -',
       'options' => $options,
    ];
}

function structDropMenuArgs(string $field, string $def): array
{
    $names = ['Swiss', 'Single Elimination', 'League', 'League Match'];
    if ($def == 'Swiss (Blossom)') {
        $def = 'Swiss';
    }
    if ($def == 'Round Robin') {
        $names[] = 'Round Robin';
    }
    $options = [];
    foreach ($names as $name) {
        $options[] = [
            'value' => $name,
            'text' => $name,
            'isSelected' => strcmp($def, $name) == 0,
        ];
    }
    return [
        'name' => $field,
        'default' => '- Structure -',
        'options' => $options,
    ];
}

function clientDropMenuArgs(string $field, int $def): array
{
    $clients = [
        1 => 'MTGO',
        2 => 'Arena',
        3 => 'Other',
    ];
    $options = [];
    foreach ($clients as $value => $text) {
        $options[] = [
            'isSelected' => $def == $value,
            'value' => $value,
            'text' => $text,
        ];
    }
    return [
        'id' => $field,
        'name' => $field,
        'default' => '- Client -',
        'options' => $options,
    ];
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
    $event->kvalue = $_POST['kvalue'];
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
    $event->client = $_POST['client'];

    $event->save();

    return $event;
}

function trophyFieldArgs(Event $event): array
{
    return [
        'hasTrophy' => $event->hastrophy,
        'eventName' => $event->name,
    ];
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
    $stmt->bindParam(1, $event, PDO::PARAM_STR);
    if (!$stmt->execute()) {
        throw new Exception($stmt->error, 1);
    }
    $stmt = $db->prepare('INSERT INTO trophies(event, size, type, image) VALUES(?, ?, ?, ?)');
    $stmt->bindParam(1, $event, PDO::PARAM_STR);
    $stmt->bindParam(2, $size, PDO::PARAM_INT);
    $stmt->bindParam(3, $type, PDO::PARAM_STR);
    $stmt->bindParam(4, $f, PDO::PARAM_LOB);
    if (!$stmt->execute()) {
        throw new Exception($stmt->error, 1);
    }
    fclose($f);

    return true;
}

function playerByeMenuArgs(Event $event): array
{
    $playerNames = $event->getRegisteredPlayers(true);
    $options = [];
    foreach ($playerNames as $player) {
        $options[] = [
            'value' => $player,
            'text' => $player,
        ];
    }
    return [
        'name' => 'newbyeplayer',
        'default' => '- Bye Player -',
        'options' => $options,
    ];
}

function playerDropMenuArgs(Event $event, string|int $letter, $def = "\n"): array
{
    // If the event is active, only list players who haven't already dropped.
    // Otherwise, list all registered players.
    $playerNames = $event->getRegisteredPlayers($event->active);
    sort($playerNames, SORT_STRING | SORT_NATURAL | SORT_FLAG_CASE);

    $default = strcmp("\n", $def) == 0 ? "- Player $letter -" : '- None -';
    $options = [];
    foreach ($playerNames as $player) {
        $options[] = [
            'isSelected' => strcmp($player, $def) == 0,
            'value' => $player,
            'text' => $player,
        ];
    }
    return [
        'name' => "newmatchplayer$letter",
        'default' => $default,
        'options' => $options,
    ];
}

function roundDropMenu(Event $event, int|string $selected): string
{
    $args = roundDropMenuArgs($event, $selected);
    return render_name('partials/dropMenu', $args);
}

function roundDropMenuArgs(Event $event, int|string $selected): array
{
    $options = [];
    for ($r = 1; $r <= ((int)$event->mainrounds + (int)$event->finalrounds); $r++) {
        $star = $r > $event->mainrounds ? '*' : '';
        $options[] = [
            'isSelected' => $selected == $r,
            'value' => $r,
            'text' => "$r$star",
        ];
    }
    return [
        'name' => 'newmatchround',
        'default' => '- Round -',
        'options' => $options,
    ];
}

function resultDropMenuArgs(string $name, array $extraOptions = []): array
{
    $options = [
        ['value' => '2-0', 'text' => '2-0'],
        ['value' => '2-1', 'text' => '2-1'],
        ['value' => '1-2', 'text' => '1-2'],
        ['value' => '0-2', 'text' => '0-2'],
        ['value' => 'D', 'text' => 'Draw'],

    ];
    foreach ($extraOptions as $value => $text) {
        $options[] = ['value' => $value, 'text' => $text];
    }
    return [
        'name' => $name,
        'default' => '- Result -',
        'options' => $options,
    ];
}

function initialByeDropMenuArgs(string $name = 'initial_byes', string $playerName = '', int $currentByes = 0): array
{
    $options = [];
    for ($i = 0; $i < 3; $i++) {
        $options[] = [
            'value' => "$playerName $i",
            'text' => $i == 0 ? 'None' : "$i",
            'isSelected' => $currentByes == $i,
        ];
    }
    return [
        'name' => $name,
        'options' => $options,
    ];
}

function initialSeedDropMenuArgs(string $name, string $playerName, int $currentSeed, int $numEntries): array
{
    $options = [
        ['value' => "$playerName 127", 'text' => 'None', 'isSelected' => $currentSeed == 127],
    ];
    for ($i = 1; $i <= $numEntries; $i++) {
        $options[] = [
            'value' => "$playerName $i",
            'text' => "$i",
            'isSelected' => $currentSeed == $i,
        ];
    }
    return [
       'name' => $name,
       'options' => $options,
    ];
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
            $event->addMatch($playerA, $playerB, $rnd, $res, $pAWins, $pBWins);
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
    $t8 = [$_POST['newmatchplayer5'],  $_POST['newmatchplayer6'],  $_POST['newmatchplayer7'],  $_POST['newmatchplayer8']];

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

function authFailed(): string
{
    return render_name('partials/authFailed');
}
