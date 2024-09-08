<?php

use Gatherling\Models\Series;

require_once 'lib.php';

function main(): void
{
    $series = $_GET['series'] ?? null;
    $season = $_GET['season'] ?? null;
    print_header('Season Report');
    ?>
    <div class="grid_10 prefix_1 suffix_1">
        <div id="gatherling_main" class="box">
            <div class="uppertitle">Season Report</div>
            <?php
            selectSeason($series, $season);
            if ($series && $season) {
                $seriesObj = new Series($series);
                $seriesObj->seasonStandings($seriesObj, $season);
            }
            ?>
        </div>
    </div>
    <?php
    print_footer();
}

function selectSeason(): void
{
    echo '<form action="seriesreport.php" method="get">';
    echo '<table class="form" style="border-width: 0px" align="center">';
    echo '<tr><th>Series</th><td>';
    echo Series::dropMenu($_GET['series'], true);
    echo '</td></tr>';
    echo '<tr><th>Season</th><td>';
    echo seasonDropMenu($_GET['season'], true);
    echo '</td></tr>';
    echo '<tr><td>&nbsp;</td></tr>';
    echo '<tr><td colspan="2" class="buttons">';
    echo "<input class=\"inputbutton\" type=\"submit\" value=\"Get Season Scoreboard\" />\n";
    echo '</td></tr></table></form>';
}

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    main();
}
