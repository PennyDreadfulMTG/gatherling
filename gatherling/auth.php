<?php

use Gatherling\Player;
use Wohali\OAuth2\Client\Provider\Exception\DiscordIdentityProviderException;

require_once __DIR__.'/lib.php';
require __DIR__.'/authlib.php';

session_start();

global $CONFIG;
global $provider;

if (isset($_GET['debug']) && isset($_SESSION['DISCORD_TOKEN'])) {
    $token = load_cached_token();
    debug_info($token);

    return;
}

if (!isset($_GET['code']) && isset($_SESSION['DISCORD_TOKEN'])) {
    $token = load_cached_token();

    try {
        if ($token->hasExpired()) {
            $newAccessToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $token->getRefreshToken(),
            ]);

            store_token($newAccessToken);
            $token = $newAccessToken;
        }
    } catch (DiscordIdentityProviderException $e) {
        if (isset($_REQUEST['scope'])) {
            $scope = $_REQUEST['scope'];
        } else {
            $scope = null;
        }
        send_to_discord($scope);
    }
    // We might be here to upgrade our requested Discord permissions (Series Organizers setting up Discord Channels, for example)
    if (isset($_REQUEST['scope'])) {
        $needed = explode(' ', $_REQUEST['scope']);
        $current = explode(' ', $token->getValues()['scope']);

        if (!empty(array_diff($needed, $current))) {
            $scope = array_unique(array_merge($current, $needed));
            send_to_discord($scope);

            return;
        }
    }
    do_login($token);
} elseif (!isset($_GET['code'])) {
    if (isset($_REQUEST['scope'])) {
        $scope = $_REQUEST['scope'];
    } else {
        $scope = null;
    }
    send_to_discord($scope);

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Failed CSRF check. Please try again, or disable any browser extensions that might be causing issues.');
} else {

    // Step 2. Get an access token using the provided authorization code
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
    ]);

    store_token($token);
    do_login($token);
}

function send_to_discord($scope = null)
{
    // Step 1. Get authorization code
    global $provider;
    if (is_null($scope)) {
        $scope = 'identify email guilds';
    }
    $options = ['scope' => $scope];
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
}

/**
 * @param \League\OAuth2\Client\Token\AccessTokenInterface $token
 */
function store_token($token)
{
    $_SESSION['DISCORD_TOKEN'] = $token->getToken();
    $_SESSION['DISCORD_REFRESH_TOKEN'] = $token->getRefreshToken();
    $_SESSION['DISCORD_EXPIRES'] = $token->getExpires();
    $_SESSION['DISCORD_SCOPES'] = $token->getValues()['scope'];
}

function do_login($token)
{
    try {
        global $provider;
        $user = $provider->getResourceOwner($token);
        $_SESSION['DISCORD_ID'] = $user->getId();
        $_SESSION['DISCORD_NAME'] = "{$user->getUsername()}#{$user->getDiscriminator()}";

        if (Player::isLoggedIn()) {
            $player = Player::getSessionPlayer();
            $player->discord_id = $_SESSION['DISCORD_ID'];
            $player->discord_handle = $_SESSION['DISCORD_NAME'];
            if (empty($player->emailAddress) && $user->getVerified())
                $player->emailAddress = $user->getEmail();
            $player->save();
            redirect('player.php');
        }

        $player = Player::findByDiscordID($user->getId());
        if (!$player && $user->getVerified()) {
            $player = Player::findByEmail($user->getEmail());
        }
        if ($player) {
            $_SESSION['username'] = $player->name;
            if ($player->discord_handle != $_SESSION['DISCORD_NAME']) {
                $player->discord_handle = $_SESSION['DISCORD_NAME'];
                $player->discord_id = $_SESSION['DISCORD_ID'];
                if (empty($player->emailAddress) && $user->getVerified())
                $player->emailAddress = $user->getEmail();

                $player->save();
            }
            redirect('player.php');
        }

        prompt_link_account($user);
    } catch (Exception $e) {
        // Failed to get user details
        exit('Discord Login Failure');
    }
}

function prompt_link_account($user)
{
    print_header('Login'); ?>
    <div class="grid_10 suffix_1 prefix_1">
        <div id="gatherling_main" class="box">
            <div class="uppertitle"> Link Discord Account </div>

            <form action="register.php" method="post">
                <table class="form" align="center" style="border-width: 0px" cellpadding="3">
                    <tr>
                        <th>Username</th>
                        <td><input id="username" class="inputbox" type="text" name="username" value="" tabindex="1"></td>
                    </tr>
                    <tr>
                        <td colspan="2">Please use your MTGO username if you have one.</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="buttons">
                            <input type="hidden" name="pw1" value="">
                            <input type="hidden" name="pw2" value="">
                            <input type="hidden" name="email" value="<?= $user->getEmail() ?>">
                            <input type="hidden" name="emailstatus" value="0">
                            <input type="hidden" name="timezone" value="0">

                            <input class="inputbutton" type="submit" name="mode" value="Link"><br />
                        </td>
                    </tr>
                </table>
            </form>
        </div> <!-- gatherling_main -->
    </div> <!-- grid 10 pre 1 suff 1 -->

<?php
    print_footer();
}
