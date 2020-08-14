<?php

include_once 'lib.php';

activeEvents();

echo "<div class=\"box sidecolumn\">\n";
echo "<h4>UPCOMING EVENTS</h4>\n";
upcomingEvents();
echo '</div>';

echo "<div class=\"box sidecolumn\">\n";
echo "<h4>RECENT WINNERS</h4>\n";
recentWinners();
echo "</div>\n";

// echo "<div class=\"box\">\n";
// echo "<h4>RECENT TROPHIES</h4>\n";
// recentTrophies();
// echo "</div>\n";

function activeEvents()
{
    $db = Database::getConnection();
    $events = Event::getActiveEvents(false);
    if ($events) {
        echo "<div class=\"box sidecolumn\">\n";
        echo "<h4>ACTIVE EVENTS</h4>\n";

        foreach ($events as $event) {
            $name = $event->name;
            $series = $event->series;
            $threadurl = $event->threadurl;
            $format = $event->format;
            $round = $event->current_round;
            $col2 = $name;
            $room = '';
            $series = new Series($event->series);
            if ($series->mtgo_room) {
                $room = '#'.$series->mtgo_room;
            }
            echo "<table class=\"center\">\n";
            if (strcmp($threadurl, '') != 0) {
                $col2 = "<a href=\"$threadurl\">".$name.'</a>';
            }
            echo "<tr><td width=60>$format</td>\n";
            echo "<td width=100>$col2<br />$room</td>\n";
            echo "<td width=50>Round $round</td></tr></table>\n";
        }
        echo "<table class=\"center\">\n";
        echo '</table>';
        echo '</div>';
    }
}

function upcomingEvents()
{
    $db = Database::getConnection();
    $result = $db->query('SELECT UNIX_TIMESTAMP(DATE_SUB(start, INTERVAL 0 MINUTE)) AS d,
    format, series, name, threadurl FROM events
    WHERE DATE_SUB(start, INTERVAL 0 MINUTE) > NOW() ORDER BY start ASC LIMIT 20');
    // interval in DATE_SUB was used to select eastern standard time, but since the server is now in Washington DC it is not needed
    $result or exit($db->error);
    while ($row = $result->fetch_assoc()) {
        $dateStr = date('D j M', $row['d']);
        $timeStr = date('g:i A', $row['d']);
        $name = $row['name'];
        $series = $row['series'];
        $threadurl = $row['threadurl'];
        $format = $row['format'];
        $col2 = $name;
        echo "<table class=\"center\">\n";
        if (strcmp($threadurl, '') != 0) {
            $col2 = "<a href=\"$threadurl\">".$name.'</a>';
        }
        echo "<tr><td width=60>$dateStr</td>\n";
        echo "<td width=100>$col2<br />$format</td>\n";
        echo "<td width=50>$timeStr</td></tr></table>\n";
    }
    echo "<table class=\"center\">\n";
    echo "<tr><td colspan=\"3\" align=\"center\"><i>All times are EST.</i></td></tr>\n";
    echo '</table>';
    $result->close();
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
    while ($row = $result->fetch_assoc()) {
        echo '<div class="newtrophies">';
        echo "<table class=\"center\">\n";
        echo '<tr><td colspan="3" align="center">';
        echo "<a class=\"borderless\" href=\"./eventreport.php?event={$row['event']}\">";
        $deck = new Deck($row['id']);
        $manaSymbol = $deck->getColorImages();
        echo $manaSymbol;
        echo "</a></td></tr>\n";
        echo "<tr><td align=\"center\" width=\"160\"><b><a href=\"./profile.php?player={$row['player']}\">{$row['player']}</a></b></td>";
        echo "<td align=\"center\" width=\"160\"><i><a href=\"./deck.php?mode=view&event={$row['event']}\">{$row['name']}</a></i></td></tr>";
        echo '<tr><td colspan="3" align="center" width="160">_______________________________________</td></tr>';
        echo '</table>';
        echo '</div>';
    }
    $result->close();
}

function recentTrophies()
{
    $sql = "SELECT e.name
             FROM entries n, events e, trophies t
             WHERE n.medal='1st'
             AND e.id=n.event_id
             AND t.event=e.name
             ORDER BY e.start
             DESC LIMIT 20";
    $results = Database::list_result($sql);
    if (count($results) > 0) {
        echo '<div class="newtrophies">';
        echo "<table class=\"center\">\n";
        foreach ($results as $result) {
            echo '<tr><td colspan="3" align="center">';
            echo "<a class=\"borderless\" href=\"./eventreport.php?event={$result}\">";
            echo "<img src=\"./displayTrophy.php?event={$result}\" alt=\"Event Trophy\" style=\"border-width: 0px;\" />";
            echo "</a></td></tr>\n";
        }
        echo '</table>';
        echo '</div>';
    } else {
        $this->errors[] = '<center><br>No Trophies Found.';
    }
}
