<?php
require_once 'lib.php';
require_once 'lib_form_helper.php';
session_start();
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
                $match = new Match($_POST['match_id']);
                $eventname = $match->getEventNamebyMatchid();
                $event = new Event($eventname);
                $event->dropPlayer($player->name);
            }
            if ($_POST['opponent'] != '0') {
                $event = new Event($_POST['event']);
                if ($event->isLeague()) {
                    $player = new Standings($event->name, $_POST['player']);
                    $opponent = new Standings($event->name, $_POST['opponent']);
                    $new_match_id = $event->addPairing($player, $opponent, $event->current_round, 'P');
                    Match::saveReport($_POST['report'], $new_match_id, 'a');
                    redirect('player.php');

                    return;
                } else {
                    $result = 'This is not a league event!';
                }
            } else {
                // Non-league matches
                $match = new Match($_POST['match_id']);
                if ($match->playerLetter($player->name) == $_POST['player']) {
                    Match::saveReport($_POST['report'], $_POST['match_id'], $_POST['player']);
                    redirect('player.php');

                    return;
                } else {
                    $result = 'Results appear to be tampered.  Please only submit your own results.';
                }
            }
        } elseif ($_POST['action'] == 'drop') {
            // drop player from event
            $event = new Event($_POST['event']);
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
        print_mainPlayerCP($player, '');
        break;
    }
    print_submit_resultForm($_GET['match_id']);
    break;

    case 'submit_league_result':
    League_print_submit_resultForm($_REQUEST['event'], $_REQUEST['round'], $player, $_REQUEST['subevent']);
    break;

    case 'verify_result':
    case 'verify_league_result':
    if (isset($_POST['report'])) {
        $drop = (isset($_POST['drop'])) ? 'Y' : 'N';
        $opponent = isset($_REQUEST['opponent']) ? $_REQUEST['opponent'] : 0;
        $event = isset($_REQUEST['event']) ? $_REQUEST['event'] : 0;

        print_verify_resultForm($_POST['report'], $_POST['match_id'], $_POST['player'], $drop, $opponent, $event);
    } else {
        print_submit_resultForm($_REQUEST['match_id']);
    }
    break;

    case 'drop_form':
        $matches = $player->getCurrentMatches();
        $event_name = $_REQUEST['event'];
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
function print_dropConfirm($event_name, $player)
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
    echo "<input name=\"submit\" type=\"submit\" value=\"Drop from Event\" />\n";
    echo '<td> ';
    echo "</form>\n";
    echo "<form action=\"report.php\" method=\"get\">\n";
    echo "<input name=\"submit\" type=\"submit\" value=\"Cancel\" />\n";
    echo "</td> </tr> \n";
    echo "<tr> <td colspan=\"2\" class=\"buttons\">\n";
    echo "</td> </tr> </table> \n";
    echo "</form>\n";
}

function print_submit_resultForm($match_id, $drop = false)
{
    $match = new Match($match_id);
    $event = new Event($match->getEventNamebyMatchid());
    $letter = $match->playerLetter(Player::getSessionPlayer()->name);
    if ($letter == 'a') {
        $opp = $match->playerb;
    } else {
        $opp = $match->playera;
    }
    $oppplayer = new Player($opp);
    echo "<center><h3>Report Game Results</h3>
    Enter results for <em>$event->name</em> round $event->current_round vs. $oppplayer->name</center>\n";

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
        print_checkbox_input('I want to drop from this event', 'drop', $drop);
    }
    echo '<tr><td></td></tr>';
    echo '<tr><td class="buttons">';
    echo '<input class="inputbutton" name="submit" type="submit" value="Submit Match Report" />';
    echo '</td></tr></table>';
    echo '</form>';
    echo '<div class="clear"></div>';
}

// *form to report League results
/**
 * @param string $event
 * @param string $round
 * @param mixed  $player
 * @param int    $subevent
 *
 * @return void
 */
function League_print_submit_resultForm($event, $round, $player, $subevent)
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
    leagueOpponentDropMenu($event, $round, $player, $subevent);

    echo "<br /></th>\n";
    echo "<td></td></tr>\n";
    echo "<tr><th><input type='radio' name='report' value='W20' /> I won the match 2-0<br /></th>\n";
    echo "<td></td> </tr> \n";
    echo "<tr><th><input type='radio' name='report' value='W21' /> I won the match 2-1</th>\n";
    echo "<td></td> </tr> \n";
    echo "<tr><th><input type='radio' name='report' value='L20' /> I lost the match 0-2<br /></th>\n";
    echo "<td></td> </tr> \n";
    echo "<tr><th><input type='radio' name='report' value='L21' /> I lost the match 1-2</th>\n";
    echo "<td></td> </tr> \n";
    echo "<tr> <td colspan=\"2\" class=\"buttons\">\n";
    echo "<input class=\"inputbutton\" name=\"submit\" type=\"submit\" value=\"Submit Match Report\" />\n";
    echo "</td> </tr> </table> \n";
    echo "</form>\n";
    echo "<div class=\"clear\"> </div>\n";
}

/** form to confirm submission.
 */
function print_verify_resultForm($report, $match_id, $player, $drop, $opponent, $event)
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
?>
