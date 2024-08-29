<?php

use Gatherling\Series;

include 'lib.php';
session_start();

$active_series = Series::activeNames();

print_header('Event Information'); ?>
    <div id="gatherling_main" class="box grid_12">
        <div class="uppertitle">Series Events</div>
        <?php
            foreach ($active_series as $series_name) {
                series($series_name);
            }
        ?>
    </div>
<?php print_footer();

function series($series_name) {
    $series = new Series($series_name);
    $mostRecentEvent = $series->mostRecentEvent();
    $nextEvent = $series->nextEvent();
    if (strtotime($mostRecentEvent->start) + (86400 * 7 * 4) < time() && !$nextEvent) {
        return;
    }
    $start_format = 'h:i a';
    $next_format = 'F j h:i a';
    $next_event_start = $nextEvent ? date($next_format, strtotime($nextEvent->start) - minutes(30)) . " registration" : "Not scheduled yet";
    ?>
        <div class="series">
            <h2 class="series-name"><?= $series->name ?></h2>
            <div class="series-content">
                <div class="series-logo"><?= Series::image_tag($series->name) ?></div>
                <div class="series-info">
                    <table>
                        <?php
                        $season_format_name = str_replace(' ', '', $series->this_season_format);
                        $season_format_link = 'format.php?mode=desc&id='.$season_format_name;
                        ?>
                        <tr>
                            <th> Hosted by </th>
                            <td><?= implode(", ", array_slice($series->organizers, 0, 3)) ?></td>
                        </tr>
                        <tr>
                            <th>Format</th>
                            <td><a href="<?php echo $season_format_link?>"> <?php echo $series->this_season_format ?></a></td>
                        </tr>
                        <tr>
                            <th>Regular Time</th>
                            <td><?php echo $series->start_day ?>, <?php echo date($start_format, strtotime($series->start_time)) ?> Eastern Time</td>
                        </tr>
                        <tr>
                            <th>Rules </th>
                            <td><a href="<?php echo (empty($series->this_season_master_link)) ? $mostRecentEvent->threadurl : $series->this_season_master_link ?>">Season <?php echo $series->this_season_season ?> Master Document</a></td>
                        </tr>
                        <tr>
                            <th>Most Recent Event</th>
                            <td><?php echo $mostRecentEvent->linkReport() ?></td>
                        </tr>
                        <tr>
                            <th>Next Event</th>
                            <td><?= $next_event_start ?></td>
                        </tr>
                    </table>
                </div> <!-- .series-content -->
          </div> <!-- .series-info -->

        </div> <!-- .series -->

    <?php
}
