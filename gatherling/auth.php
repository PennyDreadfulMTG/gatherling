<?php
require __DIR__.'/lib.php';

session_start();

global $CONFIG;

$provider = new \Wohali\OAuth2\Client\Provider\Discord([
    'clientId'     => $CONFIG['DISCORD_CLIENT_ID'],
    'clientSecret' => $CONFIG['DISCORD_CLIENT_SECRET'],
    'redirectUri'  => $CONFIG['base_url'].'auth.php',
]);

if (isset($_GET['debug']) && isset($_SESSION['DISCORD_TOKEN'])) {
    $token = new \League\OAuth2\Client\Token\AccessToken([
        'access_token'  => $_SESSION['DISCORD_TOKEN'],
        'refresh_token' => $_SESSION['DISCORD_REFRESH_TOKEN'],
        'expires'       => $_SESSION['DISCORD_EXPIRES'],
    ]);
    debug_info($token);
    return;
}

if (!isset($_GET['code']) && isset($_SESSION['DISCORD_TOKEN'])) {
    $token = new \League\OAuth2\Client\Token\AccessToken([
        'access_token'  => $_SESSION['DISCORD_TOKEN'],
        'refresh_token' => $_SESSION['DISCORD_REFRESH_TOKEN'],
        'expires'       => $_SESSION['DISCORD_EXPIRES'],
    ]);

    if ($token->hasExpired()) {
        $newAccessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $existingAccessToken->getRefreshToken(),
        ]);

        store_token($newAccessToken);
        $token = $newAccessToken;
    }

    do_login($token);
} elseif (!isset($_GET['code'])) {
    send_to_discord();

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
} else {

    // Step 2. Get an access token using the provided authorization code
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
    ]);

    store_token($token);
    do_login($token);
}

function send_to_discord()
{
    // Step 1. Get authorization code
    global $provider;
    $options = ['scope' => ['identify', 'email']];
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
}

function store_token($token)
{
    $_SESSION['DISCORD_TOKEN'] = $token->getToken();
    $_SESSION['DISCORD_REFRESH_TOKEN'] = $token->getRefreshToken();
    $_SESSION['DISCORD_EXPIRES'] = $token->getExpires();
}

function debug_info($token) {
    # Show some token details
    echo '<h2>Token details:</h2>';
    echo 'Token: ' . $token->getToken() . "<br/>";
    echo 'Refresh token: ' . $token->getRefreshToken() . "<br/>";
    echo 'Expires: ' . $token->getExpires() . " - ";
    echo ($token->hasExpired() ? 'expired' : 'not expired') . "<br/>";

    // Step 3. (Optional) Look up the user's profile with the provided token
    try {
        global $provider;
        $user = $provider->getResourceOwner($token);

        echo '<h2>Resource owner details:</h2>';
        printf('Hello %s#%s!<br/><br/>', $user->getUsername(), $user->getDiscriminator());
        var_export($user->toArray());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');

    }
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
            $player->save();
            redirect('player.php');
        }

        $player = Player::findByDiscordID($user->getId());
        if ($player) {
            $_SESSION['username'] = $player->name;
            if ($player->discord_handle != $_SESSION['DISCORD_NAME']) {
                $player->discord_handle = $_SESSION['DISCORD_NAME'];
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
                    <th>MTGO Username</th>
                    <td><input id="username" class="inputbox" type="text" name="username" value="" tabindex="1"></td>
                </tr>
                <tr>
                    <td colspan="2">Please ensure you enter this correctly, as it cannot be changed later.</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2" class="buttons">
                        <input type="hidden" name="pw1" value="">
                        <input type="hidden" name="pw2" value="">
                        <input type="hidden" name="email" value="<?=$user->getEmail()?>">
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
