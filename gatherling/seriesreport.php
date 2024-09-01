<?php

use Gatherling\Series;

include 'lib.php';

print_header('Season Report');

?>

<div class="grid_10 prefix_1 suffix_1">

<div id="gatherling_main" class="box">
<div class="uppertitle"> Season Report </div>
<?php
selectSeason();
if (isset($_GET['series']) && isset($_GET['season'])) {
    $series = new Series($_GET['series']);
    $series->seasonStandings($series, $_GET['season']);
}
?>

</div>
</div>

<?php print_footer(); ?>
<?php
function selectSeason()
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
    echo "<input type=\"submit\" value=\"Get Season Scoreboard\" />\n";
    echo '</td></tr></table></form>';
}
