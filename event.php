<?php
include 'lib.php';
include 'lib_form_helper.php';
session_start();

$verified_url = theme_file('images/verified.png');
$dot_url = theme_file('images/dot.png');

$drop_icon = '&#x2690;';

$js = <<<EOD

function updatePlayerCount() {
  var players = $('tr.entry_row').length;
  $('#player_count').html(players + ' Registered Players');
}

function addPlayerRow(data) {
  if (!data.success) { return false; }
  var html = '<tr class="entry_row" id="entry_row_' + data.player + '"><td>';
  if (data.event_running) {
    html += '<input type="checkbox" name="dropplayer[]" value="' + data.player + '" />';
  }
  html += '</td><td>' + data.player + '</td>';
  html += '<td>';
  if (data.verified) {
    html += '<img src="$verified_url" />';
  }
  html += '</td>';
  html += '<td align="center"><img src="$dot_url" alt="dot" /></td>';
  html += '<td><a class="create_deck_link" href="deck.php?player=' + data.player + '&event=' + event_name + '&mode=create">[Create Deck]</a></td>';
  html += '<td align="center"><input type="checkbox" name="delentries[]" value="' + data.player + '" /></td></tr>';
  $('input[name=newentry]').val("");
  $('#row_new_entry').before(html);
  $('#entry_row_' + data.player).find('td').wrapInner('<div style="display: none;" />').parent().find('td > div').slideDown(500, function() { var set = $(this); set.replaceWith(set.contents()); });
  updatePlayerCount();
}

function delPlayerRow(data) {
  if (!data.success) { return false; }
  $('#entry_row_' + data.player).removeClass('entry_row').find('td').wrapInner('<div style="display: block;" />').parent().find('td > div').slideUp(500, function() { $(this).parent().parent().remove(); });
  updatePlayerCount();
}

function dropPlayer(data) {
  if (!data.success) { return false; }
  $('#entry_row_' + data.player).find('input[name=dropplayer[]]').parent().html('{$drop_icon} ' + data.round + ' <a href=\"{$CONFIG['base_url']}event.php?player=' + data.player + '&action=undrop&name=' + data.eventname + '\">(undo)</a>');
}

function updateRegistration() {
  event_name = $('input[name=name]').val();
  newentry_name = $('input[name=newentry]').val();
  if (newentry_name != "") {
    $.ajax({url: 'ajax.php?event=' + event_name
                       + '&addplayer=' + newentry_name,
                       success: addPlayerRow});
  }
  $('input[name=delentries[]]').each(function(x, e) {
    if (e.checked) {
      $.ajax({url: 'ajax.php?event=' + event_name + '&delplayer=' + e.value,
              success: delPlayerRow});
    }
  });
  $('input[name=dropplayer[]]').each(function(x, e) {
    if (e.checked) {
      $.ajax({url: 'ajax.php?event=' + event_name + '&dropplayer=' + e.value,
             success: dropPlayer});
    }
  });
  return false;
}

$(document).ready(function() {
  $('#update_reg').click(updateRegistration);
});
EOD;

print_header('Event Host Control Panel');
?>
<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Host Control Panel </div>

<?php
if (Player::isLoggedIn()) {
    content();
} else {
    linkToLogin('Host Control Panel');
}
?>

<div class="clear"></div>
</div> </div>

<?php print_footer(); ?>

<?php

function mode_is($str)
{
    $mode = null;
    if (isset($_REQUEST['mode']) and $_REQUEST['mode'] != '') {
        $mode = $_REQUEST['mode'];
    }

    return (bool) (strcmp($mode, $str) == 0);
}

