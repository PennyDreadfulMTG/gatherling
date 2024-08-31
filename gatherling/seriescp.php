<?php

use Gatherling\Deck;
use Gatherling\Player;
use Gatherling\Series;

include 'lib.php';
include 'lib_form_helper.php';

if (!Player::isLoggedIn()) {
    linkToLogin('Series Control Panel');
}

$hasError = false;
$errormsg = '';

print_header('Series Control Panel');
?>

<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Series Control Panel </div>

<?php
do_page();
?>
<div class="clear"></div>
</div></div>

<?php print_footer(); ?>

<?php

function do_page()
{
    $player_series = Player::getSessionPlayer()->organizersSeries();
    if (count($player_series) == 0) {
        printNoSeries();

        return;
    }
    if (isset($_POST['series'])) {
        $_GET['series'] = $_POST['series'];
    }
    if (!isset($_GET['series'])) {
        $_GET['series'] = $player_series[0];
    }
    $active_series_name = $_GET['series'];

    handleActions();

    $view = 'settings';

    if (isset($_GET['view'])) {
        $view = $_GET['view'];
    }
    if (isset($_POST['view'])) {
        $view = $_POST['view'];
    }

    if ($view != 'no_view') {
        if (count($player_series) > 1) {
            printOrganizerSelect($player_series, $active_series_name);
        } else {
            echo "<center> Managing {$active_series_name} </center>";
        }
    }
    $active_series = new Series($active_series_name);
    printError();

    seriesCPMenu($active_series);

    if (!$active_series->authCheck(Player::loginName())) {
        printNoSeries();

        return;
    } else {
        switch ($view) {
            case 'no_view':
            case 'settings':
                printSeriesForm($active_series);
                printLogoForm($active_series);
                break;
            case 'recent_events':
                printRecentEventsTable($active_series);
                break;
            case 'points_management':
                printPointsForm($active_series);
                break;
            case 'organizers':
                printSeriesOrganizersForm($active_series);
                break;
            case 'bannedplayers':
                printPlayerBanForm($active_series);
                break;
            case 'format_editor':
                $esn = urlencode($active_series_name);
                redirect("formatcp.php?series=$esn");
                break;
            case 'trophies':
                printMissingTrophies($active_series);
                break;
            case 'season_standings':
                $active_series->seasonStandings($active_series, $active_series->currentSeason());
                break;
            case 'points_adj':
                seasonPointsAdj();
                break;
            case 'discord':
                printDiscordForm($active_series);
                break;
        }
    }
}

function printMissingTrophies($series)
{
    $recentEvents = $series->getRecentEvents(1000);
    $winningDeck = null;

    echo '<center><h3>Events Missing Trophies</h3></center>';
    echo '<table style="width: 75%;"><tr><th>Event</th><th>Date</th><th>Winner</th><th>Deck</th></tr> ';

    if (count($recentEvents) == 0) {
        echo '<tr><td colspan="4" style="text-align: center; font-weight: bold;">No Events Yet!</td></tr>';
    }

    $now = time();
    foreach ($recentEvents as $event) {
        if (!$event->hastrophy) {
            echo "<tr><td style=\"text-align: center;\"><a href=\"event.php?name={$event->name}\">{$event->name}</a></td> ";
            echo "<td style=\"text-align: center;\">" . time_element(strtotime($event->start), $now) . "</td>";
            $finalists = $event->getFinalists();
            foreach ($finalists as $finalist) {
                if ($finalist['medal'] == '1st') {
                    $winningPlayer = $finalist['player'];
                    $winningDeck = new Deck($finalist['deck']);
                }
            }
            if (!is_null($winningDeck)) {
                echo "<td style=\"text-align: center;\"><a href=\"./profile.php?player={$winningPlayer}\">{$winningPlayer}</a></td>";
                echo "<td style=\"text-align: center;\">{$winningDeck->linkTo()}</td>";
            } else {
                echo '<td></td><td></td>';
            }
            echo '</tr>';
        }
    }
    echo '</table>';
}

function seasonPointsAdj()
{
    global $hasError;
    global $errormsg;

    $hasError = true;
    $errormsg .= 'Season Points Adjustment form has not been implemeted yet.';
    printError();
}

function printError()
{
    global $hasError;
    global $errormsg;
    if ($hasError) {
        echo "<div class=\"error\">{$errormsg}</div>";
    }
}

