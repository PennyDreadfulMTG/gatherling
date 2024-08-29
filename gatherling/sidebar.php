<?php

use Gatherling\Database;
use Gatherling\Deck;

include_once 'lib.php';

echo "<div class=\"box sidecolumn\">\n";
echo "<div class=\"uppertitle\">Recent Winners</div>\n";
recentWinners();
echo "</div>\n";

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
