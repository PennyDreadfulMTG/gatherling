<?php
include 'lib.php';
session_start();

print_header('Event Report');

?>
<div class="grid_10 prefix_1 suffix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Event Report </div>

<?php
if (isset($_GET['event'])) {
    $event = new Event($_GET['event']);
    showReport($event);
} else {
    eventList();
}

?>

</div>
</div>

<?php print_footer(); ?>

<?php

function eventList($series = '', $season = '')
{
    $db = Database::getConnection();
    $result = $db->query('SELECT e.name AS name, e.format AS format,
        COUNT(DISTINCT n.player) AS players, e.host AS host, e.start AS start,
        e.finalized, e.cohost, e.series, e.season
        FROM events e
        LEFT OUTER JOIN entries AS n ON n.event_id = e.id
        WHERE 1=1 AND e.start < NOW() GROUP BY e.name ORDER BY e.start DESC');

    if (!isset($_GET['format'])) {
        $_GET['format'] = '';
    }
    if (!isset($_GET['series'])) {
        $_GET['series'] = '';
    }
    if (!isset($_GET['season'])) {
        $_GET['season'] = '';
    }

    $onlyformat = false;
    if (strcmp($_GET['format'], '') != 0) {
        $onlyformat = $_GET['format'];
    }
    $onlyseries = false;
    if (strcmp($_GET['series'], '') != 0) {
        $onlyseries = $_GET['series'];
    }
    $onlyseason = false;
    if (strcmp($_GET['season'], '') != 0) {
        $onlyseason = $_GET['season'];
    }

    echo '<form action="eventreport.php" method="get">';
    echo '<table id="EventReport">';
    echo '<tr><td colspan="2" align="center"><b>Filters</td></tr>';
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr><td>Format</td><td>';
    formatDropMenu($_GET['format'], 1);
    echo '</td></tr>';
    echo '<tr><td>Series</td><td>';
    Series::dropMenu($_GET['series'], 1);
    echo '</td></tr>';
    echo '<tr><td>Season</td><td>';
    seasonDropMenu($_GET['season'], 1);
    echo '</td></tr>';
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr><td colspan="2" align="center">';
    echo '<input class="inputbutton" type="submit" name="mode" value="Filter Events">';
    echo '</td></tr></table>';
    echo '<table style="border-width: 0px" align="center" cellpadding="3">';
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    echo '<tr><td><b>Event</td><td><b>Format</td>';
    echo '<td align="center"><b>No. Players</td>';
    echo '<td><b>Host(s)</td></tr>';
    $count = 0;
    while ($count < 100 && $thisEvent = $result->fetch_assoc()) {
        if (($onlyformat && strcmp($thisEvent['format'], $onlyformat) != 0)
     || ($onlyseries && strcmp($thisEvent['series'], $onlyseries) != 0)
     || ($onlyseason && strcmp($thisEvent['season'], $onlyseason) != 0)) {
            continue;
        }
        $dateStr = $thisEvent['start'];
        $dateArr = explode(' ', $dateStr);
        $date = $dateArr[0];
        echo '<tr><td>';
        echo "<a href=\"eventreport.php?event={$thisEvent['name']}\">";
        echo "{$thisEvent['name']}</a></td>";
        echo "<td>{$thisEvent['format']}</td>";
        echo "<td align=\"center\">{$thisEvent['players']}</td>";
        echo "<td>{$thisEvent['host']}";
        $ch = $thisEvent['cohost'];
        if (!is_null($ch) && strcmp($ch, '') != 0) {
            echo "/$ch";
        }
        echo '</td>';
        echo '</tr>';
        $count = $count + 1;
    }

    $result->close();

    if ($count == 100) {
        echo '<tr><td colspan="5" width="500">&nbsp;</td></tr>';
        echo '<tr><td colspan="5" align="center">';
        echo '<i>This list only shows the 100 most recent results. ';
        echo 'Please use the filters at the top of this page to find older ';
        echo 'results.</i></td></tr>';
    }

    echo '<tr><td colspan="5" width="500">&nbsp;</td></tr>';
    echo '</table></form>';
}

function showReport($event)
{
    // TODO: use $event->id instead
    $can_prereg = $event->prereg_allowed && Database::single_result_single_param('SELECT `start` > NOW() AS okay FROM events WHERE `name` = ?;', 's', $event->name);
    echo '<div id="EventReport">';
    echo "<table width=900>\n";
    echo '<tr><td width=300 valign="top">';
    imageCell($event);
    echo '</td><td width=300 valign="top">';
    infoCell($event);
    echo '</td><td  width=300 valign="top">';
    if ($event->finalized) {
        trophyCell($event);
    }

    echo '</td></tr></table>';
    echo "<table style=\"border-width: 0px;\" width=600>\n<tr><td>";
    if ($event->finalized) {
        finalists($event);
    } elseif ($can_prereg) {
        prereg($event);
    }
    echo '</td><td align="right">';
    if ($event->active || $event->finalized) {
        metastats($event);
    }
    echo "</td></tr></table>\n</div>";
    echo '<br /><br />';
    fullmetagame($event);
}

function finalists($event)
{
    $nfinalists = count($event->getFinalists());
    echo "<table>\n";
    echo "<tr><td colspan=5 align=\"center\"><b>TOP {$nfinalists}</td></tr>\n";
    foreach ($event->getFinalists() as $finalist) {
        $finaldeck = new Deck($finalist['deck']);
        if ($finaldeck->new) {
            $deckinfoarr = deckInfo(null);
        } else {
            $deckinfoarr = deckInfo($finaldeck);
        }
        $redstar = '<font color="#FF0000">*</font>';
        $append = ' '.$finalist['medal'];
        if ($finalist['medal'] == 't8' || $finalist['medal'] == 't4') {
            $append = ' '.strtoupper($finalist['medal']);
        }
        $medalSTR = medalImgStr($finalist['medal']);
        $medalSTR .= $append;

        $manaSTR = image_tag("mana{$deckinfoarr[1]}.png", ['alt' => 'Deck Colors']);
        $deckSTR = $finaldeck->linkTo();
        if (!$finaldeck->isValid()) {
            $deckSTR .= $redstar;
        }
        $thisplayer = new Player($finalist['player']);
        $playerSTR = "by {$thisplayer->linkTo()}";
        echo "<tr><td width=\"40\">$medalSTR</td>\n<td>$manaSTR</td><td width=\"250\">$deckSTR</td>\n";
        echo "<td align=\"right\" width=\"250\">$playerSTR</td></tr>\n";
    }
    echo '</table>';
}

function prereg($event)
{
    echo "<table>\n";
    echo "<tr><td colspan=5 align=\"center\"><h3>Registration Open</h3></td></tr>\n";
    echo '<tr><td>';
    $player = Player::getSessionPlayer();
    if (is_null($player)) {
        echo "<a href='login.php'>Sign in</a> or <a href='register.php'>make an account</a> to Register";
    } elseif ($event->hasRegistrant($player->name)) {
        echo 'You are registered for this event! <a href="prereg.php?action=unreg&event='.rawurlencode($event->name).'">(Unreg)</a>';
    } elseif ($event->is_full()) {
        echo 'This event is currently at capacity.';
    } else {
        echo '<a href="prereg.php?action=reg&event='.rawurlencode($event->name).'">Register for '.$event->name.'</a>';
    }
    echo '</td></tr>';

    echo '</table>';
}

function metastats($event)
{
    $archcnt = initArchetypeCount();
    $colorcnt = ['w' => 0, 'g' => 0, 'u' => 0, 'r' => 0, 'b' => 0];
    $decks = $event->getDecks();
    $ndecks = count($decks);
    foreach ($decks as $deck) {
        $deckarr = deckInfo($deck);
        if ($deckarr[1] != 'blackout') {
            $archcnt[$deckarr[3]]++;
            $colors = str_split($deckarr[1]);
            foreach ($colors as $color) {
                $colorcnt[$color]++;
            }
        } else {
            $ndecks--;
        }
    }

    echo "<table style=\"border-width: 0px;\" width=200>\n";
    echo "<tr><td colspan=5 align=\"center\"><b>Metagame Stats</td></tr>\n";
    foreach ($archcnt as $arch => $cnt) {
        if ($cnt > 0) {
            $pcg = round(($cnt / $ndecks) * 100);
            echo "<tr><td colspan=4 align=\"left\">$arch</td>";
            echo "<td align=\"right\">$pcg%</td></tr>\n";
        }
    }
    echo '<tr><td>&nbsp;</td></tr><tr>';
    echo '<td align="center">'.image_tag('manaw.png')."</td>\n";
    echo '<td align="center">'.image_tag('manag.png')."</td>\n";
    echo '<td align="center">'.image_tag('manau.png')."</td>\n";
    echo '<td align="center">'.image_tag('manar.png')."</td>\n";
    echo '<td align="center">'.image_tag('manab.png')."</td>\n";
    echo '</tr>';
    echo '<tr>';
    foreach ($colorcnt as $col => $cnt) {
        if ($col != '') {
            if ($ndecks > 0) {
                $pcg = round(($cnt / $ndecks) * 100);
            } else {
                $pcg = '??';
            }
            echo "<td align=\"center\">$pcg%</td>\n";
        }
    }
    echo "</tr>\n";
    echo "</table>\n";
}

function fullmetagame($event)
{
    $decks = $event->getDecks();
    $players = [];
    foreach ($decks as $deck) {
        $info = ['player'          => $deck->playername, 'deckname' => $deck->name,
            'archetype'            => $deck->archetype, 'medal' => $deck->medal,
            'id'                   => $deck->id, ];
        $arr = deckInfo($deck);
        $info['colors'] = $arr[1];
        if ($info['medal'] == 'dot') {
            $info['medal'] = 'z';
        }
        $players[] = $info;
    }
    $db = Database::getConnection();
    $succ = $db->query('CREATE TEMPORARY TABLE meta(
		player VARCHAR(40), deckname VARCHAR(40), archetype VARCHAR(20),
		colors VARCHAR(10), medal VARCHAR(10), id BIGINT UNSIGNED,
    srtordr TINYINT UNSIGNED DEFAULT 0)');
    if (!$succ) throw new Exception($db->error, 1);

    $stmt = $db->prepare('INSERT INTO meta(player, deckname, archetype,	colors, medal, id)
    VALUES(?, ?, ?, ?, ?, ?)');
    for ($ndx = 0; $ndx < count($players); $ndx++) {
        $stmt->bind_param(
            'sssssd',
            $players[$ndx]['player'],
            $players[$ndx]['deckname'],
            $players[$ndx]['archetype'],
            $players[$ndx]['colors'],
            $players[$ndx]['medal'],
            $players[$ndx]['id']
        );
        if(!$stmt->execute()) throw new Exception($stmt->error, 1);
    }
    $stmt->close();
    $result = $db->query('SELECT colors, COUNT(player) AS cnt FROM meta GROUP BY(colors)');
    $stmt = $db->prepare('UPDATE meta SET srtordr = ? WHERE colors = ?');
    while ($row = $result->fetch_assoc()) {
        $stmt->bind_param('ds', $row['cnt'], $row['colors']);
        if(!$stmt->execute()) throw new Exception($stmt->error, 1);
    }
    $stmt->close();
    $result->close();
    $result = $db->query('SELECT player, deckname, archetype, colors, medal, id, srtordr
		FROM meta ORDER BY srtordr DESC, colors, medal, player');
    $color = 'orange';
    echo '<table style="border-width: 0px;" align="center">';
    $hg = headerColor();
    echo '<tr style="">';
    if ($event->decklistsVisible()) {
        echo "<td colspan=5 align=\"center\"><b>Metagame Breakdown</td></tr>\n";
        while ($row = $result->fetch_assoc()) {
            if ($row['colors'] != $color) {
                $bg = rowColor();
                $color = $row['colors'];
                echo '<tr><td>';
                echo image_tag("mana{$color}.png")."&nbsp;</td>\n";
                echo "<td colspan=4 align=\"left\"><i>{$row['srtordr']} Players ";
                echo "</td></tr>\n";
            }
            // ironically its this error on the next <tr> by having no style that creates the look I liked for the mana
            // symbols to be right next to the Players text.
            // echo "<tr style=\"><td></td>\n";
            echo "<tr style=\"><td></td>\n";
            echo '<td align="left">';
            echo "</td>\n<td align=\"left\">";

            if ($row['medal'] != 'z') {  // puts medal next to name of person who won it
                echo medalImgStr($row['medal']).'&nbsp;';
            }
            $play = new Player($row['player']);
            $entry = new Entry($event->id, $play->name);
            echo $play->linkTo()."</td>\n";
            echo "<td align=\left\">{$entry->recordString()}</td>";
            echo '<td align="left">';
            echo "<a href=\"deck.php?mode=view&id={$row['id']}\">";
            echo "{$row['deckname']}</a></td>\n";
            echo "<td align=\"right\">{$row['archetype']}</td></tr>\n";
        }
    } else {
        echo "<td colspan=5 align=\"center\"><b><h3>Registered Players</h3></td></tr>\n";
        echo '<center><h2><em>Deck lists are not shown for privacy until event is finalized.</em></h2></center>';
        while ($row = $result->fetch_assoc()) {
            $play = new Player($row['player']);
            $entry = new Entry($event->id, $play->name);
            $format = new Format($event->format);
            // $deck = new Deck($entry->deck);

            echo '<tr><td>';
            if ($format->tribal && ($event->current_round > 1)) {
                echo '<td>'.$entry->deck->tribe.'</td>';
            }
            echo '<td>'.$play->linkTo()."</td><td align=\left\">{$entry->recordString()}</td></tr>";
        }
    }
    if ($event->active || $event->finalized) {
        if (isset($_SESSION['username'])) {
            Standings::printEventStandings($event->name, $_SESSION['username']);
        } else {
            Standings::printEventStandings($event->name, null);
        }
    }
    $result->close();
    echo "</table>\n";
}

function deckInfo($deck)
{
    $ret = ['No Deck Submitted', 'blackout', 0, 'Unclassified'];
    if (!is_null($deck)) {
        $colorstr = '';
        $row = $deck->getColorCounts();
        if ($row['w'] > 0) {
            $colorstr .= 'w';
        }
        if ($row['g'] > 0) {
            $colorstr .= 'g';
        }
        if ($row['u'] > 0) {
            $colorstr .= 'u';
        }
        if ($row['r'] > 0) {
            $colorstr .= 'r';
        }
        if ($row['b'] > 0) {
            $colorstr .= 'b';
        }
        $row['cnt'] = array_sum($deck->maindeck_cards);
        if ($colorstr == '') {
            $colorstr = 'blackout';
        }
        $ret = [$deck->name, $colorstr, $row['cnt'], $deck->archetype];
    }

    return $ret;
}

function initArchetypeCount()
{
    $ret = [];
    $db = Database::getConnection();
    $result = $db->query('SELECT name FROM archetypes ORDER BY priority DESC');
    while ($row = $result->fetch_assoc()) {
        $ret[$row['name']] = 0;
    }
    $result->close();

    return $ret;
}

function imageCell($event)
{
    echo "<div class=\"series-logo\"><img src=\"displaySeries.php?series=$event->series\" alt=\"Series Logo\" /></div>";
}

function infoCell($event)
{
    if (!is_null($event->threadurl)) {
        echo "<a href=\"{$event->threadurl}\">{$event->name}</a><br />\n";
    } else {
        echo "{$event->name}<br />\n";
    }

    $date = date('j F Y', strtotime($event->start));
    echo "$date<br />\n";
    echo "{$event->format} &middot\n";
    $playercount = $event->getPlayerCount();
    echo "{$playercount} Players<br />\n";
    $deckcount = count($event->getDecks());
    echo "{$deckcount} Decks";
    if ($event->active) {
        echo ' &middot; ';
        if ($playercount == 0) {
            $deckpercentexact = 0;
            $deckpercent = 0;
        } else {
            $deckpercentexact = round($deckcount * 100 / $playercount, 2);
            $deckpercent = round($deckcount * 100 / $playercount, 0);
        }
        if ($deckpercentexact == $deckpercent) {
            echo "{$deckpercent}% Reported";
        } else {
            echo "~{$deckpercent}% Reported";
        }
    }
    echo "<br />\n";
    foreach ($event->getSubevents() as $subevent) {
        if ($subevent->type != 'Single Elimination') {
            echo "{$subevent->rounds} rounds {$subevent->type}<br />\n";
        } else {
            $finalists = pow(2, $subevent->rounds);
            echo "Top $finalists playoff<br />\n";
        }
    }
    $host = new Player($event->host);
    echo "Hosted by {$host->linkTo()}<br />";
    if (!is_null($event->reporturl)) {
        echo "<a href=\"{$event->reporturl}\">Event Report</a><br />\n";
    }
    echo "<a href=\"seriesreport.php?series={$event->series}&season={$event->season}\">Season Leaderboard</a>";
}

function trophyCell($event)
{
    if ($event->hastrophy) {
        echo Event::trophy_image_tag($event->name)."<br />\n";
    } else {
        echo image_tag('notrophy.png');
    }
    $deck = $event->getPlaceDeck('1st');
    $player = $event->getPlacePlayer('1st');
    if (!$player) {
        echo '<br />No winner yet!';
    } else {
        $playerwin = new Player($player);
        echo $playerwin->linkTo();
        $info = deckInfo($deck);
        echo image_tag("mana{$info[1]}.png");
        echo $deck->linkTo();
        echo "<br />\n";
    }
}
?>
