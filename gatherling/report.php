<?php

declare(strict_types=1);

use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;

use function Gatherling\Views\get;
use function Gatherling\Views\post;
use function Gatherling\Views\request;

require_once 'lib.php';
require_once 'lib_form_helper.php';
$player = Player::getSessionPlayer();
$result = '';
if ($player == null) {
    linkToLogin('your Player Control Panel');
} else {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'verify_result' && !isset($_POST['drop'])) {
            $_POST['action'] = 'finalize_result';
            $_REQUEST['action'] = 'finalize_result';
            if (!isset($_POST['opponent'])) {
                $_POST['opponent'] = '0';
            }
        }
        if ($_POST['action'] == 'finalize_result') {
            // write results to matches table
            $drop = false;
            if (isset($_POST['drop'])) {
                $drop = $_POST['drop'] == 'Y';
            }
            if ($drop) {
                $match = new Matchup(post()->int('match_id'));
                $eventname = $match->getEventNamebyMatchid();
                $event = new Event($eventname);
                $event->dropPlayer($player->name);
            }
            if ($_POST['opponent'] != '0') {
                $event = new Event(post()->string('event'));
                if ($event->isLeague()) {
                    $player = new Standings($event->name, post()->string('player'));
                    $opponent = new Standings($event->name, post()->string('opponent'));
                    $new_match_id = $event->addPairing($player, $opponent, $event->current_round, 'P');
                    Matchup::saveReport(post()->string('report'), $new_match_id, 'a');
                    redirect('player.php');

                    return;
                } else {
                    $result = 'This is not a league event!';
                }
            } else {
                // Non-league matches
                $match = new Matchup(post()->int('match_id'));
                if ($match->playerLetter($player->name) == $_POST['player']) {
                    Matchup::saveReport(post()->string('report'), post()->int('match_id'), post()->string('player'));
                    redirect('player.php');

                    return;
                } else {
                    $result = 'Results appear to be tampered.  Please only submit your own results.';
                }
            }
        } elseif ($_POST['action'] == 'drop') {
            // drop player from event
            $event = new Event(post()->string('event'));
            $event->dropPlayer($player->name);
            redirect('player.php');

            return;
        }
    }
}
print_header('Player Control Panel');
?>
<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Player Control Panel </div>
<?php

    // Handle modes
    $dispmode = 'playercp';
if (isset($_REQUEST['mode'])) {
    $dispmode = $_REQUEST['mode'];
}

switch ($dispmode) {
    case 'submit_result':
        if (!isset($_GET['match_id'])) {
            // print_mainPlayerCP($player, '');
            redirect('player.php');
            break;
        }
        print_submit_resultForm(get()->int('match_id'));
        break;

    case 'submit_league_result':
        League_print_submit_resultForm(request()->string('event'), request()->int('round'), $player, request()->string('subevent'));
        break;

    case 'verify_result':
    case 'verify_league_result':
        if (isset($_POST['report'])) {
            $drop = (isset($_POST['drop'])) ? 'Y' : 'N';
            $opponent = request()->string('opponent', '0');
            $eventName = request()->string('event', '0');

            print_verify_resultForm(post()->string('report'), post()->int('match_id'), post()->string('player'), $drop, $opponent, $eventName);
        } else {
            print_submit_resultForm(request()->int('match_id'));
        }
        break;

    case 'drop_form':
        $matches = $player->getCurrentMatches();
        $event_name = request()->string('event', '');
        $can_drop = true;
        foreach ($matches as $match) {
            if (strcasecmp($event_name, $match->getEventNamebyMatchid()) != 0) {
                continue;
            }
            if ($match->verification == 'unverified') {
                $player_number = $match->playerLetter($player->name);
                if ($player_number == 'b' and ($match->playerb_wins + $match->playerb_losses) > 0) {
                    // Fine.
                } elseif ($player_number == 'a' and ($match->playera_wins + $match->playera_losses) > 0) {
                    // Also Fine
                } else {
                    if ($match->player_reportable_check() == true) {
                        $can_drop = false;
                    }
                }
            } elseif ($match->verification == 'failed') {
                $can_drop = false;
            }
        }

        if ($can_drop) {
            print_dropConfirm($event_name, $player);
        } else {
            print_submit_resultForm($match->id, true);
        }
        break;
    default:
        echo "$result<br/>Unknown dispmode: $dispmode";
    // redirect('player.php');

        return;
}
?>
</div> <!-- gatherling_main box -->
</div> <!-- grid 10 suff 1 pre 1 -->

