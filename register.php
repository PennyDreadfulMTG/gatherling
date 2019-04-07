<?php
include 'lib.php';
require_once 'lib_form_helper.php';

print_header('Register');
?>

<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Register for Gatherling </div>

<?php content(); ?>

</div> </div>

<?php print_footer(); ?>

<?php
function content()
{
    if (!isset($_POST['pw1'])) {
        regForm();
    } else {
        $code = doRegister();
        if ($code == 0) {
            echo 'Registration was successful. You may now ';
            echo "<a href=\"login.php\">Log In</a>.\n";
        } elseif ($code == -1) {
            echo "Passwords don't match. Please go back and try again.\n";
        } elseif ($code == -2) {
            echo 'The specified username was not found in the database ';
            echo 'Please contact jamuraa on the forums if you feel this is ';
            echo "an error.\n";
        } elseif ($code == -3) {
            echo "A password has already been created for this account.\n";
        }
    }
}

function regForm()
{
    echo "<form action=\"register.php\" method=\"post\" onsubmit=\"return validate_pw()\">\n";
    echo "<table align=\"center\" style=\"border-width: 0px\">\n";
    echo "<center id='notice'>Passwords are required to be at least 8 characters long.</center>\n";
    echo "<tr><td><b>MTGO Username</td>\n";
    echo "<td><input class=\"inputbox\" type=\"text\" name=\"username\" value=\"\">\n";
    echo "</td></tr>\n";
    echo "<tr><td><b>Password</td>\n";
    echo "<td><input class=\"inputbox\" type=\"password\" name=\"pw1\" id=\"pw\" value=\"\">\n";
    echo '</td></tr>';
    echo "<tr><td><b>Confirm Password</td>\n";
    echo "<td><input class=\"inputbox\" type=\"password\" name=\"pw2\" id=\"pw2\" value=\"\">\n";
    echo "</td></tr>\n";
    echo "<tr><td><b>Email</td>\n";
    echo "<td><input class=\"inputbox\" type=\"email\" name=\"email\" value=\"\">\n";
    echo emailStatusDropDown();
    echo "</td></tr>\n";
    echo "<tr><td><b>Time Zone</td>\n";
    echo '<td>';
    echo timeZoneDropMenu();
    echo "</td></tr>\n";
    echo "<tr><td>&nbsp;</td></tr>\n";
    echo "<tr><td align=\"center\" colspan=\"2\">\n";
    echo '<input class="inputbutton" type="submit" name="mode" value="Register Account">';
    echo "</td></tr></table></form>\n";
}

function doRegister()
{
    $code = 0;
    if (strcmp($_POST['pw1'], $_POST['pw2']) != 0) {
        $code = -1;
    }
    $player = Player::findOrCreateByName($_POST['username']);
    if (!is_null($player->password)) {
        $code = -3;
    }
    if ($code == 0) {
        $player->password = hash('sha256', $_POST['pw1']);
        $player->super = Player::activeCount() == 0;
        $player->emailAddress = $_POST['email'];
        $player->emailPrivacy = $_POST['emailstatus'];
        $player->timezone = $_POST['timezone'];
        $player->save();
    }

    return $code;
}

?>