function printNoSeries()
{
    echo "<center>You're not a organizer of any series, so you can't use this page.<br />";
    echo '<a href="player.php">Back to the Player Control Panel</a></center>';
}

function printSeriesForm($series)
{
    echo '<form action="seriescp.php" method="post">';
    echo '<table class="form" style="border-width: 0px" align="center">';
    echo "<input type=\"hidden\" name=\"series\" value=\"{$series->name}\" />";
    // Active
    echo '<tr><th>Series is Active</th><td> ';
    if ($series->active == 1) {
        echo '<select class="inputbox" name="isactive"><option value="1" selected>Yes</option><option value="0">No</option></select>';
    } else {
        echo '<select class="inputbox" name="isactive"><option value="1">Yes</option><option value="0" selected>No</option></select>';
    }
    echo '</td></tr>';
    // Start day
    echo '<tr><th>Normal start day</th><td> ';
    echo '<select class="inputbox" name="start_day">';
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    foreach ($days as $dayofweek) {
        if ($dayofweek == $series->start_day) {
            echo "<option selected>{$dayofweek}</option>";
        } else {
            echo "<option>{$dayofweek}</option>";
        }
    }
    echo '</select>';
    echo '</td></tr>';
    // Start time
    echo '<tr><th> Normal start time </th> <td> ';
    $time_parts = explode(':', $series->start_time);
    echo timeDropMenu($time_parts[0], $time_parts[1]);
    echo '</td> </tr>';
    // Pre-registration on by default?
    echo '<tr><th>Pre-Registration Default</th>';
    echo '<td><input type="checkbox" value="1" name="preregdefault" ';
    if ($series->prereg_default == 1) {
        echo 'checked ';
    }
    echo '/></td></tr>';
    print_text_input('MTGO Room', 'mtgo_room', $series->mtgo_room);

    // Submit button
    echo '<tr><td colspan="2" class="buttons">';
    echo '<input class="inputbutton" type="submit" name="action" value="Update Series" /></td></tr>';
    echo '</table></form>';
}

function seriesCPMenu($series, $cur = '')
{
    $name = $series->name;
    echo '<table><tr><td colspan="2" align="center">';
    echo "<a href=\"seriescp.php?series=$name&view=settings\">Series Settings</a>";
    echo " | <a href=\"seriescp.php?series=$name&view=recent_events\">Recent Events</a>";
    echo " | <a href=\"seriescp.php?series=$name&view=points_management\">Points Management</a>";
    // echo " | <a href=\"seriescp.php?series=$name&view=points_adj\">Points Adj.</a>";
    echo " | <a href=\"seriescp.php?series=$name&view=organizers\">Series Organizers</a>";
    // echo " | <a href=\"formatcp.php?series=$name\">Format Editor</a>";
    echo " | <a href=\"seriescp.php?series=$name&view=trophies\">Trophies</a>";
    echo " | <a href=\"seriescp.php?series=$name&view=season_standings\">Season Standings</a>";
    echo " | <a href=\"seriescp.php?series=$name&view=bannedplayers\">Ban Players</a>";
    echo '</td></tr></table>';
}

function printSeriesOrganizersForm($series)
{
    $player = new Player(Player::loginName());
    echo '<form action="seriescp.php" method="post">';
    echo "<input type=\"hidden\" name=\"series\" value=\"{$series->name}\" />";
    echo '<h3><center>Series Organizers</center></h3>';
    echo '<p style="width: 75%; text-align: left;">Series organizers can create new series events, manage any event in the series, and modify anything on this page.  Please add them with care as they could screw with anything related to your series including changing the logo and the time.  Only verified members can be series organizers.</p>';
    echo '<p style="width: 75%; text-align: left;"><em>If you just need a guest host, add them as the host to a specific event!</em></p>';
    echo '<table class="form" style="border-width: 0px;" align="center">';
    echo '<tr><th style="text-align: center;">Player</th><th style="width: 50px; text-align: center;">Delete</th></tr>';
    foreach ($series->organizers as $organizer) {
        echo "<tr><td style=\"text-align: center;\">{$organizer}</td>";
        echo "<td style=\"text-align: center; width: 50px; \"><input type=\"checkbox\" value=\"{$organizer}\" name=\"delorganizers[]\" ";
        if ($organizer == $player->loginName() && !$player->isSuper()) {
            echo 'disabled="yes" ';
        }
        echo '/></td></tr>';
    }
    echo '<tr><td colspan="2">Add new: <input class="inputbox" type="text" name="addorganizer" /></td></tr> ';
    echo '<tr><td colspan="2" class="buttons">';
    echo '<input type="hidden" name="view" value="organizers" />';
    echo '<input class="inputbutton" type="submit" value="Update Organizers" name="action" /> </td> </tr> ';
    echo '</table> ';
}

