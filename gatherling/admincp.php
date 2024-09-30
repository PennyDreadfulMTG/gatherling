<?php

declare(strict_types=1);

use Gatherling\Models\Format;
use Gatherling\Models\Player;
use Gatherling\Models\Ratings;
use Gatherling\Models\Series;
use Gatherling\Models\SetScraper;

require_once 'lib.php';
include 'lib_form_helper.php';

$hasError = false;
$errormsg = '';

if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
    redirect('index.php');
}

print_header('Admin Control Panel');
?>

<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Admin Control Panel </div>
<center>
<?php do_page(); ?>
</div>
<?php print_footer(); ?>

<?php

function do_page(): void
{
    $player = Player::getSessionPlayer();
    if (!$player->isSuper()) {
        printNoAdmin();

        return;
    }

    printAdminCPIntroduction();
    handleActions();
    printError();
    adminCPMenu();

    $view = 'change_password';

    if (isset($_GET['view']) && ($_GET['view'] != '')) {
        $view = $_GET['view'];
    }
    if (isset($_POST['view'])) {
        $view = $_POST['view'];
    }

    if ($view == 'no_view') {
        // Show Nothing
    } elseif ($view == 'change_password') {
        printChangePasswordForm();
    } elseif ($view == 'create_series') {
        printCreateNewSeriesForm();
    } elseif (($view == 'add_cardset')) {
        printAddCardSet();
    } elseif ($view == 'calc_ratings') {
        printCalcRatingsForm();
    } elseif ($view == 'verify_player') {
        printManualVerificationForm();
    }

    echo '</center><div class="clear"></div></div>';
}

function printAdminCPIntroduction(): void
{
    echo 'Welcome to the Admin CP! <br />';
}

function printNoAdmin(): void
{
    global $hasError;
    global $errormsg;
    $hasError = true;
    $errormsg = "<center>You're not an Admin here on Gatherling.com! Access Restricted.<br />";
    printError();
    echo '<a href="player.php">Back to the Player Control Panel</a></center>';
}

function printError(): void
{
    global $hasError;
    global $errormsg;
    if ($hasError) {
        echo "<div class=\"error\">{$errormsg}</div>";
    }
}

function adminCPMenu(): void
{
    echo '<table><tr><td colspan="2" align="center">';
    echo '<a href="admincp.php?view=change_password">Change Player Password</a>';
    echo ' | <a href="admincp.php?view=verify_player">Manually Verify Player</a>';
    echo ' | <a href="admincp.php?view=create_series">Create New Series</a>';
    echo ' | <a href="admincp.php?view=calc_ratings">Maintenance Tasks</a>';
    echo ' | <a href="admincp.php?view=add_cardset">Add New Cardset</a>';
    echo '</td></tr></table>';
}

function printCreateNewSeriesForm(): void
{
    echo '<h4>Create New Series</h4>';
    echo '<form action="admincp.php" method="post">';
    echo '<input type="hidden" name="view" value="create_series" />';
    echo '<table class="form c">';
    echo '<tr><td colspan="2">New Series Name: <input class="inputbox" type="text" name="seriesname" STYLE="width: 175px"/></td></tr>';

    // Active
    echo '<tr><th> Series is Active </th> <td> ';
    echo '<select class="inputbox" name="isactive"> <option value="1">Yes</option> <option value="0" selected>No</option></select>';
    echo '</td></tr>';

    // Start day
    echo '<tr><th>Normal Start Day</th><td> ';
    echo '<select class="inputbox" name="start_day">';
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    foreach ($days as $dayofweek) {
        echo "<option>{$dayofweek}</option>";
    }
    echo '</select>';
    echo '</td></tr>';

    // Start time
    echo '<tr><th>Normal start time</th><td> ';
    $time_parts = explode(':', '12:00:00');
    echo timeDropMenu($time_parts[0], $time_parts[1]);
    echo '</td> </tr>';

    // Pre-registration on by default?
    echo '<tr><th>Pre-Registration Default</th>';
    echo '<td><input type="checkbox" value="1" name="preregdefault" /></td></tr>';

    // Submit button
    echo '<tr><td colspan="2" class="buttons">';
    echo '<input class="inputbutton" type="submit" name="action" value="Create Series" /></td></tr>';
    echo '</table></form>';
}

function printCalcRatingsForm(): void
{
    $ratings = new Ratings();
    echo '<h4>Calculate Ratings</h4>';
    echo '<form action="admincp.php" method="post">';
    echo '<input type="hidden" name="view" value="calc_ratings" />';
    echo '<table class="form c">';
    echo '<tr><td class="buttons">';
    echo '<input class="inputbutton" type="submit" name="action" value="Re-Calculate All Ratings" /></td></tr>';
    echo '<tr><td class="buttons">';
    echo '<tr><td>Select a rating to Re-Calculate: ';
    echo $ratings->formatDropMenuR();
    echo '&nbsp;';
    echo '<input class="inputbutton" type="submit" name="action" value="Re-Calcualte By Format" /></td></tr>';
    echo '</table>';
    echo '<h4>Rebuild tribal database</h4>';
    echo '<table class="form c">';
    echo '<tr><td class="buttons">';
    echo '<input class="inputbutton" type="submit" name="action" value="Rebuild Tribes" /></td></tr>';
    echo '</table></form>';
}

