<?php

declare(strict_types=1);

require_once __DIR__.'/lib.php';

global $CONFIG;

$provider = new \Wohali\OAuth2\Client\Provider\Discord([
    'clientId'     => $CONFIG['DISCORD_CLIENT_ID'],
    'clientSecret' => $CONFIG['DISCORD_CLIENT_SECRET'],
    'redirectUri'  => $CONFIG['base_url'].'auth.php',
]);

function load_cached_token(): \League\OAuth2\Client\Token\AccessToken
{
    return new \League\OAuth2\Client\Token\AccessToken([
        'access_token'  => $_SESSION['DISCORD_TOKEN'],
        'refresh_token' => $_SESSION['DISCORD_REFRESH_TOKEN'],
        'expires'       => $_SESSION['DISCORD_EXPIRES'],
        'scope'         => $_SESSION['DISCORD_SCOPES'],
    ]);
}

/** @return list<array{id: string, name: string, icon: string, owner: bool, permissions: int}> */
function get_user_guilds(\League\OAuth2\Client\Token\AccessToken $token): array
{
    global $provider;

    $guildsRequest = $provider->getAuthenticatedRequest('GET', $provider->getResourceOwnerDetailsUrl($token).'/guilds', $token);

    return $provider->getParsedResponse($guildsRequest);
}

function debug_info(\League\OAuth2\Client\Token\AccessToken $token): void
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
        exit($e->getMessage());
    }
    echo '<h2>Guilds:</h2>';
    $guilds = get_user_guilds($token);

    // var_dump($guilds);
    foreach ($guilds as $g) {
        var_dump($g);
        echo '<p/>';
    }
}
