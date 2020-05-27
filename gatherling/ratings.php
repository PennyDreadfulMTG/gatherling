<?php
include 'lib.php';
session_start();

print_header('Ratings');
?>
<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Ratings </div>

<?php content(); ?>

</div> </div>

<?php print_footer(); ?>

<?php

function content()
{
    $format = 'Composite';
    if (isset($_POST['format'])) {
        $format = $_POST['format'];
    }
    ratingsForm($format);
    $min = 20;
    echo '<br /><center>';
    currentThrough($format);
    echo "</center><br />\n";
    echo '<center>';
    bestEver($format);
    echo "</center><br />\n";
    ratingsTable($format, $min);
    echo '<br />';
}

function ratingsForm($format)
{
    $ratings = new Ratings();
    echo "<form action=\"ratings.php\" method=\"post\">\n";
    echo '<table align="center" style="border-width: 0px">';
    echo '<tr><td>Select a rating to display: ';
    $ratings->formatDropMenuR($format);
    echo '&nbsp;';
    echo '<input class="inputbutton" type="submit" name="mode" value="Display Ratings" />';
    echo "</td></tr>\n";
    echo "</table></form>\n";
}

function ratingsTable($format, $min = 20)
{
    $db = Database::getConnection();
    $stmt = $db->prepare('SELECT p.name AS player, r.rating, r.wins, r.losses
                          FROM ratings r, players p,
                          (SELECT qr.player AS qplayer, MAX(qr.updated) AS qmax
                  FROM ratings AS qr
                  WHERE qr.format = ?
                  GROUP BY qr.player) AS q
                  WHERE r.format = ?
                  AND p.name=r.player
                  AND q.qplayer=r.player
                  AND q.qmax=r.updated
                  AND r.wins + r.losses >= ?
                          ORDER BY r.rating DESC');
    $stmt->bind_param('ssd', $format, $format, $min);
    $stmt->execute() or die($stmt->error);
    $stmt->bind_result($playername, $rating, $wins, $losses);
    $rank = 0;

    $ratings_data = [];
    while ($stmt->fetch()) {
        $rank++;
        $ratings_data[] = ['rank' => $rank, 'playername' => $playername, 'rating' => $rating, 'wins' => $wins, 'losses' => $losses];
    }
    $stmt->close();

    $records_per_page = 25;
    $pagination = new Pagination();
    $pagination->records(count($ratings_data));
    $pagination->records_per_page($records_per_page);
    $pagination->avoid_duplicate_content(false);

    // get the ratings for the current page
    $ratings_data = array_slice($ratings_data, (($pagination->get_page() - 1)
                                    * $records_per_page), $records_per_page);

    echo "<table align=\"center\" style=\"border-width: 0px;\" width=\"500px\">\n";
    echo '<tr><td colspan=6 align="center">';
    echo "<i>Only players with $min or more matches are included.";
    echo '</td></tr>';
    echo "<tr><td>&nbsp;</td></tr>\n";
    echo '<tr><td align="center"><b>Rank</td>';
    echo '<td><b>Player</td><td align="center">';
    echo '<b>Rating</td>';
    echo "<td align=\"center\" colspan=\"3\"><b>Record</td></tr>\n";

    foreach ($ratings_data as $vals) {
        echo "<tr><td align=\"center\">{$vals['rank']}</td><td>";
        $player = new Player($vals['playername']);
        echo $player->linkTo();
        echo "</td>\n";
        echo "<td align=\"center\">{$vals['rating']}</td>\n";
        echo "<td align=\"right\" width=35>{$vals['wins']}&nbsp;</td>\n";
        echo "<td align=\"center\">-</td><td width=35 align=\"left\">&nbsp;{$vals['losses']}</td></tr>";
    }
    echo '</table>';
    $pagination->render();
    echo '<br />';
    echo '<br />';
}

function bestEver($format)
{
    $db = Database::getConnection();
    $stmt = $db->prepare('SELECT p.name AS player, r.rating,
                        UNIX_TIMESTAMP(r.updated) AS t
                FROM ratings AS r, players AS p,
                (SELECT MAX(qr.rating) AS qmax
                FROM ratings AS qr WHERE qr.format = ?) AS q
                        WHERE format = ?  AND p.name=r.player AND q.qmax=r.rating');
    $stmt->bind_param('ss', $format, $format);
    $stmt->execute() or die($stmt->error);
    $stmt->bind_result($playername, $rating, $timestamp);
    $stmt->fetch();
    $stmt->close();

    printf(
        "The highest $format rating ever achieved is <b>%d</b>, obtained by <b>%s</b> on %s",
        $rating,
        $playername,
        date('l, F j, Y', $timestamp)
    );
}

function currentThrough($format)
{
    $db = Database::getConnection();
    $stmt = $db->prepare('SELECT MAX(updated) AS m FROM ratings WHERE format = ?');
    $stmt->bind_param('s', $format);
    $stmt->execute() or die($stmt->error);
    $stmt->bind_result($start);
    $stmt->fetch();
    $stmt->close();
    $stmt = $db->prepare('SELECT name FROM events WHERE start = ?');
    $stmt->bind_param('s', $start);
    $stmt->execute() or die($stmt->error);
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
    $date = strftime('%Y-%m-%d', strtotime($start));
    echo "<b>Ratings current through {$date} - <span style=\"color: #D45E28\">{$name}</span></b>";
}

?>