function printPlayerBanForm($series)
{
    $player = new Player(Player::loginName());
    echo '<form action="seriescp.php" method="post">';
    echo "<input type=\"hidden\" name=\"series\" value=\"{$series->name}\" />";
    echo '<h3><center>Banned Players</center></h3>';
    echo '<p style="width: 75%; text-align: left;">Players added to this ban list will not be able to register for any event (including alt events) that are created by this series. You can also suspend a player simply by adding them to this list for a period of time and removing them.</p>';
    echo '<table class="form" style="border-width: 0px;" align="center">';
    echo '<tr><th style="text-align: center;">Player</th>';
    echo '<th style="text-align: center;">Added On</th>';
    echo '<th style="text-align: center;">Reason</th>';
    echo '<th style="width: 50px; text-align: center;">Delete</th></tr>';
    if (count($series->bannedplayers)) {
        foreach ($series->bannedplayers as $bannedplayername) {
            $addedDate = $series->getBannedPlayerDate($bannedplayername);
            $reasonBanned = $series->getBannedPlayerReason($bannedplayername);
            echo "<tr><td style=\"text-align: center;\">{$bannedplayername}</td>";
            echo "<td style=\"text-align: center;\">$addedDate</td>";
            echo "<td style=\"text-align: center;\">$reasonBanned</td>";
            echo "<td style=\"text-align: center; width: 50px; \"><input type=\"checkbox\" value=\"{$bannedplayername}\" name=\"removebannedplayer[]\" ";
            if ($bannedplayername == $player->loginName()) {
                echo 'disabled="yes" ';
            }
            echo '/></td></tr>';
        }
    } else {
        echo '<tr><td colspan="3" style="text-align: left;">No Banned Players</td></tr>';
    }
    echo '</table>';
    echo '<table class="form" style="border-width: 0px;" align="center">';
    echo '<tr><td>Add new:</td><td><input class="inputbox" type="text" name="addbannedplayer" /></td></tr> ';
    echo '<tr><td>Reason:</td><td><input class="inputbox" type="text" name="reason" /></td></tr> ';
    echo '<tr><td colspan="2" class="buttons">';
    echo '<input type="hidden" name="view" value="bannedplayers" />';
    echo '<input class="inputbutton" type="submit" value="Update Banned Players" name="action" /> </td> </tr> ';
    echo '</table> ';
}

function printPointsRule($rule, $key, $rules, $formtype = 'text', $size = 4)
{
    echo "<tr><th>{$rule}</th>";
    if ($formtype == 'text') {
        echo "<td><input class=\"inputbox\" type=\"text\" value=\"{$rules[$key]}\" name=\"new_rules[{$key}]\" size=\"{$size}\" /> </td> </tr> ";
    } elseif ($formtype == 'checkbox') {
        echo "<td><input type=\"checkbox\" value=\"1\" name=\"new_rules[{$key}]\" ";
        if ($rules[$key] == 1) {
            echo 'checked ';
        }
        echo ' /></td></tr>';
    } elseif ($formtype == 'format') {
        echo '<td> ';
        echo formatDropMenu($rules[$key], false, "new_rules[{$key}]");
        echo '</td></tr>';
    }
}