function printAddCardSet(): void
{
    echo '<h3><center>Install New Cardset</center></h3>';
    echo '<form action="util/insertcardset.php" method="post" enctype="multipart/form-data">';
    echo '<table class="form c">';
    echo '<input type="hidden" name="return" value="admincp.php" />';
    echo '<input type="hidden" name="ret_args" value="view=add_cardset" />';
    print_file_input('Cardset JSON', 'cardsetfile');
    echo submit('Install New Cardset');
    echo '</table></form>';

    echo '<form action="util/insertcardset.php" method="post" enctype="multipart/form-data">';
    flush();
    echo '<h3><center>Or</center></h3>';
    echo '<table class="form c">';
    echo '<input type="hidden" name="return" value="admincp.php" />';
    echo '<input type="hidden" name="ret_args" value="view=add_cardset" />';
    // print_text_input("Cardset code", "cardsetcode");
    $missing = SetScraper::getSetList();
    echo selectInput('Missing Sets', 'cardsetcode', $missing);
    echo submit('Install New Cardset');
    echo '</table></form>';
}

function printChangePasswordForm(): void
{
    echo '<form action="admincp.php" method="post">';
    echo '<input type="hidden" name="view" value="change_password" />';
    echo '<h3><center>Change User Password</center></h3>';
    echo '<table class="form c">';
    echo textInput('Username', 'username');
    echo textInput('New Password', 'new_password');
    echo submit('Change Password');
    echo '</table> </form>';
}

function printManualVerificationForm(): void
{
    echo '<form action="admincp.php" method="post">';
    echo '<input type="hidden" name="view" value="verify_player" />';
    echo '<h3><center>Manual Player Verification</center></h3>';
    echo '<table class="form c">';
    echo textInput('Username', 'username');
    echo submit('Verify Player');
    echo '</table> </form>';
}

function handleActions(): void
{
    if (!isset($_POST['action'])) {
        return;
    }
    global $hasError;
    global $errormsg;
    if ($_POST['action'] == 'Change Password') {
        $hasError = true;
        $username = $_POST['username'];

        try {
            $player = new Player($username);
            $player->setPassword($_POST['new_password']);
            $errormsg = "Password changed for user {$player->name} to {$_POST['new_password']}";
        } catch (Exception $e) {
            $errormsg = "User $username is not found.";
        }
    } elseif ($_POST['action'] == 'Verify Player') {
        $hasError = true;
        $player = new Player($_POST['username']);
        $player->setVerified(true);
        $errormsg = "User {$player->name} is now verified.";
    } elseif ($_POST['action'] == 'Create Series') {
        $hasError = true;
        $newactive = (int) $_POST['isactive'];
        $newtime = $_POST['hour'];
        $newday = $_POST['start_day'];
        $prereg = 0;

        if (isset($_POST['preregdefault'])) {
            $prereg = $_POST['preregdefault'];
        } else {
            $prereg = 0;
        }

        $series = new Series('');
        $newseries = $_POST['seriesname'];
        if ($series->authCheck(Player::loginName())) {
            $series->name = $newseries;
            $series->active = $newactive;
            $series->start_time = $newtime . ':00';
            $series->start_day = $newday;
            $series->prereg_default = $prereg;
            $series->save();
        }
        $errormsg = "New series $series->name was created!";
    } elseif ($_POST['action'] == 'Re-Calculate All Ratings') {
        $ratings = new Ratings();
        $ratings->deleteAllRatings();
        $ratings->calcAllRatings();
    } elseif ($_POST['action'] == 'Re-Calcualte By Format') {
        $ratings = new Ratings();
        $ratings->deleteRatingByFormat($_POST['format']);
        if ($_POST['format'] == 'Composite') {
            $ratings->calcCompositeRating();
        } else {
            $ratings->calcRatingByFormat($_POST['format']);
        }
    } elseif ($_POST['action'] == 'Rebuild Tribes') {
        Format::constructTribes('All');
    }
}

function printNewFormat(): void
{
    echo "<h4>New Format</h4>\n";
    echo '<form action="admincp.php" method="post">';
    echo '<input type="hidden" name="view" value="no_view" />';
    echo '<table class="form c">';
    echo '<tr><td colspan="2">New Format Name: <input type="text" name="newformatname" STYLE="width: 175px"/></td></tr>';
    echo '<td colspan="2" class="buttons">';
    echo '<input class="inputbutton" type="submit" value="Create New Format" name ="action" /></td></tr>';
    echo'</table></form>';
}
