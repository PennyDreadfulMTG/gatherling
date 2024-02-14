<?php

use Gatherling\Player;

include 'lib.php';
require_once 'lib_form_helper.php';

session_start();
print_header('Login');
printPageHeader();

if (isset($_POST['view']) && $_POST['view'] === 'send_login_link') {
    if (isset($_POST['identifier']) && strpos($_POST['identifier'], '@') !== false) {
        $player = Player::findByEmail($_POST['identifier']);
    } else {
        $player = Player::findByName($_POST['identifier']);
    }
    if ($player) {
        $email = $player->emailPrivacy ? "your registered email" : $player->emailAddress;
        if (sendLoginLink($player)) {
            echo '<p>A link has been sent to ' . htmlentities($email) . '</p>';
        } else {
            echo '<p class="error">Unable to send a link to ' . htmlentities($email) . '</p>';
            printForgotForm();
        }
    } else {
        echo '<p class="error">Unable to find a player with that email or username</p>';
        printForgotForm();
    }
} else {
    printForgotForm();
}

printPageFooter();
print_footer();

function printPageHeader() {
    ?>
    <div class="grid_10 suffix_1 prefix_1">
        <div id="gatherling_main" class="box">
            <div class="uppertitle"> Login to Gatherling </div>
            <center>
                <h3>Resetting your Gatherling password</h3>
    <?php
}

function printPageFooter() {
    ?>
        </center>
        </div> <!-- gatherling_main -->
    </div> <!-- grid 10 pre 1 suff 1 -->
<?php
}

function printForgotForm() {
    ?>
                    <p>Enter your email or username and we'll send you a link to get back into your account.</p>
                    <form action="forgot.php" method="post">
                        <input type="hidden" name="view" value="send_login_link" />
                        <table class="form">
                            <?php
                                print_text_input('Email or Username', 'identifier');
                                print_submit('Send Login Link');
                            ?>
                        </table>
                    </form>
                    <p>If you aren't able to reset your password this way please message a Gatherling Administrator
                        on the <a href="https://discord.gg/2VJ8Fa6">Discord</a></p>
<?php
}

function sendLoginLink($player) {
    return mail($player->emailAddress, 'Gatherling Login Link', "Click the following link to log in to Gatherling:\n\n");
}
