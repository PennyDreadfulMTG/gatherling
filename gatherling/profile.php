<?php

use Gatherling\Models\Event;
use Gatherling\Models\Player;

require_once 'lib.php';
require_once 'lib_form_helper.php';

print_header('Player Profile');

$playername = '';
if (isset($_SESSION['username'])) {
    $playername = $_SESSION['username'];
}
if (isset($_GET['player'])) {
    $playername = $_GET['player'];
}
if (isset($_POST['player'])) {
    $playername = $_POST['player'];
}
$playername = htmlspecialchars($playername);

$profile_edit = 0;
if (isset($_REQUEST['profile_edit'])) {
    $profile_edit = $_REQUEST['profile_edit'];
}

if (isset($_REQUEST['email'])) {
    $email = $_REQUEST['email'];
}
if (isset($_REQUEST['email_public'])) {
    $email_public = $_REQUEST['email_public'];
}
if (isset($_REQUEST['time_zone'])) {
    $timezone = $_REQUEST['time_zone'];
}

    searchForm($playername);
?>
<div class="grid_10 suffix_1 prefix_1">
    <div id="gatherling_main" class="box">
        <div class="uppertitle">Player Profile</div>
        <?php content($profile_edit); ?>
    </div>
</div>

<?php print_footer(); ?>

<?php
function content($profile_edit)
{
    global $playername;
    if (rtrim($playername) === '') {
        ?>
            <p>Please <a href="login.php">log in</a> to see your profile. You may also use the search above without logging in.</p>
        <?php
        return;
    }
    $player = Player::findByName($playername);
    if (is_null($player)) {
        ?>
            <p><b><?= $playername ?></b> could not be found in the database. Please check your spelling and try again.</p>
        <?php
        return;
    }
    if ($profile_edit == 1) {
        editForm($player->timezone, $player->emailAddress, $player->emailPrivacy);
    } elseif ($profile_edit == 2) {
        $player->emailAddress = $_GET['email'];
        $player->emailPrivacy = $_GET['email_public'];
        $player->timezone = $_GET['timezone'];
        $player->save();
        profileTable($player);
    } else {
        profileTable($player);
    }
}

function profileTable($player)
{
    echo "<div class=\"grid_5 alpha\"> <div id=\"gatherling_lefthalf\">\n";
    infoTable($player);
    bestDecksTable($player);
    echo "</div></div>\n";
    echo "<div class=\"grid_5 omega\"> <div id=\"gatherling_righthalf\">\n";
    medalTable($player);
    trophyTable($player);
    echo "</div> </div>\n";
    echo '<div class="clear"></div>';
}

