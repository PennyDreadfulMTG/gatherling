<?php

session_start();

require_once __DIR__.'/lib.php';
require __DIR__.'/authlib.php';


global $CONFIG;
global $provider;

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