function printPointsForm($series)
{
    $chosen_season = $series->currentSeason();
    if (isset($_GET['season'])) {
        $chosen_season = $_GET['season'];
    }
    if (isset($_POST['season'])) {
        $chosen_season = $_POST['season'];
    }

    echo '<h3><center> Season Points Management </center> </h3>';
    echo '<p style="width:75%; text-align: left;">Here you can edit the way that season points are calculated for each player.  Choose the season that you want your point rules to be active for, and then put in the number of season points for each type of event.  You can adjust the points a player gets for each event individually as well, to take away points for not posting a deck for example or giving extra points for a tiebreaker-miss of top eight.</p>';
    echo "<p style=\"width:75%; text-align: left;\">Points are cumulative, so if someone gets the first place, they will get points for first place, participation, each round they played (in the main event, not the finals), for each match they won, lost, and got a bye, as well as the points for posting a decklist if they do post.  However, The first place to top 8 points are NOT added together, you only get points for where you end up (calculated by the medals).  An event winner doesn't get points for the second place, top 4 or top 8.</p>";
    echo "<p style=\"width:75%; text-align: left;\"><strong>Points are NOT counted for events with the 'Custom' number!</strong></p>";
    echo '<center>';
    echo '<form action="seriescp.php">';
    echo "<input type=\"hidden\" name=\"series\" value=\"{$series->name}\" />";
    echo seasonDropMenu($chosen_season);
    echo '<input type="hidden" name="view" value="points_management" />';
    echo '<input class="inputbutton" type="submit" value="Choose Season" />';
    echo '</form>';
    echo '</center>';
    $seasonrules = $series->getSeasonRules($chosen_season);
    echo '<form action="seriescp.php" method="post">';
    echo "<input type=\"hidden\" name=\"series\" value=\"{$series->name}\" />";
    echo "<input type=\"hidden\" name=\"season\" value=\"{$chosen_season}\" />";
    echo '<table class="form" style="border-width: 0px;" align="center">';
    echo "<tr><th class=\"top\" colspan=\"2\">Season {$chosen_season} Settings</th></tr>";
    printPointsRule('First Place', 'first_pts', $seasonrules);
    printPointsRule('Second Place', 'second_pts', $seasonrules);
    printPointsRule('Top 4', 'semi_pts', $seasonrules);
    printPointsRule('Top 8', 'quarter_pts', $seasonrules);
    printPointsRule('Participating', 'participation_pts', $seasonrules);
    printPointsRule('Each round played', 'rounds_pts', $seasonrules);
    printPointsRule('Match win', 'win_pts', $seasonrules);
    printPointsRule('Match loss', 'loss_pts', $seasonrules);
    printPointsRule('Round bye', 'bye_pts', $seasonrules);
    printPointsRule('Posting a decklist', 'decklist_pts', $seasonrules);
    printPointsRule('Require decklist for points', 'must_decklist', $seasonrules, 'checkbox');
    printPointsRule('WORLDS Cutoff (players)', 'cutoff_ord', $seasonrules);
    printPointsRule('Master Document Location', 'master_link', $seasonrules, 'text', 50);
    printPointsRule('Season Format', 'format', $seasonrules, 'format');
    echo '<tr><td colspan="2" class="buttons">';
    echo '<input type="hidden" name="view" value="points_management" />';
    echo '<input class="inputbutton" type="submit" name="action" value="Update Points Rules" />';
    echo '</td></table></form>';
}

function printLogoForm($series)
{
    echo '<form action="seriescp.php" method="post" enctype="multipart/form-data">';
    echo '<table class="form" style="border-width: 0px;" align="center">';
    echo "<input type=\"hidden\" name=\"series\" value=\"{$series->name}\" />";
    echo '<tr><th>Current Logo</th>';
    echo '<td>' . Series::image_tag($series->name) . '</td></tr>';
    echo '<tr><th>Upload New Logo</th>';
    echo '<td><input class="inputbox" type="file" name="logo" /> ';
    echo '<input class="inputbutton" type="submit" name="action" value="Change Logo" /></td></tr>';
    echo '</table></form> ';
}

function printDiscordForm($series)
{
    $player = new Player(Player::loginName());
    echo '<form action="seriescp.php" method="post">';
    echo "<input type=\"hidden\" name=\"series\" value=\"{$series->name}\" />";
    echo '<h3><center>Series Organizers</center></h3>';
    echo '<p style="width: 75%; text-align: left;">Series organizers can create new series events, manage any event in the series, and modify anything on this page.  Please add them with care as they could screw with anything related to your series including changing the logo and the time.  Only verified members can be series organizers.</p>';
    echo '<p style="width: 75%; text-align: left;"><em>If you just need a guest host, add them as the host to a specific event!</em></p>';
    echo '<table class="form" style="border-width: 0px;" align="center">';
    echo '<tr><th style="text-align: center;">Player</th><th style="width: 50px; text-align: center;">Delete</th></tr>';
    foreach ($series->organizers as $organizer) {
        echo "<tr><td style=\"text-align: center;\">{$organizer}</td>";
        echo "<td style=\"text-align: center; width: 50px; \"><input type=\"checkbox\" value=\"{$organizer}\" name=\"delorganizers[]\" ";
        if ($organizer == $player->loginName() && !$player->isSuper()) {
            echo 'disabled="yes" ';
        }
        echo '/></td></tr>';
    }
    echo '<tr><td colspan="2">Add new: <input class="inputbox" type="text" name="addorganizer" /></td></tr> ';
    echo '<tr><td colspan="2" class="buttons">';
    echo '<input type="hidden" name="view" value="organizers" />';
    echo '<input class="inputbutton" type="submit" value="Update Organizers" name="action" /> </td> </tr> ';
    echo '</table> ';
}