function infoTable($player)
{
    $ndx = 0;
    $sum = 0;
    $favF = '';
    foreach ($player->getFormatsPlayedStats() as $tmprow) {
        $sum += $tmprow['cnt'];
        if ($ndx == 0) {
            $max = $tmprow['cnt'];
            $favF = $tmprow['format'];
        }
        $ndx++;
    }
    $pcgF = 0;
    if ($sum > 0) {
        $pcgF = round(($max / $sum) * 100);
    }

    $ndx = 0;
    $sum = 0;
    $favS = '';
    foreach ($player->getSeriesPlayedStats() as $tmprow) {
        $sum += $tmprow['cnt'];
        if ($ndx == 0) {
            $max = $tmprow['cnt'];
            $favS = $tmprow['series'];
        }
        $ndx++;
    }
    $pcgS = 0;
    if ($sum > 0) {
        $pcgS = round(($max / $sum) * 100);
    }

    $line1 = strtoupper($player->name);

    $matches = $player->getAllMatches();
    $nummatches = count($matches);

    $rating = $player->getRating();
    $hosted = $player->getHostedEventsCount();
    $lastevent = $player->getLastEventPlayed();
    $emailAddress = $player->emailAddress;
    if ($player->emailIsPublic() == 0) {
        $emailprivacy = 'Admin Viewable Only';
    } else {
        $emailprivacy = 'Publicly Viewable';
    }

    echo "<table>\n";
    echo '<tr><td colspan="2">';
    echo "<b>$line1</td></tr>\n";
    if ($player->mtgo_username) {
        echo '<tr><td colspan="2">';
        echo "<i class=\"ss ss-pmodo\"></i> {$player->mtgo_username}</td></tr>\n";
    }
    if ($player->mtga_username) {
        echo '<tr><td colspan="2">';
        echo "<i class=\"ss ss-parl3\"></i> {$player->mtga_username}</td></tr>\n";
    }
    if ($player->discord_handle) {
        echo '<tr><td colspan="2">';
        echo "<i class=\"fab fa-discord\"></i> {$player->discord_handle}</td></tr>\n";
    }
    echo "<tr><td style=\"min-width: 16ch\">Rating:</td>\n";
    echo "<td align=\"right\">{$rating}</td></tr>\n";
    echo "<tr><td>Matches Played:</td>\n";
    echo "<td align=\"right\">$nummatches</td></tr>\n";
    echo "<tr><td>Record:</td>\n";
    echo "<td align=\"right\">{$player->getRecord()}<td>";
    echo "</tr>\n";
    if ($hosted > 0) {
        echo "<tr><td>Events Hosted:</td>\n";
        echo "<td align=\"right\">$hosted</td></tr>\n";
    }
    echo "<tr><td>Favorite Format:</td>\n";
    echo "<td align=\"right\">$favF ($pcgF%)</td></tr>\n";
    echo "<tr><td>Favorite Series:</td>\n";
    echo "<td align=\"right\">$favS ($pcgS%)</td></tr>\n";
    echo "<tr><td>Last Active:</td>\n";
    if (!is_null($lastevent)) {
        echo "<td align=\"right\">" . time_element(strtotime($lastevent->start), time()) . "<br>({$lastevent->name})</td></tr>\n";
    } else {
        echo "<td align=\"right\">Never</td></tr>\n";
    }

    echo "<tr><td>Email:</td>\n";
    if ($emailprivacy == 'Admin Viewable Only') {
        echo "<td align=\"right\">$emailprivacy</td></tr>\n";
    } else {
        echo "<td align=\"right\">$emailAddress<br>($emailprivacy)</td></tr>\n";
    }

    echo "<tr><td>Time Zone:</td>\n";
    echo "<td align=\"right\">{$player->time_zone()}</td></tr>\n";
    echo "<tr><td align=\"center\" colspan='2'><a href=\"profile.php?profile_edit=1\" class=\"borderless\">Edit Player Information</a></td></tr>\n";
    echo '</table>';
}

function medalTable($player)
{
    $medalcount = $player->getMedalStats();

    echo "<table width=260>\n";
    echo "<tr><td align=\"center\" colspan=4><b>MEDALS EARNED</td></tr>\n";
    if (count($medalcount) == 0) {
        echo '<tr><td align="center" colspan="2">';
        echo "<i>{$player->name} has not earned any medals.</td></tr>\n";
    } else {
        medalCell('1st', $medalcount['1st']);
        medalCell('2nd', $medalcount['2nd']);
        medalCell('t4', $medalcount['t4']);
        medalCell('t8', $medalcount['t8']);
    }
    echo "</table>\n";
}

function trophyTable($player)
{
    $events = $player->getEventsWithTrophies();
    echo "<table width=260>\n";
    echo "<tr><td align=\"center\"><b>TROPHIES EARNED</td></tr>\n";
    if (count($events) == 0) {
        echo "<tr><td align=\"center\"><i>{$player->name} has not earned any trophies.</td></tr>\n";
    } else {
        foreach ($events as $eventname) {
            echo '<tr><td align="center">';
            echo "<a href=\"deck.php?mode=view&event=$eventname\" class=\"borderless\">";
            echo Event::trophy_image_tag($eventname);
            echo '</a></td></tr>';
        }
    }
    echo "</table>\n";
}

