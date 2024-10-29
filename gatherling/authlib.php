<?php

declare(strict_types=1);

use Gatherling\Views\Components\AuthDebugInfo;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Wohali\OAuth2\Client\Provider\Discord;

use function Gatherling\Helpers\config;

require_once __DIR__ . '/lib.php';

$provider = new Discord([
    'clientId'     => config()->string('DISCORD_CLIENT_ID'),
    'clientSecret' => config()->string('DISCORD_CLIENT_SECRET'),
    'redirectUri'  => config()->string('base_url') . 'auth.php',
]);

function load_cached_token(): AccessToken
{
    return new AccessToken([
        'access_token'  => $_SESSION['DISCORD_TOKEN'],
        'refresh_token' => $_SESSION['DISCORD_REFRESH_TOKEN'],
        'expires'       => $_SESSION['DISCORD_EXPIRES'],
        'scope'         => $_SESSION['DISCORD_SCOPES'],
    ]);
}

function store_token(AccessTokenInterface $token): void
{
    $_SESSION['DISCORD_TOKEN'] = $token->getToken();
    $_SESSION['DISCORD_REFRESH_TOKEN'] = $token->getRefreshToken();
    $_SESSION['DISCORD_EXPIRES'] = $token->getExpires();
    $_SESSION['DISCORD_SCOPES'] = $token->getValues()['scope'];
}

/** @return list<array{id: string, name: string, icon: string, owner: bool, permissions: int}> */
function get_user_guilds(AccessToken $token): array
{
    global $provider;

    $guildsRequest = $provider->getAuthenticatedRequest('GET', $provider->getResourceOwnerDetailsUrl($token) . '/guilds', $token);

    return $provider->getParsedResponse($guildsRequest);
}

function debug_info(\League\OAuth2\Client\Token\AccessToken $token): string
{
    return (new AuthDebugInfo($token))->render();
}