function printRecentEventsTable($series)
{
    $recentEvents = $series->getRecentEvents();
    echo '<center> <h3> Recent Events </h3> </center>';
    echo '<table style="width: 75%;"> <tr> <th> Event </th> <th> Date </th> <th> Players </th> <th> Hosts </th> </tr> ';
    if (count($recentEvents) == 0) {
        echo '<tr><td colspan="4" style="text-align: center; font-weight: bold;"> No Events Yet! </td> </tr>';
    }
    $now = time();
    foreach ($recentEvents as $event) {
        echo "<tr> <td> <a href=\"event.php?name={$event->name}\">{$event->name}</a> </td> ";
        echo "<td>" . time_element(strtotime($event->start), $now) . "</td>";
        echo "<td style=\"text-align: center;\"> {$event->getPlayerCount()} </td>";
        echo "<td>{$event->host}";
        if ($event->cohost != '') {
            echo " / {$event->cohost}</td>";
        }
        echo '</tr>';
    }
    echo '</table>';
}

function handleActions()
{
    global $hasError;
    global $errormsg;
    if (!isset($_POST['series'])) {
        return;
    }
    $seriesname = $_POST['series'];
    $series = new Series($seriesname);
    if (!$series) {
        return;
    }
    if (!$series->authCheck(Player::loginName())) {
        return;
    }
    if ($_POST['action'] == 'Update Series') {
        $newactive = $_POST['isactive'];
        $newtime = $_POST['hour'];
        $newday = $_POST['start_day'];
        $room = $_POST['mtgo_room'];

        if (!isset($_POST['preregdefault'])) {
            $prereg = 0;
        } else {
            $prereg = $_POST['preregdefault'];
        }

        $series = new Series($seriesname);
        if ($series->authCheck(Player::loginName())) {
            $series->active = $newactive;
            $series->start_time = $newtime . ':00';
            $series->start_day = $newday;
            $series->prereg_default = $prereg;
            $series->mtgo_room = $room;
            $series->save();
        }
    } elseif ($_POST['action'] == 'Change Logo') {
        if ($_FILES['logo']['size'] > 0) {
            $file = $_FILES['logo'];
            $name = $file['name'];
            $tmp = $file['tmp_name'];
            $size = $file['size'];
            $type = $file['type'];

            $series->setLogo($tmp, $type, $size);
        }
    } elseif ($_POST['action'] == 'Update Organizers') {
        if (isset($_POST['delorganizers'])) {
            $removals = $_POST['delorganizers'];
            foreach ($removals as $deadorganizer) {
                $series->removeOrganizer($deadorganizer);
            }
        }
        if (!isset($_POST['addorganizer'])) {
            return;
        }
        $addition = $_POST['addorganizer'];
        $addplayer = Player::findByName($addition);
        if ($addplayer == null) {
            $hasError = true;
            $errormsg .= "Can't add {$addition} to Series Organizers, they don't exist!";

            return;
        }
        if ($addplayer->verified == 0 && Player::getSessionPlayer()->super == 0) {
            $hasError = true;
            $errormsg .= "Can't add {$addplayer->name} to Series Organizers, they aren't a verified user!";

            return;
        }
        $series->addOrganizer($addplayer->name);
    } elseif ($_POST['action'] == 'Update Banned Players') {
        if (isset($_POST['removebannedplayer'])) {
            $removals = $_POST['removebannedplayer'];
            foreach ($removals as $playertoremove) {
                $series->removeBannedPlayer($playertoremove);
            }
        }
        if (!isset($_POST['addbannedplayer'])) {
            return;
        }
        $addition = $_POST['addbannedplayer'];
        $addplayer = Player::findOrCreateByName($addition);
        if ($addplayer == null) {
            $hasError = true;
            $errormsg .= "Can't add {$addition} to Banned Players, they don't exist!";

            return;
        }
        $series->addBannedPlayer($addplayer->name, $_POST['reason']);
    } elseif ($_POST['action'] == 'Update Points Rules') {
        $new_rules = $_POST['new_rules'];
        $series->setSeasonRules($_POST['season'], $new_rules);
    }
}