<?php print_footer(); ?>

<?php
//Drop confirmation form
function print_dropConfirm(string $event_name, ?Player $player): void
{
    echo '<center><h3>Drop Form</h3>';
    echo "<center style=\"color: red; font-weight: bold;\">
    Are you sure you want to drop? This cannot be undone. </center>\n";
    echo "<center bold;\">Please be sure to submit a result for any active matches before you leave.</center>\n";
    echo '<table class="form">';
    echo '<tr><th>';
    echo "<form action=\"report.php\" method=\"post\">\n";
    echo "<input name=\"action\" type=\"hidden\" value=\"drop\" />\n";
    echo "<input name=\"event\" type=\"hidden\" value=\"{$event_name}\" />\n";
    echo "<input name=\"player\" type=\"hidden\" value=\"{$player->name}\" />\n";
    echo "<input class=\"inputbutton\" name=\"submit\" type=\"submit\" value=\"Drop from Event\" />\n";
    echo '<td> ';
    echo "</form>\n";
    echo "<form action=\"player.php\" method=\"get\">\n";
    echo "<input class=\"inputbutton\" name=\"submit\" type=\"submit\" value=\"Cancel\" />\n";
    echo "</td> </tr> \n";
    echo "<tr> <td colspan=\"2\" class=\"buttons\">\n";
    echo "</td> </tr> </table> \n";
    echo "</form>\n";
}

function print_submit_resultForm(int $match_id, bool $drop = false): void
{
    $match = new Matchup($match_id);
    $event = new Event($match->getEventNamebyMatchid());
    $letter = $match->playerLetter(Player::getSessionPlayer()->name);
    if ($letter == 'a') {
        $opp = $match->playerb;
    } else {
        $opp = $match->playera;
    }
    $oppplayer = new Player($opp);
    echo "<center><h3>Report Game Results</h3>
    Enter results for <em>$event->name</em> round $event->current_round vs. {$oppplayer->gameName($event->client)}</center>\n";

    echo "<form action=\"report.php\" method=\"post\">\n";
    echo "<input name=\"mode\" type=\"hidden\" value=\"verify_result\" />\n";
    echo "<input name=\"action\" type=\"hidden\" value=\"verify_result\" />\n";
    echo "<input name=\"match_id\" type=\"hidden\" value=\"{$match_id}\" />\n";
    echo "<input name=\"player\" type=\"hidden\" value=\"{$letter}\" />\n";
    echo '<table class="form">';
    echo "<tr><td><input type='radio' name='report' value='W20' /> I won the match 2-0</td> </tr>";
    echo "<tr><td><input type='radio' name='report' value='W21' />I won the match 2-1</td> </tr>";
    echo "<tr><td><input type='radio' name='report' value='L20' />I lost the match 0-2 </td> </tr>";
    echo "<tr><td><input type='radio' name='report' value='L21' />I lost the match 1-2</td> </tr>";
    if ($match->allowsPlayerReportedDraws() == 1) {
        echo "<tr><td><input type='radio' name='report' value='D' />The match was a draw</td> </tr>";
    }
    echo '<tr><td></td></tr>';
    if ($match->type !== 'Single Elimination') {
        echo checkboxInput('I want to drop from this event', 'drop', $drop);
    }
    echo '<tr><td></td></tr>';
    echo '<tr><td class="buttons">';
    echo '<input class="inputbutton" name="submit" type="submit" value="Submit Match Report" />';
    echo '</td></tr></table>';
    echo '</form>';
    echo '<div class="clear"></div>';
}

