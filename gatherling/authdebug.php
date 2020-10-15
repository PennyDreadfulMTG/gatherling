<?php

require __DIR__.'/lib.php';

session_start();

global $CONFIG;

$provider = new \Wohali\OAuth2\Client\Provider\Discord([
    'clientId'     => $CONFIG['DISCORD_CLIENT_ID'],
    'clientSecret' => $CONFIG['DISCORD_CLIENT_SECRET'],
    'redirectUri'  => $CONFIG['base_url'].'authdebug.php',
]);

function load_cached_token() {
    return new \League\OAuth2\Client\Token\AccessToken([
        'access_token'  => $_SESSION['DISCORD_TOKEN'],
        'refresh_token' => $_SESSION['DISCORD_REFRESH_TOKEN'],
        'expires'       => $_SESSION['DISCORD_EXPIRES'],
        'scope'         => $_SESSION['DISCORD_SCOPES'],
    ]);
}

if (!isset($_GET['code']) && isset($_SESSION['DISCORD_TOKEN'])) {
    $token = load_cached_token();

    if ($token->hasExpired()) {
        $newAccessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->getRefreshToken(),
        ]);

        store_token($newAccessToken);
        $token = $newAccessToken;
    }

    debug_info($token);
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
    debug_info($token);
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
    $_SESSION['DISCORD_SCOPES'] = $token->getValues()['scope'];
}

function debug_info($token)
{
    // Show some token details
    echo '<h2>Token details:</h2>';
    echo 'Token: '.$token->getToken().'<br/>';
    echo 'Refresh token: '.$token->getRefreshToken().'<br/>';
    echo 'Expires: '.$token->getExpires().' - ';
    echo($token->hasExpired() ? 'expired' : 'not expired').'<br/>';
    echo 'Values: <br/>';
    foreach ($token->getValues() as $key => $value) {
        echo "$key=$value<br/>";
    }

    // Step 3. (Optional) Look up the user's profile with the provided token
    try {
        global $provider;
        $user = $provider->getResourceOwner($token);

        echo '<h2>Resource owner details:</h2>';
        printf('Hello %s#%s!<br/><br/>', $user->getUsername(), $user->getDiscriminator());
        var_export($user->toArray());
    } catch (Exception $e) {

        // Failed to get user details
        exit($e);
    }
}
