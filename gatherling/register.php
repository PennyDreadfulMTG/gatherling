<?php

use Gatherling\Models\Player;

require_once 'lib.php';
require_once 'lib_form_helper.php';

$result = '';
if (isset($_POST['pw1'])) {
    $code = doRegister();
    if ($code == 0) {
        $result = 'Registration was successful. You may now  <a href="login.php">Log In</a>.';
        redirect('player.php');
    } elseif ($code == -1) {
        $result = "Passwords don't match. Please go back and try again.";
    } elseif ($code == -3) {
        $result = 'A password has already been created for this account.';
        linkToLogin('your Player Control Panel', 'player.php', $result, trim($_POST['username']));
    }
}

print_header('Register');
?>

<script src="https://hcaptcha.com/1/api.js" async defer></script>
<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Register for Gatherling </div>

<?php
if (!isset($_POST['pw1'])) {
    regForm();
} else {
    echo $result;
}
?>

</div> </div>

<?php print_footer(); ?>

<?php

function regForm()
{
    echo "<form action=\"register.php\" method=\"post\" onsubmit=\"return validate_pw()\">\n";
    echo "<table align=\"center\" style=\"border-width: 0px\">\n";
    echo "<center id='notice'>Passwords are required to be at least 8 characters long.</center>\n";
    echo "<center id='notice'>Please use your MTGO username if you have one.</center>\n";
    echo "<tr><td><b>Username</td>\n";
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
    emailStatusDropDown();
    echo "</td></tr>\n";
    echo "<tr><td><b>Time Zone</td>\n";
    echo '<td>';
    echo timeZoneDropMenu();
    echo "</td></tr>\n";
    echo "<tr><td>&nbsp;</td></tr>\n";
    echo "<tr><td align=\"center\" colspan=\"2\">\n";
    echo '<div class="h-captcha" data-sitekey="6593a7b2-48b0-4a15-bb0f-c6d47c4ac0e6"></div>';
    echo '<input class="inputbutton" type="submit" name="mode" value="Register Account">';
    echo "</td></tr></table></form>\n";
}

function doRegister()
{
    $code = 0;
    if (strcmp($_POST['pw1'], $_POST['pw2']) != 0) {
        $code = -1;
    }
    if (empty($_POST['pw1']) && !isset($_SESSION['DISCORD_ID'])) {
        $code = -1;
    }
    $player = Player::findOrCreateByName(trim($_POST['username']));
    if (!is_null($player->password)) {
        $code = -3;
    }
    if ($code == 0) {
        $player->password = hash('sha256', $_POST['pw1']);
        $player->super = Player::activeCount() == 0;
        $player->emailAddress = $_POST['email'];
        $player->emailPrivacy = $_POST['emailstatus'];
        $player->timezone = $_POST['timezone'];
        if (isset($_SESSION['DISCORD_ID'])) {
            $player->discord_id = $_SESSION['DISCORD_ID'];
        }
        if (isset($_SESSION['DISCORD_NAME'])) {
            $player->discord_handle = $_SESSION['DISCORD_NAME'];
        }
        $player->save();
        $_SESSION['username'] = $_POST['username'];
    }

    return $code;
}

?>