function content()
{
    $event = null;

    if (!isset($_GET['series'])) {
        $_GET['series'] = '';
    }
    if (!isset($_GET['season'])) {
        $_GET['season'] = '';
    }

    if (isset($_GET['event']) && !isset($_GET['name'])) {
        $_GET['name'] = $_GET['event'];
    }

    if (mode_is('Create New Event')) {
        $series = new Series($_POST['series']);
        if (($series->authCheck(Player::loginName())) && isset($_POST['insert'])) {
            insertEvent();
            eventList();
        } else {
            authFailed();
        }
    } elseif (mode_is('Create A New Event')) {
        eventForm(null, true);
    } elseif (mode_is('Create Next Event')) {
        $eventName = $_REQUEST['name'];

        try {
            $oldevent = new Event($eventName);
        } catch (Exception $exc) {
            if ($exc->getMessage() == "Event $eventName not found in DB") {
                $seriesName = preg_replace('/ 1.00$/', '', $eventName);
                $oldevent = new Event('');
                $oldevent->name = $eventName;
                $oldevent->season = 1;
                $oldevent->number = 0;
                $oldevent->series = $seriesName;
            } else {
                echo $exc->getMessage();
            }
        }
        $newevent = new Event('');
        $newevent->season = $oldevent->season;
        $newevent->number = $oldevent->number + 1;
        $newevent->format = $oldevent->format;

        $newevent->start = strftime('%Y-%m-%d %H:%M:00', strtotime($oldevent->start) + (86400 * 7));
        $newevent->kvalue = $oldevent->kvalue;
        $newevent->finalized = 0;
        $newevent->prereg_allowed = $oldevent->prereg_allowed;
        $newevent->pkonly = $oldevent->pkonly;
        $newevent->threadurl = $oldevent->threadurl;
        $newevent->reporturl = $oldevent->reporturl;
        $newevent->metaurl = $oldevent->metaurl;

        $newevent->player_editdecks = $oldevent->player_editdecks;

        $newevent->series = $oldevent->series;
        $newevent->host = $oldevent->host;
        $newevent->cohost = $oldevent->cohost;

        $newevent->mainrounds = $oldevent->mainrounds;
        $newevent->mainstruct = $oldevent->mainstruct;
        $newevent->finalrounds = $oldevent->finalrounds;
        $newevent->finalstruct = $oldevent->finalstruct;

        $newevent->player_reportable = $oldevent->player_reportable;
        $newevent->prereg_cap = $oldevent->prereg_cap;
        $newevent->private_decks = $oldevent->private_decks;
        $newevent->private_finals = $oldevent->private_finals;

        $newevent->player_reportable = $oldevent->player_reportable;
        $newevent->player_reported_draws = $oldevent->player_reported_draws;
        $newevent->prereg_cap = $oldevent->prereg_cap;
        $newevent->late_entry_limit = $oldevent->late_entry_limit;

        $newevent->name = sprintf('%s %d.%02d', $newevent->series, $newevent->season, $newevent->number);

        eventForm($newevent, true);
    } elseif (isset($_GET['name'])) {
        $event = new Event($_GET['name']);
        if (!$event->authCheck(Player::loginName())) {
            authFailed();

            return;
        }
        if (isset($_GET['action'])) {
            if (strcmp($_GET['action'], 'undrop') == 0) {
                $entry = new Entry($_GET['name'], $_GET['player']);
                if ($entry->deck and $entry->deck->isValid()) {
                    $event->undropPlayer($_GET['player']);
                }
            }
        }
        eventForm($event);
    } elseif (isset($_POST['name'])) {
        $event = new Event($_POST['name']);

        if (!$event->authCheck(Player::loginName())) {
            authFailed();
        } else {
            if (mode_is('Start Event')) {
                $event->startEvent();
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
            } elseif (mode_is('Parse DCI Files')) {
                dciInput();
            } elseif (mode_is('Parse DCIv3 Files')) {
                dci3Input();
            } elseif (mode_is('Auto-Input Event Data')) {
                autoInput();
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
            eventForm($event);
        }
    } else {
        if (!isset($_POST['series'])) {
            $_POST['series'] = '';
        }
        if (!isset($_POST['season'])) {
            $_POST['season'] = '';
        }
        eventList($_POST['series'], $_POST['season']);
    }
}

function eventList($series = '', $season = '')
{
    $db = Database::getConnection();
    $player = Player::getSessionPlayer();
    $playerSeries = $player->organizersSeries();
    $seriesEscaped = [];
    foreach ($playerSeries as $oneSeries) {
        $seriesEscaped[] = $db->escape_string($oneSeries);
    }
    $seriesString = '"'.implode('","', $seriesEscaped).'"';
    $query = "SELECT e.name AS name, e.format AS format,
    COUNT(DISTINCT n.player) AS players, e.host AS host, e.start AS start,
    e.finalized, e.cohost, e.series, e.kvalue
    FROM events e
    LEFT OUTER JOIN entries AS n ON n.event = e.name
    WHERE (e.host = \"{$db->escape_string($player->name)}\"
           OR e.cohost = \"{$db->escape_string($player->name)}\"
           OR e.series IN (".$seriesString.'))';
    if (isset($_GET['format']) && strcmp($_GET['format'], '') != 0) {
        $query = $query." AND e.format=\"{$db->escape_string($_GET['format'])}\" ";
    }
    if (isset($_GET['series']) && strcmp($_GET['series'], '') != 0) {
        $query = $query." AND e.series=\"{$db->escape_string($_GET['series'])}\" ";
    }
    if (isset($_GET['season']) && strcmp($_GET['season'], '') != 0) {
        $query = $query." AND e.season=\"{$db->escape_string($_GET['season'])}\" ";
    }
    $query = $query.' GROUP BY e.name ORDER BY e.start DESC LIMIT 100';
    $result = $db->query($query);

    $seriesShown = [];
    $results = [];
    while ($thisEvent = $result->fetch_assoc()) {
        $results[] = $thisEvent;
        $seriesShown[] = $thisEvent['series'];
    }

    if (isset($_GET['series']) && $_GET['series'] != '') {
        $seriesShown = $playerSeries;
    } else {
        $seriesShown = array_unique($seriesShown);
    }

    echo '<form action="event.php" method="get">';
    echo '<table class="form" style="border-width: 0px" align="center">';
    echo '<tr><td colspan="2" align="center"><b>Filters</td></tr>';
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr><th>Format</th><td>';
    if (!isset($_GET['format'])) {
        $_GET['format'] = '';
    }
    formatDropMenu($_GET['format'], 1);
    echo '</td></tr>';
    echo '<tr><th>Series</th><td>';
    Series::dropMenu($_GET['series'], 1, $seriesShown);
    echo '</td></tr>';
    echo '<tr><th>Season</th><td>';
    seasonDropMenu($_GET['season'], 1);
    echo '</td></tr>';
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr><td colspan="2" class="buttons">';
    if (count($playerSeries) > 0) {
        echo "<input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"Create A New Event\" />\n";
    }
    echo "<input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"Filter Events\" />\n";
    echo '</td></tr></table>';
    echo '<table style="border-width: 0px" align="center" cellpadding="3">';
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    echo '<tr><td><b>Event</b></td><td><b>Format</b></td><td><b>K-Value</b></td>';
    echo '<td align="center"><b>Players</td>';
    echo '<td><b>Host(s)</td>';
    echo '<td align="center"><b>Finalized</td></tr>';

    foreach ($results as $thisEvent) {
        $dateStr = $thisEvent['start'];
        $dateArr = explode(' ', $dateStr);
        $date = $dateArr[0];
        $kvalue = '';
        switch ($thisEvent['kvalue']) {
        case 0:
            $kvalue = 'none';
            break;
        case 8:
            $kvalue = 'Casual';
            break;
        case 16:
            $kvalue = 'Regular';
            break;
        case 24:
            $kvalue = 'Large';
            break;
        case 32:
            $kvalue = 'Championship';
            break;
    }
        echo '<tr><td>';
        echo '<a href="event.php?name='.rawurlencode($thisEvent['name']).'">';
        echo "{$thisEvent['name']}</a></td>";
        echo "<td>{$thisEvent['format']}</td>";
        echo "<td>{$kvalue}</td>";
        echo "<td align=\"center\">{$thisEvent['players']}</td>";
        echo "<td>{$thisEvent['host']}";
        $ch = $thisEvent['cohost'];
        if (!is_null($ch) && strcmp($ch, '') != 0) {
            echo "/$ch";
        }
        echo '</td>';
        //echo "<td>$date</td>";
        echo '<td align="center">';
        if ($thisEvent['finalized'] == 1) {
            echo '&#x2714;';
        }
        echo '</td>';
        echo '</tr>';
    }

    if ($result->num_rows == 100) {
        echo '<tr><td colspan="5" width="500">&nbsp;</td></tr>';
        echo '<tr><td colspan="5" align="center">';
        echo '<i>This list only shows the 100 most recent results. ';
        echo 'Please use the filters at the top of this page to find older ';
        echo 'results.</i></td></tr>';
    }
    $result->close();

    echo '<tr><td colspan="5" width="500">&nbsp;</td></tr>';
    echo '</table></form>';
}

function eventForm($event = null, $forcenew = false)
{
    if ($forcenew) {
        $edit = 0;
    } elseif ($event != null && $event->name == '') {
        $edit = 0;
    } else {
        $edit = ($event != null);
    }
    if (is_null($event)) {
        $event = new Event('');
    }

    echo '<table style="border-width: 0px" align="center">';
    if ($edit) {
        if (!isset($view)) {
            if ($event->active) {
                $view = 'match';
            } else {
                $view = 'reg';
            }
        }
        $view = isset($_GET['view']) ? $_GET['view'] : $view;
        $view = isset($_POST['view']) ? $_POST['view'] : $view;
    } else {
        $view = 'edit';
    }

    echo '<tr><td colspan="2">&nbsp;</td></tr>';
    controlPanel($event, $view);
    echo '<tr><td colspan="2">&nbsp;</td></tr>';
    echo '</table>';

    if (strcmp($view, 'reg') == 0) {
        playerList($event);
    } elseif (strcmp($view, 'match') == 0) {
        matchList($event);
    } elseif (strcmp($view, 'standings') == 0) {
        standingsList($event);
    } elseif (strcmp($view, 'medal') == 0) {
        medalList($event);
    } elseif (strcmp($view, 'autoinput') == 0) {
        autoInputForm($event);
    } elseif (strcmp($view, 'fileinput') == 0) {
        fileInputForm($event);
    // file3InputForm($event); DCI 3 files currently don't work. Re-enable once they do.
    } elseif (strcmp($view, 'points_adj') == 0) {
        pointsAdjustmentForm($event);
    } elseif (strcmp($view, 'reports') == 0) {
        reportsForm($event);
    } else {
        echo '<form action="event.php" method="post" ';
        echo 'enctype="multipart/form-data">';
        echo '<table class="form" style="border-width: 0px" align="center">';
        if ($event->start != null) {
            $date = $event->start;
            preg_match('/([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+):.*/', $date, $datearr);
            $year = $datearr[1];
            $month = $datearr[2];
            $day = $datearr[3];
            $hour = $datearr[4];
            $minutes = $datearr[5];
            echo '<tr><th>Currently Editing</th>';
            echo "<td><i>{$event->name}</i>";
            echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\">";
            echo '</td>';
            echo '</tr><tr><td>&nbsp;</td><td>';
            $prevevent = $event->findPrev();
            if ($prevevent) {
                echo $prevevent->makeLink('&laquo; Previous');
            }
            $nextevent = $event->findNext();
            if ($nextevent) {
                if ($prevevent) {
                    echo ' | ';
                }
                echo $nextevent->makeLink('Next &raquo;');
            }
            echo '</td></tr>';
        } else {
            echo '<tr><th>Event Name</th>';
            echo '<td><input type="radio" name="naming" value="auto" checked>';
            echo 'Automatically name this event based on Series, Season, and Number.';
            echo '<br /><input type="radio" name="naming" value="custom">';
            echo 'Use a custom name: ';
            echo "<input class=\"inputbox\" type=\"text\" name=\"name\" value=\"{$event->name}\" ";
            echo 'size="40">';
            echo '</td></tr>';
            $year = strftime('Y', time());
            $month = strftime('B', time());
            $day = strftime('Y', time());
            $hour = strftime('H', time());
            $minutes = strftime('M', time());
        }
        echo '<tr><th>Date & Time</th><td>';
        numDropMenu('year', '- Year -', date('Y') + 1, $year, 2011);
        monthDropMenu($month);
        numDropMenu('day', '- Day- ', 31, $day, 1);
        timeDropMenu($hour, $minutes);
        echo '</td></tr>';
        echo '<tr><th>Series</th><td>';
        $seriesList = Player::getSessionPlayer()->organizersSeries();
        if ($event->series) {
            $seriesList[] = $event->series;
        }
        $seriesList = array_unique($seriesList);
        Series::dropMenu($event->series, 0, $seriesList);
        echo '</td></tr>';
        echo '<tr><th>Season</th><td>';
        seasonDropMenu($event->season);
        echo '</td></tr>';
        echo '<tr><th>Number</th><td>';
        numDropMenu('number', '- Event Number -', Event::largestEventNum() + 5, $event->number, 0, 'Custom');
        echo '</td><tr>';
        echo '<tr><th>Format</th><td>';
        formatDropMenu($event->format);
        echo '</td></tr>';
        kValueDropMenu($event->kvalue);
        echo '<tr><th>Host/Cohost</th><td>';
        stringField('host', $event->host, 20);
        echo '&nbsp;/&nbsp;';
        stringField('cohost', $event->cohost, 20);
        echo '</td></tr>';
        print_text_input('Event Thread URL', 'threadurl', $event->threadurl, 60);
        print_text_input('Metagame URL', 'metaurl', $event->metaurl, 60);
        print_text_input('Report URL', 'reporturl', $event->reporturl, 60);
        echo '<tr><th>Main Event Structure</th><td>';
        numDropMenu('mainrounds', '- No. of Rounds -', 10, $event->mainrounds, 1);
        echo ' rounds of ';
        structDropMenu('mainstruct', $event->mainstruct);
        echo '</td></tr>';
        echo '<tr><th>Finals Structure</th><td>';
        numDropMenu('finalrounds', '- No. of Rounds -', 10, $event->finalrounds, 0);
        echo ' rounds of ';
        structDropMenu('finalstruct', $event->finalstruct);
        echo '</td></tr>';
        print_checkbox_input('Allow Pre-Registration', 'prereg_allowed', $event->prereg_allowed);
        print_text_input('Late Entry Limit', 'late_entry_limit', $event->late_entry_limit, 4, 'The event host may still add players after this round.');
        // print_checkbox_input('Pauper Krew Members Only', 'pkonly', $event->pkonly);

        print_checkbox_input('Allow Players to Report Results', 'player_reportable', $event->player_reportable);

        print_text_input('Player initiatied registration cap', 'prereg_cap', $event->prereg_cap, 4, 'The event host may still add players beyond this limit. 0 is disabled.');

        print_checkbox_input('Deck List Privacy', 'private_decks', $event->private_decks);
        print_checkbox_input('Finals List Privacy', 'private_finals', $event->private_finals);
        print_checkbox_input('Allow Player Reported Draws', 'player_reported_draws', $event->player_reported_draws, 'This allows players to report a draw result for matches.');

        if ($edit == 0) {
            echo '<tr><td>&nbsp;</td></tr>';
            echo '<tr><td colspan="2" class="buttons">';
            echo '<input class="inputbutton" type="submit" name="mode" value="Create New Event">';
            echo '<input type="hidden" name="insert" value="1">';
            echo '</td></tr>';
        } else {
            print_checkbox_input('Finalize Event', 'finalized', $event->finalized);
            print_checkbox_input('Event Active', 'active', $event->active);

            echo '<tr><th>Current Round</th>';
            echo '<td>';
            roundDropMenu($event, $event->current_round);
            echo '</td></tr>';
            trophyField($event);
            echo '<tr><td>&nbsp;</td></tr>';
            echo '<tr><td colspan="2" class="buttons">';
            echo ' <input class="inputbutton" type="submit" name="mode" value="Update Event Info" />';
            $nexteventname = sprintf('%s %d.%02d', $event->series, $event->season, $event->number + 1);
            if (!Event::exists($nexteventname)) {
                echo ' <input class="inputbutton" type="submit" name="mode" value="Create Next Event" />';
            }
            echo '<input type="hidden" name="update" value="1" />';
            echo '</td></tr>';
            echo '</table>';
            echo '</form>';
        }
    }
    echo '</table>';
}

function reportsForm($event)
{
    /* @var $entries \www\models\Event.php
     */
    $entries = $event->getEntriesByDateTime();
    $entries2 = $event->getEntriesByMedal();
    $numEntries = count($entries);
    $emailAd = '---------';

    echo '<table style="border-width: 0px" align="center">';
    echo '<center><h2><b>Player Information</b></h2></center>';
    echo '<tr><td>&nbsp;</td></tr>';
    if ($numEntries > 0) {
        echo '<tr><th>Players by final placing</th><th>Player</th><th>Email</th></tr>';
        $count = 1;
        foreach ($entries2 as $entryName) {
            $player = new Player($entryName);
            if ($player->emailAddress != '') {
                $emailAd = $player->emailAddress;
            } else {
                $emailAd = '---------';
            }
            echo "<tr><td align=\"center\">$count</td><td>$entryName</td><td align=\"center\">$emailAd</td></tr>";
            $count++;
        }

        echo '<tr><th>Registration Order</th><th>Player</th><th>Email</th></tr>';
        $count = 1;
        foreach ($entries as $entryName) {
            $player = new Player($entryName);
            if ($player->emailAddress != '') {
                $emailAd = $player->emailAddress;
            } else {
                $emailAd = '---------';
            }
            echo "<tr><td align=\"center\">$count</td><td>$entryName</td><td align=\"center\">$emailAd</td></tr>";
            $count++;
        }
    } else {
        echo '<tr><td align="center" colspan="5"><i>';
        echo 'No players are currently registered for this event.</i></td></tr>';
    }
    echo '</table>';
}

function playerList($event)
{
    global $drop_icon;
    $entries = $event->getEntries();
    $numentries = count($entries);
    $format = new Format($event->format);
    echo '<form action="event.php" method="post">';
    echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\" />";
    echo '<table style="border-width: 0px" align="center">';
    echo '<tr><td colspan="2" align="center">';
    echo '<table align="center" style="border-width: 0px;">';
    echo '<tr><td colspan="4" align="center">';
    echo "<h2>{$event->name}</h2></td></tr>";
    echo '<tr><td colspan="4" align="center">';
    if ($numentries > 0) {
        echo "<b>{$numentries} Registered Players</b></td></tr>";
    } else {
        echo '<b>Registered Players</b></td></tr>';
    }
    echo '<tr><td>&nbsp;</td><tr>';
    echo '<input type="hidden" name="view" value="reg">';
    if ($numentries > 0) {
        echo '<tr>';
        if ($event->active == 1) {
            echo '<th>Drop</th>';
        }
        if ($event->finalized && !$event->active) {
            echo '<th>Medal</th>';
        }
        echo '<th style="text-align: center">Player</th>';
        echo '<th style="text-align: center">Deck</th>';
        if ($format->tribal) {
            echo '<th>Tribe</th>';
        }
        echo '<th>Delete</th></tr>';
    } else {
        echo '<tr><td align="center" colspan="5"><i>';
        echo 'No players are currently registered for this event.</i></td></tr>';
    }

    foreach ($entries as $entry) {
        echo "<tr id=\"entry_row_{$entry->player->name}\">";
        // Show drop box if event is active.
        if ($event->active == 1) {
            if (Standings::playerActive($event->name, $entry->player->name)) {
                echo '<td align="center">';
                echo '<input type="checkbox" name="dropplayer[]" ';
                echo "value=\"{$entry->player->name}\"></td>";
            } else {
                echo "<td>{$drop_icon} {$entry->drop_round} <a href=\"event.php?view=reg&player=".$entry->player->name.'&event='.$event->name.'&action=undrop&name='.$event->name.'">(undrop)</a></td>'; // else echo a symbol to represent player has dropped
            }
        }
        if ($event->finalized && !$event->active) {
            if (strcmp('', $entry->medal) != 0) {
                $img = medalImgStr($entry->medal);
            }
            echo "<td align=\"center\">$img</td>";
        }
        echo '<td>';
        if ($entry->player->emailAddress == '') {
            echo "{$entry->player->name}";
        } else {
            displayPlayerEmailPopUp($entry->player->name, $entry->player->emailAddress);
        }
        echo '</td>';
        if ($entry->deck) {
            $decklink = $entry->deck->linkTo();
        } else {
            $decklink = $entry->createDeckLink();
        }
        $rstar = '<font color="red">*</font>';
        if ($entry->deck != null) {
            if (!$entry->deck->isValid()) {
                $decklink .= $rstar;
            }
        }
        echo "<td>$decklink</td>";
        if ($format->tribal) {
            if ($entry->deck != null) {
                echo '<td>'.$entry->deck->tribe.'</td>';
            } else {
                echo '<td> </td>'; // leave tribe blank
            }
        }
        echo '<td align="center">';
        if ($entry->canDelete()) {
            echo "<input type=\"checkbox\" name=\"delentries[]\" value=\"{$entry->player->name}\" />";
        } else {
            not_allowed("Can't delete player, they have matches recorded.");
        }
        echo '</td></tr>';
    }
    if ($event->active == 0 && !$event->finalized) {
        echo '<tr id="row_new_entry"><td>Add: ';
        stringField('newentry', '', 40);
        echo '</td><td>&nbsp;</td><td colspan=2>';
        echo '<input id="update_reg" class="inputbutton" type="submit" name="mode" value="Update Registration" />';
        echo '</td></tr>';
    } elseif ($event->active == 1 && !$event->finalized) {
        echo '<tr id="row_new_entry"><td>Add:</td><td>';
        stringField('newentry', '', 40);
        echo '</td><td>&nbsp;</td><td colspan=2>';
        echo '<input id="update_reg" class="inputbutton" type="submit" name="mode" value="Update Registration" />';
        echo '</td></tr>';
    }
    echo '</table>';
    echo '</form>';
    echo '</td></tr>';
    echo '</table>';
    echo '</td></tr>';
    echo '</table>';

    if ($event->active == 1) {
        echo '<table><tr><td colspan="2">';
        echo '<font color= "red"><b><p class="squeeze">Players added after the event has started:</p></b></font>';
        echo '<ul>';
        echo '<li>receive 0 points for any rounds already started</li>';
        echo '<li>will be paired when the next round begins</li>';
        echo '</ul>';
        echo '</td></tr></table>';
    }

    if ($event->active == 0 && $event->finalized == 0) {
        echo '<table><tr><td colspan="2">';
        echo '<font color= "red"><b><p class="squeeze">Warning: Players who have not entered deck lists will be dropped automatically!</p></b></font>';
        echo '</td></tr></table>';
    }

    echo '<div id="event_run_actions">';
    echo '<form action="event.php" method="post">';
    echo '<input type="hidden" name="view" value="reg" />';
    echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\" />";
    echo '<table><th>Round Actions</th><tr>';
    if ($event->active == 0 && $event->finalized == 0) {
        echo '<td><input id="start_event" class="inputbutton" type="submit" name="mode" value="Start Event" /></td></tr>';
    } elseif ($event->active == 1) {
        echo '<td><input id="start_event" class="inputbutton" type="submit" name="mode" value="Recalculate Standings" />';
        echo '<input id="start_event" class="inputbutton" type="submit" name="mode" value="Reset Event" />';
        echo '<input id="start_event" class="inputbutton" type="submit" name="mode" value="Delete Matches and Re-Pair Round" /></td></tr>';
    } else {
        echo '<td><input id="start_event" class="inputbutton" type="submit" name="mode" value="Reactivate Event" />';
        echo '<input id="start_event" class="inputbutton" type="submit" name="mode" value="Recalculate Standings" />';
        echo '<input id="start_event" class="inputbutton" type="submit" name="mode" value="Assign Medals" />';
        $nexteventname = sprintf('%s %d.%02d', $event->series, $event->season, $event->number + 1);
        if (!Event::exists($nexteventname)) {
            echo '<input class="inputbutton" type="submit" name="mode" value="Create Next Event" />';
        }
        echo '</td></tr>';
    }
    echo '</table>';
    echo '</form>';
    echo '</div>';
}

function pointsAdjustmentForm($event)
{
    $entries = $event->getEntries();

    // Start a new form
    echo '<form action="event.php" method="post">';
    echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\" />";
    echo '<table style="border-width: 0px" align="center">';
    echo '<input type="hidden" name="view" value="points_adj">';
    echo '<tr class="top"><th>Player</th><th></th><th>Deck</th><th>Points<br />Adj.</th><th>Reason</th></tr>';
    foreach ($entries as $entry) {
        $name = $entry->player->name;
        $adjustment = $event->getSeasonPointAdjustment($name);
        echo "<tr><td>{$name}</td>";
        if ($entry->medal != '') {
            $img = medalImgStr($entry->medal);
            echo "<td>{$img}</td>";
        } else {
            echo '<td></td>';
        }
        if ($entry->deck != null) {
            $img = image_tag('verified.png', ['title' => 'Player posted deck']);
            echo "<td>{$img}</td>";
        } else {
            echo '<td> </td>';
        }
        if ($adjustment != null) {
            echo "<td style=\"text-align: center;\"> <input type=\"text\" style=\"width: 50px;\" name=\"adjustments[{$name}]\" value=\"{$adjustment['adjustment']}\" /></td>";
            echo "<td><input class=\"inputbox\" type=\"text\" style=\"width: 400px;\" name=\"reasons[{$name}]\" value=\"{$adjustment['reason']}\" /></td>";
        } else {
            echo "<td style=\"text-align: center;\"><input class=\"inputbox\" type=\"text\" style=\"width: 50px;\" name=\"adjustments[{$name}]\" value=\"\" /></td>";
            echo "<td><input class=\"inputbox\" type=\"text\" style=\"width: 400px;\" name=\"reasons[{$name}]\" value=\"\" /></td>";
        }
        echo '</tr>';
    }
    echo '<tr><td colspan="3" class="buttons"> ';
    echo '<input class="inputbutton" type="submit" name="mode" value="Update Adjustments" />';
    echo '</td></tr></table></form>';
}

function printUnverifiedPlayerCell($match, $playername)
{
    global $drop_icon;
    $dropped = $match->playerDropped($playername);
    if ($dropped) {
        echo "<td>{$drop_icon}";
    } else {
        echo "<td><input type=\"checkbox\" name=\"dropplayer[]\" value=\"{$playername}\" title='Drop $playername from the event'>";
    }
    if (($match->getPlayerWins($playername) > 0) || ($match->getPlayerLosses($playername) > 0)) {
        if ($match->getPlayerWins($playername) > $match->getPlayerLosses($playername)) {
            $matchresult = 'W ';
        } else {
            $matchresult = 'L ';
        }
        if (($match->getPlayerWins($playername) == 1) && ($match->getPlayerLosses($playername) == 1)) {
            echo "<span class=\"match_{$match->verification}\">{$playername}</span> (Draw)</td>";
        } else {
            echo "<span class=\"match_{$match->verification}\">{$playername}</span> ({$matchresult}{$match->getPlayerWins($playername)}-{$match->getPlayerLosses($playername)})</td>";
        }
    } else {
        echo "{$playername}</td>";
    }
}

function matchList($event)
{
    global $drop_icon;
    $matches = $event->getMatches();
    // Prevent warnings in php output.  TODO: make this not needed.
    if (!isset($_POST['newmatchround'])) {
        $_POST['newmatchround'] = '';
    }

    echo '<p style="text-align: center"><b>Match List</b><br />';
    echo '<i>* denotes a playoff/finals match.</i><br />';
    echo '<i>To drop a player while entering match results, select the';
    echo ' check box next to the players name.</i></p>';
    // Quick links to rounds
    echo '<p style="text-align: center">';
    for ($r = 1; $r <= $event->current_round; $r++) {
        echo "<a href=\"event.php?view=match&name={$event->name}#round-{$r}\">Round {$r}</a> ";
    }
    echo '</p>';
    // Start a new form
    echo '<form action="event.php" method="post" enctype="multipart/form-data">';
    echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\">";
    echo "<input type=\"hidden\" name=\"eventname\" value=\"{$event->name}\">";
    echo '<input type="hidden" name="view" value="match">';
    echo '<table align="center" style="border-width: 0px;">';
    if (count($matches) > 0) {
        echo '<tr class="lefted"><th style="text-align: center; padding-right: 10px;">Round</th><th>Player A</th>';
        echo '<th>Result</th>';
        echo '<th>Player B</th>';
        echo '<th style="text-align: center;">Delete</th></tr>';
    } else {
        echo '<tr><td align="center" colspan="5"><i>';
        echo 'There are no matches listed for this event.</td></tr>';
    }
    $first = 1;
    $rndadd = 0;
    $thisround = 0;
    $ezypaste = '';
    $playersInMatches = [];
    foreach ($matches as $match) {
        if ($first && $match->timing == 1) {
            $rndadd = $match->rounds;
        }
        $first = 0;
        // add final round to main round if in extra rounds to keep round correct
        if ($match->timing == 2) {
            $printrnd = $match->round + $rndadd;
        } else {
            $printrnd = $match->round;
        }
        $printplr = $match->getWinner();
        if (is_null($printplr)) {
            $printplr = 'Database Error';
        }
        $star = ($match->timing > 1) ? '*' : '';
        if ($printrnd != $thisround) {
            $thisround = $printrnd;
            $playersInMatches = [];
            $extraRoundTitle = '';
            if ($match->timing > 1) {
                $extraRoundTitle = "(Finals Round {$match->round})";
            }
            $ezypaste = "Pairings for Round {$thisround} $extraRoundTitle<br />";
            echo "<tr><td class=\"box\" align=\"center\" colspan=\"7\" style=\"background-color: Grey;color: Black\"> <a name=\"round-{$thisround}\"></a>ROUND {$thisround} {$extraRoundTitle} </td></tr>";
        }
        echo "<tr><td align=\"center\">$printrnd$star</td>";
        $playersInMatches[] = $match->playera;
        $playersInMatches[] = $match->playerb;
        if (strcasecmp($match->verification, 'verified') != 0 && $event->finalized == 0) {
            $ezypaste .= "{$match->playera} vs. {$match->playerb}<br />";
            printUnverifiedPlayerCell($match, $match->playera);
            echo '<td>';
            echo "<input type=\"hidden\" name=\"hostupdatesmatches[]\" value=\"{$match->id}\">";
            resultDropMenu('matchresult[]');
            echo '</td>';
            printUnverifiedPlayerCell($match, $match->playerb);
        } else {
            $playerawins = $match->getPlayerWins($match->playera);
            $playerbwins = $match->getPlayerWins($match->playerb);
            $playeradropflag = $match->playerDropped($match->playera) ? $drop_icon : '';
            $playerbdropflag = $match->playerDropped($match->playerb) ? $drop_icon : '';
            echo "<td class=\"match_{$match->verification}\">{$match->playera}</td>";
            if ($match->playera == $match->playerb) {
                $ezypaste .= "{$match->playera} has the BYE<br />";
                echo '<td>BYE</td>';
                echo '<td></td>';
            } elseif (($match->getPlayerWins($match->playera) == 1) && ($match->getPlayerWins($match->playerb) == 1)) {
                echo "<td>{$playeradropflag} Draw {$playerbdropflag}</td>";
                $ezypaste .= "{$match->playera} {$playerawins}-{$playerbwins} {$match->playerb}<br />";
                echo "<td class=\"match_{$match->verification}\">{$match->playerb}</td>";
            } else {
                echo "<td>{$playeradropflag} {$playerawins}-{$playerbwins} {$playerbdropflag}</td>";
                $ezypaste .= "{$match->playera} {$playerawins}-{$playerbwins} {$match->playerb}<br />";
                echo "<td class=\"match_{$match->verification}\">{$match->playerb}</td>";
            }
        }
        echo '<td align="center">';
        echo "<input type=\"checkbox\" name=\"matchdelete[]\" title='Delete this pairing' ";
        echo "value=\"{$match->id}\"></td></tr>";
    }
    $ezypaste .= 'Good luck everyone!<br />';
    echo '<tr><td>&nbsp;</td></tr>';
    if ($event->active) {
        echo '<tr><td align="center" colspan="7"><b>Add Pairing</b></td></tr>';
        echo "<input type=\"hidden\" name=\"newmatchround\" value=\"{$event->current_round}\">";
        echo '<input type="hidden" name="newmatchresult" value="P">';
        echo '<tr><td align="center" colspan="7">';
        playerDropMenu($event, 'A', $event->active);
        echo ' vs ';
        playerDropMenu($event, 'B', $event->active);
        echo '</td></tr>';
        echo '<tr><td>&nbsp;</td></tr>';
        echo '<tr><td align="center" colspan="7"><b>Award Bye</b></td></tr>';
        echo '<tr><td align="center" colspan="7">';
        playerByeMenu($event);
        echo '</td></tr>';
    } else {
        echo '<tr><td align="center" colspan="7">';
        echo '<b>Add a Match</b></td></tr>';
        echo '<tr><td align="center" colspan="7">';
        roundDropMenu($event, $_POST['newmatchround']);
        playerDropMenu($event, 'A', $event->active);
        resultDropMenu('newmatchresult');
        playerDropMenu($event, 'B', $event->active);
        echo '</td></tr>';
    }
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr><td align="center" colspan="7">';
    echo '<input class="inputbutton" type="submit" name="mode" value="Update Match Listing">';
    echo '</td></tr>';
    echo '</form></table>';
    if (!$event->finalized) {
        echo '<p>Paste stuff:<br />';
        echo "<code>{$ezypaste}</code></p>";
    }

    if ($event->current_round > $event->mainrounds) {
        $structure = $event->finalstruct;
    } else {
        $structure = $event->mainstruct;
    }

    if ($structure == 'League') {
        //echo "<center> <b> Players added after the event has started will receive 0 points for any rounds already started and be paired when the next round begins</center></b>";
        echo '<table style="border-width: 0px" align="center">';
        echo '<tr><td>';
        echo '<tr><td colspan="2" align="center">';
        echo '<form action="event.php" method="post">';
        echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\" />";
        echo '<input type="hidden" name="view" value="reg">';
        echo '<input type="submit" name="mode" value="End Current League Round" />';
        echo '</tr></td>';
        echo '</table>';
    }
}

function standingsList($event)
{
    Standings::printEventStandings($event->name, Player::loginName());
}

function medalList($event)
{
    $def1 = '';
    $def2 = '';
    $def4 = ['', ''];
    $def8 = ['', '', '', ''];

    $finalists = $event->getFinalists();

    $t4used = 0;
    $t8used = 0;
    foreach ($finalists as $finalist) {
        if ($finalist['medal'] == '1st') {
            $def1 = $finalist['player'];
        } elseif ($finalist['medal'] == '2nd') {
            $def2 = $finalist['player'];
        } elseif ($finalist['medal'] == 't4') {
            $def4[$t4used++] = $finalist['player'];
        } elseif ($finalist['medal'] == 't8') {
            $def8[$t8used++] = $finalist['player'];
        }
    }
    // Start a new form
    echo '<form action="event.php" method="post" ';
    echo 'enctype="multipart/form-data">';
    echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\">";
    echo '<table style="border-width: 0px" align="center">';

    echo '<tr><td colspan="2">';
    echo '<input type="hidden" name="view" value="reg">';
    echo '<table align="center" style="border-width: 0px;">';
    echo '<tr><td align="center" colspan="2"><b>Medals</td></tr>';
    echo '<tr><td colspan="2" width=200>&nbsp;</td></tr>';
    echo '<tr><td align="center"><b>Medal</td>';
    echo '<td align="center"><b>Player</td></tr>';
    echo '<tr><td align="center">';
    echo image_tag('1st.png').'</td>';
    echo '<td align="center">';
    playerDropMenu($event, '1', $event->active, $def1);
    echo '</td></tr>';
    echo '<tr><td align="center">';
    echo image_tag('2nd.png').'</td>';
    echo '<td align="center">';
    playerDropMenu($event, '2', $event->active, $def2);
    echo '</td></tr>';
    for ($i = 3; $i < 5; $i++) {
        echo '<tr><td align="center">';
        echo image_tag('t4.png').'</td>';
        echo '<td align="center">';
        playerDropMenu($event, $i, $event->active, $def4[$i - 3]);
        echo '</td></tr>';
    }
    for ($i = 5; $i < 9; $i++) {
        echo '<tr><td align="center">';
        echo image_tag('t8.png').'</td>';
        echo '<td align="center">';
        playerDropMenu($event, $i, $event->active, $def8[$i - 5]);
        echo '</td></tr>';
    }
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr><td colspan="2" align="center">';
    echo '<input class="inputbutton" type="submit" name="mode" value="Update Medals">';
    echo '</form>';
    echo '</td></tr>';
    echo '</table>';
    echo '</td></tr>';
    echo '</table>';
}

function kValueDropMenu($kvalue)
{
    if (strcmp($kvalue, '') == 0) {
        $kvalue = -1;
    }
    $names = [''      => '- K-Value -', 8 => 'Casual (Alt Event)', 16 => 'Regular (less than 24 players)',
                 24   => 'Large (24 or more players)', 32 => 'Championship', ];
    print_select_input('K-Value', 'kvalue', $names, $kvalue);
}

function monthDropMenu($month)
{
    if (strcmp($month, '') == 0) {
        $month = -1;
    }
    $names = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December', ];
    echo '<select class="inputbox" name="month">';
    echo '<option value="">- Month -</option>';
    for ($m = 1; $m <= 12; $m++) {
        $selStr = ($month == $m) ? 'selected' : '';
        echo "<option value=\"$m\" $selStr>{$names[$m - 1]}</option>";
    }
    echo '</select>';
}

function structDropMenu($field, $def)
{
    $names = ['Swiss (Blossom)', 'Swiss', 'Single Elimination', /*"Round Robin",*/ 'League'];
    echo "<select class=\"inputbox\" name=\"$field\">";
    echo '<option value="">- Structure -</option>';
    for ($i = 0; $i < count($names); $i++) {
        $selStr = (strcmp($def, $names[$i]) == 0) ? 'selected' : '';
        echo "<option value=\"{$names[$i]}\" $selStr>{$names[$i]}</option>";
    }
    echo '</select>';
}

function noEvent($event)
{
    return "The requested event \"$event\" could not be found.";
}

function insertEvent()
{
    if (!isset($_POST['naming'])) {
        $_POST['naming'] = '';
    }

    if (!isset($_POST['prereg_allowed'])) {
        $_POST['prereg_allowed'] = 0;
    }

    if (!isset($_POST['pkonly'])) {
        $_POST['pkonly'] = 0;
    }

    if (!isset($_POST['player_reportable'])) {
        $_POST['player_reportable'] = 0;
    }

    if (!isset($_POST['late_entry_limit'])) {
        $_POST['late_entry_limit'] = 0;
    }

    $event = Event::CreateEvent($_POST['year'], $_POST['month'], $_POST['day'], $_POST['hour'], $_POST['naming'], $_POST['name'],
                       $_POST['format'], $_POST['host'], $_POST['cohost'], $_POST['kvalue'], $_POST['series'], $_POST['season'],
                       $_POST['number'], $_POST['threadurl'], $_POST['metaurl'], $_POST['reporturl'], $_POST['prereg_allowed'],
                       $_POST['pkonly'], $_POST['player_reportable'], $_POST['late_entry_limit'],
                       $_POST['mainrounds'], $_POST['mainstruct'], $_POST['finalrounds'], $_POST['finalstruct']);

    return $event;
}

function updateEvent()
{
    if (!isset($_POST['pkonly'])) {
        $_POST['pkonly'] = 0;
    }
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

    $event = new Event($_POST['name']);
    $event->start = "{$_POST['year']}-{$_POST['month']}-{$_POST['day']} {$_POST['hour']}:00";
    $event->finalized = $_POST['finalized'];
    $event->active = $_POST['active'];
    $event->current_round = $_POST['newmatchround'];
    $event->prereg_allowed = $_POST['prereg_allowed'];
    $event->pkonly = $_POST['pkonly'];
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
        $_POST['mainstruct'] = 'Swiss (Blossom)';
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

    $event->save();

    return $event;
}

function trophyField($event)
{
    if ($event->hastrophy) {
        echo '<tr><td>&nbsp;</td></tr>';
        echo '<tr><td colspan="2" align="center">';
        echo "<img src=\"displayTrophy.php?event={$event->name}\" alt=\"Trophy\" /></td></tr>";
    }
    echo '<tr><th>Trophy Image</th><td>';
    echo '<input  id="trophy" class="inputbox" type="file" name="trophy">&nbsp';
    echo '<input class="inputbutton" type="submit" name="mode" value="Upload Trophy">';
    echo '</tr>';
}

function insertTrophy()
{
    if ($_FILES['trophy']['size'] > 0) {
        $file = $_FILES['trophy'];
        $event = $_POST['name'];

        $name = $file['name'];
        $tmp = $file['tmp_name'];
        $size = $file['size'];
        $type = $file['type'];

        $f = fopen($tmp, 'rb');

        $db = Database::getPDOConnection();
        $stmt = $db->prepare('DELETE FROM trophies WHERE event = ?');
        $stmt->bindParam(1, $event, PDO::PARAM_STR);
        $stmt->execute() or die($stmt->errorCode());

        $stmt = $db->prepare('INSERT INTO trophies(event, size, type, image)
      VALUES(?, ?, ?, ?)');
        $stmt->bindParam(1, $event, PDO::PARAM_STR);
        $stmt->bindParam(2, $size, PDO::PARAM_INT);
        $stmt->bindParam(3, $type, PDO::PARAM_STR);
        $stmt->bindParam(4, $f, PDO::PARAM_LOB);
        $stmt->execute() or die($stmt->errorCode());
        fclose($f);

        return true;
    }
}

function medalDropMenu()
{
    echo '<select name="medal">';
    echo '<option value="dot">- Medal -</option>';
    echo '<option value="dot">No Medal</option>';
    echo '<option value="1st">1st Place</option>';
    echo '<option value="2nd">2nd Place</option>';
    echo '<option value="t4">Top 4</option>';
    echo '<option value="t8">Top 8</option>';
    echo '</select>';
}

function playerByeMenu($event, $def = "\n")
{
    $playernames = $event->getActiveRegisteredPlayers();
    echo '<select class="inputbox" name="newbyeplayer">';
    if (strcmp("\n", $def) == 0) {
        echo '<option value="">- Bye Player -</option>';
    } else {
        echo '<option value="">- None -</option>';
    }
    foreach ($playernames as $player) {
        $selstr = (strcmp($player, $def) == 0) ? 'selected' : '';
        echo "<option value=\"{$player}\" $selstr>";
        echo "{$player}</option>";
    }
    echo '</select>';
}

function playerDropMenu($event, $letter, $active, $def = "\n")
{
    // If $acive is set to one, function assumes event is active
    // and will only use registered players who are still active
    // (ie. who haven't dropped)
    // if $active is not set, function will return all registered
    // players
    if ($active) {
        $playernames = $event->getActiveRegisteredPlayers();
    } else {
        $playernames = $event->getRegisteredPlayers();
    }

    echo "<select class=\"inputbox\" name=\"newmatchplayer$letter\">";
    if (strcmp("\n", $def) == 0) {
        echo "<option value=\"\">- Player $letter -</option>";
    } else {
        echo '<option value="">- None -</option>';
    }
    foreach ($playernames as $player) {
        $selstr = (strcmp($player, $def) == 0) ? 'selected' : '';
        echo "<option value=\"{$player}\" $selstr>";
        echo "{$player}</option>";
    }
    echo '</select>';
}

function roundDropMenu($event, $selected)
{
    echo '<select class="inputbox" name="newmatchround">';
    echo '<option value="">- Round -</option>';
    for ($r = 1; $r <= ($event->mainrounds + $event->finalrounds); $r++) {
        $star = ($r > $event->mainrounds) ? '*' : '';
        echo "<option value=\"$r\"";
        if ($selected == $r) {
            echo ' selected';
        }
        echo ">$r$star</option>";
    }
    echo '</select>';
}

function resultDropMenu($name = 'newmatchresult', $extra_options = [])
{
    echo "<select class=\"inputbox\" name=\"{$name}\">";
    echo '<option value="">- Result -</option>';
    echo '<option value="2-0">2-0</option>';
    echo '<option value="2-1">2-1</option>';
    echo '<option value="1-2">1-2</option>';
    echo '<option value="0-2">0-2</option>';
    echo '<option value="D">Draw</option>';
    foreach ($extra_options as $value => $text) {
        echo "<option value=\"{$value}\">{$text}</option>";
    }
    echo '</select>';
}

function controlPanel($event, $cur = '')
{
    $name = $event->name;
    echo '<tr><td colspan="2" align="center">';
    echo '<a href="event.php?name='.rawurlencode($name).'&view=settings">Event Settings</a>';
    echo ' | <a href="event.php?name='.rawurlencode($name).'&view=reg">Registration</a>';
    echo ' | <a href="event.php?name='.rawurlencode($name).'&view=match">Match Listing</a>';
    echo ' | <a href="event.php?name='.rawurlencode($name).'&view=standings">Standings</a>';
    echo ' | <a href="event.php?name='.rawurlencode($name).'&view=medal">Medals</a>';
    // echo " | <a href=\"event.php?name=$name&view=autoinput\">Auto-Input</a>"; hiding until I fix the auto-input feature
    echo ' | <a href="event.php?name='.rawurlencode($name).'&view=fileinput">DCI-R File Input</a>';
    echo ' | <a href="event.php?name='.rawurlencode($name).'&view=points_adj">Season Points Adj.</a>';
    echo ' | <a href="event.php?name='.rawurlencode($name).'&view=reports">Reports</a>';
    echo '</td></tr>';
}

function updateReg()
{
    $event = new Event($_POST['name']);

    if (isset($_POST['delentries'])) {
        foreach ($_POST['delentries'] as $playername) {
            $event->removeEntry($playername);
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

    if (isset($_POST['earned_byes'])) {
        foreach ($_POST['earned_byes'] as $playername) {
            $entry = new Entry($event->name, $playername);
            $entry->add_earned_byes($playername, $playername[1]);
        }
    }
}

function updateMatches()
{
    $event = new Event($_POST['name']);
    if (isset($_POST['matchdelete'])) {
        foreach ($_POST['matchdelete'] as $matchid) {
            Match::destroy($matchid);
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
                Match::saveReport($resultForA, $matchid, 'a');
                Match::saveReport($resultForB, $matchid, 'b');
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

    if (strcmp($pA, '') != 0 && strcmp($pB, '') != 0
    && strcmp($res, '') != 0 && strcmp($rnd, '') != 0) {
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

function updateMedals()
{
    $name = $_POST['name'];
    $event = new Event($_POST['name']);

    $winner = $_POST['newmatchplayer1'];
    $second = $_POST['newmatchplayer2'];
    $t4 = [$_POST['newmatchplayer3'], $_POST['newmatchplayer4']];
    $t8 = [$_POST['newmatchplayer5'],  $_POST['newmatchplayer6'],  $_POST['newmatchplayer7'],  $_POST['newmatchplayer8']];

    $event->setFinalists($winner, $second, $t4, $t8);
}

function updateAdjustments()
{
    $name = $_POST['name'];
    $event = new Event($_POST['name']);

    $adjustments = $_POST['adjustments'];
    $reasons = $_POST['reasons'];

    foreach ($adjustments as $name => $points) {
        if ($points != '') {
            $event->setSeasonPointAdjustment($name, $points, $reasons[$name]);
        }
    }
}

function autoInputForm($event)
{
    // Start a new form
    echo '<form action="event.php" method="post" ';
    echo 'enctype="multipart/form-data">';
    echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\">";
    echo '<table style="border-width: 0px" align="center">';

    echo '<tr><td colspan="2 align="center">';
    echo '<table align="center" style="border-width: 0px;">';
    $totalrnds = 0;
    foreach ($event->getSubevents() as $subevent) {
        if (strcmp($subevent->type, 'Single Elimination') == 0) {
            for ($rnd = 1; $rnd <= $subevent->rounds; $rnd++) {
                $rem = pow(2, $subevent->rounds - $rnd + 1);
                echo '<tr><td colspan="2" align="center"><b>';
                echo "Round of $rem Pairings</td></tr>";
                echo '<tr><td colspan="2" align="center">';
                echo '<textarea class="inputbox" name="finals[]" rows="10" cols="60">';
                echo '</textarea></td></tr>';
                echo '<tr><td>&nbsp;</td></tr>';
            }
        } else {
            for ($rnd = 1; $rnd <= $subevent->rounds; $rnd++) {
                $printrnd = $rnd + $totalrnds;
                $pairfield = $printrnd.'p';
                $standfield = $printrnd.'s';
                echo '<tr><td colspan="2" align="center"><b>';
                echo "Round $printrnd Pairings</td></tr>";
                echo '<tr><td colspan="2" align="center">';
                echo '<textarea class="inputbox" name="pairings[]" rows="10" cols="60">';
                echo '</textarea></td></tr>';
                echo '<tr><td>&nbsp;</td></tr>';
                if ($rnd > 1) {
                    echo '<tr><td colspan="2" align="center"><b>';
                    echo "Round $printrnd Standings</td></tr>";
                    echo '<tr><td colspan="2" align="center">';
                    echo '<textarea class="inputbox" name="standings[]" ';
                    echo 'rows="10" cols="60">';
                    echo '</textarea></td></tr>';
                    echo '<tr><td>&nbsp;</td></tr>';
                }
            }
            $totalrnds += $subevent->rounds;
        }
    }
    echo '<tr><td colspan="2" align="center"><b>';
    echo 'Event Champion</td></tr>';
    echo '<tr><td colspan="2" align="center">';
    stringField('champion', '', 20);
    echo '</td></tr>';
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr><td colspan="2" align="center">';
    echo '<input type="hidden" name="view" value="match">';
    echo '<input class="inputbutton" type="submit" name="mode" value="Auto-Input Event Data">';
    echo '</form>';
    echo '</td></tr>';
    echo '</table>';
    echo '</td></tr>';
    echo '</table>';
}

function autoInput()
{
    if (count($_POST['pairings']) == 0 ||
      strlen($_POST['pairings'][0]) == 0) {
        // No data.
        return;
    }
    $pairings = [];
    $standings = [];
    for ($rnd = 0; $rnd < count($_POST['pairings']); $rnd++) {
        $pairings[$rnd] = extractPairings($_POST['pairings'][$rnd]);
        if ($rnd == 0) {
            $standings[$rnd] = standFromPairs($_POST['pairings'][$rnd + 1]);
        } else {
            $testStr = rtrim($_POST['standings'][$rnd - 1]);
            if (strcmp($testStr, '') == 0) {
                $standings[$rnd] = standFromPairs($_POST['pairings'][$rnd + 1]);
            } else {
                $standings[$rnd] = extractStandings($_POST['standings'][$rnd - 1]);
            }
        }
    }
    $event = new Event($_POST['name']);
    $sid = $event->mainid;
    $onlyfirstround = true;
    for ($rnd = 1; $rnd < count($_POST['pairings']); $rnd++) {
        if (strlen($_POST['pairings'][$rnd]) > 0) {
            $onlyfirstround = false;
            break;
        }
    }
    if ($onlyfirstround) {
        for ($pair = 0; $pair < count($pairings[0]); $pair++) {
            $event->addPlayer($pairings[0][$pair][0]);
            $event->addPlayer($pairings[0][$pair][1]);
        }
        $byeplayer = extractBye($_POST['pairings'][0]);
        if ($byeplayer) {
            $event->addPlayer($byeplayer);
        }
        // There are no interesting matches to see, so let's go to the registration list
        $_POST['view'] = 'reg';

        return;
    }
    for ($rnd = 0; $rnd < count($pairings); $rnd++) {
        for ($pair = 0; $pair < count($pairings[$rnd]); $pair++) {
            $printrnd = $rnd + 1;
            $playerA = $pairings[$rnd][$pair][0];
            $playerB = $pairings[$rnd][$pair][1];
            $winner = 'D';
            if ($rnd == 0) {
                if (isset($standings[$rnd][$playerA]) &&
        $standings[$rnd][$playerA] > 1) {
                    $winner = 'A';
                }
                if (isset($standings[$rnd][$playerB]) &&
        $standings[$rnd][$playerB] > 1) {
                    $winner = 'B';
                }
            } else {
                if (isset($standings[$rnd][$playerA]) &&
        isset($standings[$rnd - 1][$playerA]) &&
        $standings[$rnd][$playerA] - $standings[$rnd - 1][$playerA] > 1) {
                    $winner = 'A';
                }
                if (isset($standings[$rnd][$playerB]) &&
        isset($standings[$rnd - 1][$playerB]) &&
        $standings[$rnd][$playerB] - $standings[$rnd - 1][$playerB] > 1) {
                    $winner = 'B';
                }
            }

            $event->addPlayer($playerA);
            $event->addPlayer($playerB);

            $playerAStanding = new Standings($event->name, $playerA);
            $playerBStanding = new Standings($event->name, $playerB);
            $event->addMatch($playerAStanding, $playerBStanding, $rnd + 1, $winner, 0, 0);
        }
    }
    $finals = [];
    for ($ndx = 0; $ndx < count($_POST['finals']); $ndx++) {
        $finals[$ndx] = extractFinals($_POST['finals'][$ndx]);
    }
    $fid = $event->finalid;
    $win = '';
    $sec = '';
    $t4 = [];
    $t8 = [];
    for ($ndx = 0; $ndx < count($finals); $ndx++) {
        for ($match = 0; $match < count($finals[$ndx]); $match += 2) {
            $playerA = $finals[$ndx][$match];
            $playerB = $finals[$ndx][$match + 1];
            $event->addPlayer($playerA);
            $event->addPlayer($playerB);
            if ($ndx < count($finals) - 1) {
                $winner = detwinner($playerA, $playerB, $finals[$ndx + 1]);
            } else {
                $winner = $_POST['champion'];
            }
            $res = 'D';
            if (strcmp($winner, $playerA) == 0) {
                $res = 'A';
            }
            if (strcmp($winner, $playerB) == 0) {
                $res = 'B';
            }
            $playerAStanding = new Standings($event->name, $playerA);
            $playerBStanding = new Standings($event->name, $playerB);
            $event->addMatch($playerAStanding, $playerBStanding, $ndx + 1 + $event->mainrounds, $res, 0, 0);
            $loser = (strcmp($winner, $playerA) == 0) ? $playerB : $playerA;
            if ($ndx == count($finals) - 1) {
                $win = $winner;
                $sec = $loser;
            } elseif ($ndx == count($finals) - 2) {
                $t4[] = $loser;
            } elseif ($ndx == count($finals) - 3) {
                $t8[] = $loser;
            }
        }
    }
    $event->setFinalists($win, $sec, $t4, $t8);
}

function extractPairings($text)
{
    $pairings = [];
    $lines = explode("\n", $text);
    $loc = 0;
    for ($ndx = 0; $ndx < count($lines); $ndx++) {
        if (preg_match("/^\s*[0-9]+\s+([0-9]+\s+)?([0-9a-z_.\- ]+),.*\s+[0-9]+\s+([0-9a-z_.\- ]+),/i",
      $lines[$ndx], $m)) {
            $pairings[$loc] = [$m[2], $m[3]];
            $loc++;
        }
    }

    return $pairings;
}

function extractBye($text)
{
    $lines = explode("\n", $text);
    $loc = 0;
    for ($ndx = 0; $ndx < count($lines); $ndx++) {
        if (preg_match("/^\s*[0-9]+\s+([0-9]+\s+)?([0-9a-z_.\- ]+),.*\s+\* BYE \*/i",
      $lines[$ndx], $m)) {
            return $m[2];
        }
    }
}

function extractStandings($text)
{
    $standings = [];
    $lines = explode("\n", $text);
    for ($ndx = 0; $ndx < count($lines); $ndx++) {
        if (preg_match("/^\s*[0-9]+\s+([0-9]+\s+)?([0-9a-z_.\- ]+),.*\s+([0-9]+)\s+/i",
    $lines[$ndx], $m)) {
            $standings[$m[2]] = $m[3];
        }
    }

    return $standings;
}

function standFromPairs($text)
{
    $standings = [];
    $lines = explode("\n", $text);
    for ($ndx = 0; $ndx < count($lines); $ndx++) {
        if (preg_match("/^\s*[0-9]+\s+([0-9]+\s+)?([0-9a-z_.\- ]+),.*\s+([0-9]+)-([0-9]+)\s+[0-9]+\s+([0-9a-z_.\- ]+),/i", $lines[$ndx], $m)) {
            $standings[$m[2]] = $m[3];
            $standings[$m[5]] = $m[4];
        }
    }

    return $standings;
}

function extractFinals($text)
{
    $finals = [];
    $lines = explode("\n", $text);
    $loc = 0;
    for ($ndx = 0; $ndx < count($lines); $ndx++) {
        if (preg_match("/[\t ]+([0-9a-z_.\- ]+),/i", $lines[$ndx], $m)) {
            $finals[$loc] = $m[1];
            $loc++;
        }
    }

    return $finals;
}

function detwinner($a, $b, $next)
{
    $ret = 'No Winner';
    for ($ndx = 0; $ndx < count($next); $ndx++) {
        if (strcmp($a, $next[$ndx]) == 0) {
            $ret = $a;
        }
        if (strcmp($b, $next[$ndx]) == 0) {
            $ret = $b;
        }
    }

    return $ret;
}

function authFailed()
{
    echo 'You are not permitted to make that change. Please contact the ';
    echo 'event host to modify this event. If you <b>are</b> the event host, ';
    echo 'or feel that you should have privilege to modify this event, you ';
    echo 'should contact Dabil via the Pauper Krew forums.<br /><br />';
}

function fileInputForm($event)
{
    // Start a new form
    echo '<form action="event.php" method="post" ';
    echo 'enctype="multipart/form-data">';
    echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\">";
    echo '<h3><center>DCI version 2</center></h3>';
    echo "<table style=\"border-width: 0px;\" align=\"center\">\n";
    echo "<tr><td><b>*delt.dat</td><td>\n";
    echo "<input class=\"inputbox\" type=\"file\" name=\"delt\" id=\"delt\" size=40></td></tr>\n";
    echo "<tr><td><b>*kamp.dat&nbsp;</td><td>\n";
    echo "<input class=\"inputbox\" type=\"file\" name=\"kamp\" id=\"kamp\" size=40></td></tr>\n";
    echo "<tr><td><b>*elim.dat</td><td>\n";
    echo "<input class=\"inputbox\" type=\"file\" name=\"elim\" id=\"elim\" size=40></td></tr>\n";
    echo "<tr><td>&nbsp;</td></tr>\n";
    echo "<tr><td colspan=2 align=\"center\">\n";
    echo "<input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"Parse DCI Files\">\n";
    echo "</form>\n";
    echo "</td></tr></table>\n";
}

function file3InputForm($event)
{
    // Start a new form
    echo '<form action="event.php" method="post" ';
    echo 'enctype="multipart/form-data">';
    echo "<input type=\"hidden\" name=\"name\" value=\"{$event->name}\">";
    echo '<h3><center>DCI version 3</center></h3>';
    echo "<table style=\"border-width: 0px;\" align=\"center\">\n";
    echo "<tr><td><b>*302.dat</td><td>\n";
    echo "<input type=\"file\" name=\"302\" id=\"302\" size=40></td></tr>\n";
    echo "<tr><td><b>*305.dat&nbsp;</td><td>\n";
    echo "<input type=\"file\" name=\"305\" id=\"305\" size=40></td></tr>\n";
    echo "<tr><td colspan=2 align=\"center\">\n";
    echo "<input type=\"submit\" name=\"mode\" value=\"Parse DCIv3 Files\">\n";
    echo "</form>\n";
    echo "</td></tr></table>\n";
}

function dciInput()
{
    $reg = [];
    if ($_FILES['delt']['size'] > 0) {
        $fileptr = fopen($_FILES['delt']['tmp_name'], 'r');
        $deltcontent = fread($fileptr, filesize($_FILES['delt']['tmp_name']));
        fclose($fileptr);
        $reg = dciregister($deltcontent);
    }
    if ($_FILES['kamp']['size'] > 0 && count($reg) > 0) {
        $fileptr = fopen($_FILES['kamp']['tmp_name'], 'r');
        $kampcontent = fread($fileptr, filesize($_FILES['kamp']['tmp_name']));
        fclose($fileptr);
        dciinputmatches($reg, $kampcontent);
    }
    if ($_FILES['elim']['size'] > 0 && count($reg) > 0) {
        $fileptr = fopen($_FILES['elim']['tmp_name'], 'r');
        $elimcontent = fread($fileptr, filesize($_FILES['elim']['tmp_name']));
        fclose($fileptr);
        dciinputplayoffs($reg, $elimcontent);
    }
}

function dciregister($data)
{
    $event = new Event($_POST['name']);
    echo "Registering DCI-R players for {$event->name}.<br />";
    $data = preg_replace("/
\n/", "\n", $data);
    $lines = explode("\n", $data);
    $ret = [];
    for ($ndx = 0; $ndx < count($lines); $ndx++) {
        $tokens = explode(',', $lines[$ndx]);
        if (preg_match('/"(.*)"/', $tokens[3], $matches)) {
            $didadd = $event->addPlayer($matches[1]);
            if ($didadd) {
                echo "Adding player: {$matches[1]}.<br />";
            } else {
                echo "{$matches[1]} could not be added.<br />";
            }
            $ret[] = $matches[1];
        }
    }

    return $ret;
}

function dciinputmatches($reg, $data)
{
    $event = new Event($_POST['name']);
    echo "Adding matches to {$event->name}.<br />";
    $data = preg_replace("/
\n/", "\n", $data);
    $lines = explode("\n", $data);
    for ($table = 0; $table < count($lines) / 6; $table++) {
        $offset = $table * 6;
        $numberofrounds = explode(',', $lines[$offset]);
        $playeraresults = explode(',', $lines[$offset + 1]);
        $playerbresults = explode(',', $lines[$offset + 2]);
        $playerawins = explode(',', $lines[$offset + 3]);
        $playerbwins = explode(',', $lines[$offset + 4]);
        for ($round = 1; $round <= count($numberofrounds); $round++) {
            if ($numberofrounds[$round - 1] != 0) {
                $playera = Player::findByName($reg[$playeraresults[$round - 1] - 1]); // find by name returns player object! not just a name!
        $playerb = Player::findByName($reg[$playerbresults[$round - 1] - 1]); // may want to write a custom function later that just returns name
        // should probably do a check to for NULL here for to see if player object
        // was in fact returned for playera and playerb, just in case the dciregister
        // function above failed to register
        $result = 'D';
                // need to do a check for a bye here
                if ($playerawins[$round - 1] > $playerbwins[$round - 1]) {
                    $result = 'A';
                } // player A wins
                if ($playerbwins[$round - 1] > $playerawins[$round - 1]) {
                    $result = 'B';
                } // player B wins
                echo "{$playera->name} vs {$playerb->name} in Round: {$round} and ";
                if ($result == 'A') {
                    echo "{$playera->name} wins {$playerawins[$round - 1]} - {$playeralosses[$round - 1]}<br />";
                }
                if ($result == 'B') {
                    echo "{$playerb->name} wins {$playerbwins[$round - 1]} - {$playerblosses[$round - 1]}<br />";
                }
                if ($result == 'D') {
                    echo ' match is a draw<br />';
                }
                $playerAStanding = new Standings($event->name, $playera->name);
                $playerBStanding = new Standings($event->name, $playerb->name);
                $event->addMatch($playerAStanding, $playerBStanding, $round, $result, $playerawins[$round - 1], $playerbwins[$round - 1]);
            }
        }
    }
}

function dciinputplayoffs($reg, $data)
{
    $event = new Event($_POST['name']);
    $data = preg_replace("/
\n/", "\n", $data);
    $lines = explode("\n", $data);
    $ntables = $lines[0];
    $nrounds = log($ntables, 2);
    for ($rnd = 1; $rnd <= $nrounds; $rnd++) {
        $ngames = pow(2, $nrounds - $rnd);
        for ($game = 0; $game < $ngames; $game++) {
            $offset = 2 + $game * 24;
            $playera = $lines[$offset + ($rnd - 1) * 3];
            $pbl = $offset + ($rnd - 1) * 3 + 12;
            $playerb = $lines[$pbl];
            $winner = $lines[(($pbl + 1) + 3 * $rnd - 6) / 2 - 1];
            $pa = $reg[$playera - 1];
            $pb = $reg[$playerb - 1];
            $res = 'D';
            if ($winner == $playera) {
                $res = 'A';
            }
            if ($winner == $playerb) {
                $res = 'B';
            }
            $playerAStanding = new Standings($event->name, $pa);
            $playerBStanding = new Standings($event->name, $pb);
            $event->addMatch($playerAStanding, $playerBStanding, $rnd + $event->mainrounds, $res, 0, 0);
        }
    }
    $event->assignTropiesFromMatches();
}

function dci3Input()
{
    $reg = [];
    if ($_FILES['302']['size'] > 0) {
        $fileptr = fopen($_FILES['302']['tmp_name'], 'r');
        $regfilecontent = fread($fileptr, filesize($_FILES['302']['tmp_name']));
        fclose($fileptr);
        $reg = dci3register($regfilecontent);
    }
    if ($_FILES['305']['size'] > 0) {
        $fileptr = fopen($_FILES['305']['tmp_name'], 'r');
        $matchfilecontent = fread($fileptr, filesize($_FILES['305']['tmp_name']));
        fclose($fileptr);
        dci3makematches($matchfilecontent, $reg);
    }
}

function dci3register($data)
{
    $event = new Event($_POST['name']);
    $result = [];
    $data = preg_replace("/
\n/", "\n", $data);
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        $table = explode("\t", $line);
        if (count($table) > 5) {
            $playernumber = $table[0];
            $playername = $table[5];
            $result[$playernumber] = $playername;
            $event->addPlayer($playername);
        }
    }

    return $result;
}

function dci3makematches($data, $regmap)
{
    $event = new Event($_POST['name']);
    $result = [];
    $data = preg_replace("/
\n/", "\n", $data);
    $lines = explode("\n", $data);
    $playernumber = 1;
    $lastroundnum = 0;
    $alreadyin = [];
    foreach ($lines as $line) {
        $table = explode(',', $line);
        var_dump($table);
        $roundnum = $table[0];
        $opponentnum = $table[1];
        $win = $table[2];
        if ($roundnum < $lastroundnum) {
            $playernumber++;
        }
        if (!isset($alreadyin["{$opponentnum}-{$playernumber}-{$roundnum}"])) {
            // Match hasn't been added yet
            $res = 'D';
            if ($win == 3) {
                $res = 'A';
            } elseif ($win == 0) {
                $res = 'B';
            }
            $playerAStanding = new Standings($event->name, $regmap[$playernumber]);
            $playerBStanding = new Standings($event->name, $regmap[$opponentnum]);
            $event->addMatch($playerAStanding, $playerBStanding, $roundnum, $res, 0, 0);
            $alreadyin["{$playernumber}-{$opponentnum}-{$roundnum}"] = 1;
        }
        $lastroundnum = $roundnum;
    }
    $event->assignTropiesFromMatches();
}

?>
