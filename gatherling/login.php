<?php
include 'lib.php';
session_start();
$in = testLogin();
print_header('Login');

?>
<div class="grid_10 suffix_1 prefix_1">
    <div id="gatherling_main" class="box">
        <div class="uppertitle"> Login to Gatherling </div>
<?php if (isset($_POST['mode'])) {
    printLoginFailed();
} ?>
<?php if (isset($_GET['ipaddresschanged'])) {
    printIPAddressChanged();
}?>
<?php
  if (isset($_REQUEST['message'])) {
      $message = filter_var($_REQUEST['message'], FILTER_SANITIZE_STRING);
      echo "<div align=\"center\" class=\"error\">$message</div>";
  }

  $username = '';
  if (isset($_REQUEST['username'])) {
      $username = $_REQUEST['username'];
  }
?>
        <form action="login.php" method="post">
            <table class="form" align="center" style="border-width: 0px" cellpadding="3">
                <tr>
                    <th>MTGO Username</th>
                    <td><input id="username" class="inputbox" type="text" name="username" value="<?=$username?>" tabindex="1"></td>
                </tr>
                <tr>
                    <th>Gatherling Password</th>
                    <td><input id="password" class="inputbox" type="password" name="password" tabindex="2"></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" class="buttons">
                    <?php
                      if (isset($_REQUEST['target'])) {
                          $target = filter_var($_REQUEST['target'], FILTER_SANITIZE_STRING);
                          echo "<input type=\"hidden\" name=\"target\" value=\"$target\">";
                      }
                    ?>
                        <input class="inputbutton" type="submit" name="mode" value="Log In">
                        <?php
                        if (!isset($_SESSION['DISCORD_ID'])) {
                            echo '<input class="inputbutton discordlogin fa-discord" type="submit" name="mode" value="Log In with discord" />';
                        } ?>
                        <br />
                        Please <a href="register.php">Click Here</a> if you need to register.<br />
                        <a href="forgot.php">Forgot your password?</a>
                    </td>
                </tr>
            </table>
        </form>
    </div> <!-- gatherling_main -->
</div> <!-- grid 10 pre 1 suff 1 -->

<?php print_footer(); ?>

<?php
function printLoginFailed()
                        {
                            echo "<span class=\"error\"><center>Incorrect username or password. Please try again.</center></span>\n";
                            echo '<br />';
                        }

function printIPAddressChanged()
{
    echo "<span class=\"error\"><center>Your IP Address has changed. Please login again.</center></span>\n";
    echo '<br />';
}

function testLogin()
{
    if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'Log In with discord') {
        redirect('auth.php');
    }
    $success = 0;

    if (isset($_POST['username']) && isset($_POST['password'])) {
        $auth = Player::checkPassword($_POST['username'], $_POST['password']);
        // The $admin check allows an admin to su into any user without a password.
        $admin = Player::isLoggedIn() && Player::getSessionPlayer()->isSuper();
        if ($auth || $admin) {
            header('Cache-control: private');
            $_SESSION['username'] = $_POST['username'];
            $target = 'player.php';
            if (isset($_REQUEST['target'])) {
                $target = $_REQUEST['target'];
            }
            if (strlen($_POST['password']) < 8 && !$admin) {
                $target = 'player.php?mode=changepass&tooshort=true';
            }
            header("location: $target");
            $success = 1;
        }

        return $success;
    }
}
?>
