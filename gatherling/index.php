<?php

use Gatherling\Database;
use Gatherling\Deck;
use Gatherling\Event;
use Gatherling\Matchup;
use Gatherling\Player;

include 'lib.php';
$version = Database::single_result('SELECT version FROM db_version LIMIT 1');
if ($version < 51) {
    require 'outofservice.php';
    return;
}

session_start();
print_header('Home'); ?>

<div id="gatherling_main" class="box grid_12">
    <div class="uppertitle">Gatherling</div>
    <p>Welcome to Gatherling! The place for player-run Magic Online tournaments. Report bugs or give feedback on the <a href="https://discord.gg/F9SrMwV">Discord</a>.</a></p>
</div>

<div id="maincolumn" class="grid_8">

    <div class="box">
        <div class="uppertitle">Active Events</div>
        <?php activeEvents(); ?>
    </div>

    <div class="box">
        <div class="uppertitle">Upcoming Events</div>
        <?php upcomingEvents(); ?>
    </div>

    <div class="box">
        <div class="uppertitle">Gatherling Statistics</div>
        <?php stats(); ?>
    </div>

</div>

<div class="grid_4">
    <div class="box pad">
        <?php
        $player = Player::getSessionPlayer();
        if ($player != null) {
            playerBox($player);
        } else {
            loginBox();
        } ?>
    </div>
    <div class="box">
        <div class="uppertitle">Recent Winners</div>
        <?php recentWinners(); ?>
    </div>

</div>

<div class="clear"></div>

<?php
print_footer();

function activeEvents()
{
    $events = Event::getActiveEvents(false);
    if ($events) {
        echo "<table class=\"events\">\n";
        foreach ($events as $event) {
            $name = $event->name;
            $format = $event->format;
            $round = $event->current_round;
            $col2 = '<a href="eventreport.php?event='.rawurlencode($name)."\">{$name}</a>";
            ?>
                <tr>
                    <td><?= $format ?></td>
                    <td><?= $col2 ?></td>
                    <td>Round <?= $round ?></td>
                </tr>
            <?php
        }
        echo "</table>\n";
    }
}

function upcomingEvents()
{
    $db = Database::getConnection();
    $result = $db->query('SELECT UNIX_TIMESTAMP(DATE_SUB(start, INTERVAL 0 MINUTE)) AS d,
    format, series, name, threadurl, start FROM events
    WHERE DATE_SUB(start, INTERVAL 0 MINUTE) > NOW() AND private = 0 ORDER BY start ASC LIMIT 20');
    // interval in DATE_SUB was used to select eastern standard time, but since the server is now in Washington DC it is not needed
    $result or exit($db->error);
    echo "<table class=\"events\">\n";
    while ($row = $result->fetch_assoc()) {
        $dateStr = date('D j M', $row['d']);
        $timeStr = date('g:i A', $row['d']);
        $name = $row['name'];
        $format = $row['format'];
        $start = $row['start'];
        $col2 = '<a href="eventreport.php?event='.rawurlencode($name)."\">$name</a>";
        ?>
            <tr>
                <td><?= $col2 ?><br><?= $format ?></td>
                <td class="eventtime timeclear" start="<?= $start ?>"><?= $dateStr ?></td>
                <td class="timeclear"><?= $timeStr ?></td>
            </tr>
        <?php
    }
    echo "</table>\n";
    echo "<p class=\"timeclear\"><i>All times are EST.</i></p>\n";
    echo '<script src="time.js"></script>';

    $result->close();
}

function stats()
{
    ?>
        <ul>
            <li>There are <?php echo Deck::uniqueCount() ?> unique decks.</li>
            <li>We have recorded <?php echo Matchup::count() ?> matches from <?php echo Event::count() ?> events.</li>
            <li>There are <?php echo Player::activeCount() ?> active players in gatherling. (<?php echo Player::verifiedCount() ?> verified.)</li>
        </ul>
    <?php
}

function playerBox($player)
{
    ?>
        <div>
            <p><b> Welcome back <?php echo $player->name ?> </b></p>
            <ul>
                <li> <a href="profile.php">Check out your profile</a> </li>
                <li> <a href="player.php?mode=alldecks">Enter your own decklists</a> </li>
                <?php
                $event = Event::findMostRecentByHost($player->name);
                if (!is_null($event)) { ?>
                    <li> <a href="event.php?name=<?php echo $event->name ?>">Manage <?php echo $event->name ?></a> </li>
                    <?php
                }
                if ($player->isHost()) { ?>
                    <li> <a href="event.php">Host Control Panel</a></li>
                    <?php
                } ?>
            </ul>
        </div>
    <?php
}

function loginBox()
{
    ?>
        <div class="uppertitle">Login to Gatherling</div>
        <form action="login.php" method="post">
            <table class="form">
                <tr>
                    <th><label for="username">Username</label></th>
                    <td><input class="inputbox" type="text" name="username" value="" /></td>
                </tr>
                <tr>
                    <th><label for="password">Password</label></th>
                    <td><input class="inputbox" type="password" name="password" value="" /></td>
                </tr>
                <tr>
                    <td colspan="2" class="buttons">
                        <input class="inputbutton" type="submit" name="mode" value="Log In" />
                        <input class="inputbutton discordlogin fa-discord" type="submit" name="mode" value="Log In with Discord" />
                    </td>
                </tr>
            </table>
        </form>
        <p>
            <a href="register.php">Need to register?</a><br />
            <a href="forgot.php">Forgot your password?</a>
        </p>
    <?php
}

function recentWinners()
{
    $db = Database::getConnection();
    $result = $db->query("SELECT e.name as `event`, n.player, d.name, d.id
                        FROM entries n, decks d, events e
                        WHERE n.medal='1st'
                        AND d.id=n.deck
                        AND e.id=n.event_id
                        ORDER BY e.start
                        DESC LIMIT 10");
    $result or exit($db->error);
    echo "<table class=\"winners\">\n";
    while ($row = $result->fetch_assoc()) {
        $deck = new Deck($row['id']);
        $manaSymbol = $deck->getColorImages();
        ?>
        <tr>
            <td colspan="2"><?= $row['event'] ?></td>
        </tr>
        <tr>
            <td colspan="2">
                <a class="borderless" href="./eventreport.php?event=<?= $row['event'] ?>">
                    <?= $manaSymbol ?>
                </a>
            </td>
        </tr>
        <tr class="player-deck">
            <td>
                <b><a href="./profile.php?player=<?= $row['player'] ?>"><?= $row['player'] ?></a></b>
            </td>
            <td>
                <i><a href="./deck.php?mode=view&event=<?= $row['event'] ?>"><?= $row['name'] ?></a></i>
            </td>
        </tr>
        <?php
    }
    echo '</table>';
    $result->close();
}