function League_print_submit_resultForm(string $event, int $round, ?Player $player, string $subevent): void
{
    echo "<center><h3>Report League Game Results</h3>
    Enter results</center>\n";
    echo "<center style=\" font-weight: bold;\">Opponent</center>\n";
    echo "<form action=\"report.php\" method=\"post\">\n";
    echo "<input name=\"mode\" type=\"hidden\" value=\"verify_league_result\" />\n";
    echo "<input name=\"match_id\" type=\"hidden\" value=\"0\" />\n";
    echo "<input name=\"event\" type=\"hidden\" value=\"{$event}\" />\n";
    echo "<input name=\"player\" type=\"hidden\" value=\"{$player->name}\" />\n";
    echo '<table class="form">';

    echo '<tr><th>';
    leagueOpponentDropMenu($event, $round, $player, (int) $subevent);

    echo "<br /></th>\n";
    echo "<td></td></tr>\n";
    echo "<tr><th><input type='radio' name='report' value='W20' /> I won the match 2-0<br /></th>\n";
    echo "<td></td> </tr> \n";
    echo "<tr><th><input type='radio' name='report' value='W21' /> I won the match 2-1</th>\n";
    echo "<td></td> </tr> \n";
    // Allowing the loser to report leads to race conditions :/
    // echo "<tr><th><input type='radio' name='report' value='L20' /> I lost the match 0-2<br /></th>\n";
    // echo "<td></td> </tr> \n";
    // echo "<tr><th><input type='radio' name='report' value='L21' /> I lost the match 1-2</th>\n";
    echo "<td></td> </tr> \n";
    echo "<tr> <td colspan=\"2\" class=\"buttons\">\n";
    echo "<input class=\"inputbutton\" name=\"submit\" type=\"submit\" value=\"Submit Match Report\" />\n";
    echo "</td> </tr> </table> \n";
    echo "</form>\n";
    echo "<div class=\"clear\"> </div>\n";
}

/** form to confirm submission.
 */
function print_verify_resultForm(string $report, int $match_id, string $player, string|int $drop, string $opponent, string $event): void
{
    echo "<center><h3><br>Confirm Game Results</p></h3></center>\n";
    echo "<center style=\"color: red; font-weight: bold;\">Please confirm your entry.</center></p>\n";
    echo '<center><h4>';
    if ($opponent != '0') {
        echo 'Opponent: '.$opponent.'<br />';
    }
    switch ($report) {
        case 'W20':
            echo 'I won the match 2-0';
            break;
        case 'W21':
            echo 'I won the match 2-1';
            break;
        case 'L20':
            echo 'I lost the match 0-2';
            break;
        case 'L21':
            echo 'I lost the match 1-2';
            break;
        case 'D':
            echo 'The match was a draw';
            break;
    }
    if ($drop == 1) {
        $drop = 'Y';
    }
    if ($drop == 'Y') {
        echo "</p><center style=\"color: red; font-weight: bold;\">I want to drop out of this event.</center>\n";
    }
    echo '</center></h4></p>';

    echo '<table class="form">';
    echo '<tr><th>';
    echo "<form action=\"report.php\" method=\"post\">\n";
    if ($drop == 'Y') {
        echo "<input name=\"drop\" type=\"hidden\" value=\"Y\" />\n";
    }
    echo "<input name=\"action\" type=\"hidden\" value=\"finalize_result\" />\n";
    echo "<input name=\"match_id\" type=\"hidden\" value=\"{$match_id}\" />\n";
    echo "<input name=\"report\" type=\"hidden\" value=\"{$report}\" />\n";
    echo "<input name=\"opponent\" type=\"hidden\" value=\"{$opponent}\" />\n";
    echo "<input name=\"player\" type=\"hidden\" value=\"{$player}\" />\n";
    echo "<input name=\"event\" type=\"hidden\" value=\"{$event}\" />\n";
    echo "<input name=\"submit\" type=\"submit\" value=\"Verify Match Report\" />\n";
    echo "</form>\n";
    echo "</th>\n";
    echo '<td> ';
    echo "<form action=\"report.php\" method=\"get\">\n";
    echo "<input name=\"match_id\" type=\"hidden\" value=\"{$match_id}\" />\n";
    echo "<input name=\"player\" type=\"hidden\" value=\"{$player}\" />\n";
    echo "<input name=\"mode\" type=\"hidden\" value=\"submit_result\" />\n";
    echo "<input name=\"submit\" type=\"submit\" value=\"Go Back and Correct\" />\n";
    echo "</form>\n";
    echo "</td> </tr> \n";
    echo "<tr> <td colspan=\"2\" class=\"buttons\">\n";

    echo "</td> </tr> </table> \n";
    echo "</form>\n";
    echo "<div class=\"clear\"> </div>\n";
}