function bestDecksTable($player)
{
    echo "<table width=250>\n";
    echo "<tr><td align=\"left\" colspan=3><b>MEDAL WINNING DECKS</td></tr>\n";
    $printed = 0;
    foreach ($player->getBestDeckStats() as $row) {
        if ($row['score'] > 0) {
            $record = deckRecordString($row['name'], $player);
            if (rtrim($row['name']) == '') {
                $row['name'] = '* NO NAME *';
            }
            echo '<tr><td>';
            echo "<a href=\"deck.php?mode=view&id={$row['id']}\">";
            echo "{$row['name']}</a></td>\n";
            echo "<td align=\"center\" width=50>$record</td>";
            echo '<td align="right">';
            for ($i = 0; $i < $row['1st']; $i++) {
                inlineMedal('1st');
            }
            for ($i = 0; $i < $row['2nd']; $i++) {
                inlineMedal('2nd');
            }
            for ($i = 0; $i < $row['t4']; $i++) {
                inlineMedal('t4');
            }
            for ($i = 0; $i < $row['t8']; $i++) {
                inlineMedal('t8');
            }
            echo "</td></tr>\n";
            $printed++;
        }
    }
    if ($printed == 0) {
        echo "<tr><td colspan=3><i>{$player->name} has no medal winning decks.";
        echo "</td></tr>\n";
    }
    echo "</table>\n";
}

function medalCell($medal, $count)
{
    if (is_null($count)) {
        $count = 0;
    }
    echo '<tr><td align="right" width=130>';
    echo medalImgStr($medal);
    echo  "<td>$count</td></tr>\n";
}

function inlineMedal($medal)
{
    echo medalImgStr($medal) . '&nbsp;';
}

function deckRecordString($deckname, $player)
{
    $matches = $player->getMatchesByDeckName($deckname);
    $wins = 0;
    $losses = 0;
    $draws = 0;

    foreach ($matches as $match) {
        if ($match->playerWon($player->name)) {
            $wins++;
        } elseif ($match->playerLost($player->name)) {
            $losses++;
        } elseif ($match->playerBye($player->name)) {
            $wins = $wins + 1;
        } elseif ($match->playerMatchInProgress($player->name)) {
            // do nothing since match is in progress and there are no results
        } else {
            $draws++;
        }
    }
    $recordString = $wins . '-' . $losses;
    if ($draws > 0) {
        $recordString .= '-' . $draws;
    }

    return $recordString;
}

function searchForm($name)
{
    echo "<div class=\"grid_10 prefix_1 suffix_1\"> <div class=\"box\" id=\"gatherling_simpleform\">\n";
    echo "<div class=\"uppertitle\">Player Lookup</div>\n";
    echo "<form action=\"profile.php\" mode=\"post\">\n";
    echo "<input class=\"inputbox\" type=\"text\" name=\"player\" value=\"$name\" />";
    echo "<input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"Lookup Profile\" />\n";
    echo "</form>\n";
    echo "<div class=\"clear\"></div>\n";
    echo "</div> </div>\n";
}

function editForm($timezone, $email, $public)
{
    echo "<form action=\"profile.php\" mode=\"POST\">\n";
    echo '<label for="timezone">Time Zone:</label>';
    echo timeZoneDropMenu($timezone);
    echo "<br><label for=\"player\">Email Address: </label><input class=\"inputbox\" type=\"text\" name=\"email\" value=\"$email\" />";
    echo '<br><input type="radio" name="email_public" value="1"';
    if ($public == 1) {
        echo ' checked ';
    }
    echo'>Make my email publicly viewable';
    echo '<br><input type="radio" name="email_public" value="0"';
    if ($public == 0) {
        echo ' checked ';
    }
    echo'>Only allow administrators and event hosts to view my email';
    echo '<br><input type="hidden" name="profile_edit" value="2">';
    echo "<br><input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"Submit Changes\" />\n";
    echo "</form>\n";
}
