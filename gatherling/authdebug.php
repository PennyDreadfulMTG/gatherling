<?php

declare(strict_types=1);

namespace Gatherling;

use Gatherling\Views\Pages\AuthDebug;
use Gatherling\Views\Redirect;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Wohali\OAuth2\Client\Provider\Discord;

use function Gatherling\Helpers\get;
use function Gatherling\Helpers\server;
use function Gatherling\Helpers\session;

require_once __DIR__ . '/lib.php';
require __DIR__ . '/authlib.php';

function main(): void
{
    global $provider;

    $code = get()->optionalString('code');
    $token = session()->optionalString('DISCORD_TOKEN');

    if ($code === null && $token !== null) {
        $token = load_cached_token();
        if ($token->hasExpired()) {
            $token = refreshAccessToken($provider, $token);
        }
    } elseif ($code === null) {
        // Step 1. Get authorization code
        global $provider;
        $options = ['scope' => ['identify', 'email']];
        $authUrl = $provider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = $provider->getState();
        (new Redirect($authUrl))->send();
    // Check given state against previously stored one to mitigate CSRF attack
    } elseif (get()->optionalString('state') !== session()->string('oauth2state')) {
        unset($_SESSION['oauth2state']);
        exit('Invalid state');
    } else {
        // Step 2. Get an access token using the provided authorization code
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
        store_token($token);
    }

    $page = new AuthDebug($token);
    $page->send();
}

function refreshAccessToken(Discord $provider, AccessTokenInterface $token): AccessTokenInterface
{
    $newAccessToken = $provider->getAccessToken('refresh_token', [
        'refresh_token' => $token->getRefreshToken(),
    ]);
    store_token($newAccessToken);
    return $newAccessToken;
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
