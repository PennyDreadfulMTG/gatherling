<?php

declare(strict_types=1);

namespace Gatherling\Auth;

use Gatherling\Views\Redirect;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Wohali\OAuth2\Client\Provider\Discord;
use Wohali\OAuth2\Client\Provider\Exception\DiscordIdentityProviderException;

use function Gatherling\Helpers\config;

class DiscordAuth
{

    public static function getProvider(): Discord
    {
        static $provider;
        if (!isset($provider))
            $provider = new Discord([
                'clientId'     => config()->string('DISCORD_CLIENT_ID'),
                'clientSecret' => config()->string('DISCORD_CLIENT_SECRET'),
                'redirectUri'  => config()->string('base_url') . 'auth.php',
            ]);
        return $provider;
    }

    public static function loadCachedToken(): AccessToken
    {
        return new AccessToken([
            'access_token'  => $_SESSION['DISCORD_TOKEN'],
            'refresh_token' => $_SESSION['DISCORD_REFRESH_TOKEN'],
            'expires'       => $_SESSION['DISCORD_EXPIRES'],
            'scope'         => $_SESSION['DISCORD_SCOPES'],
        ]);
    }

    public static function storeToken(AccessTokenInterface $token): void
    {
        $_SESSION['DISCORD_TOKEN'] = $token->getToken();
        $_SESSION['DISCORD_REFRESH_TOKEN'] = $token->getRefreshToken();
        $_SESSION['DISCORD_EXPIRES'] = $token->getExpires();
        $_SESSION['DISCORD_SCOPES'] = $token->getValues()['scope'];
    }

    /** @return list<array{id: string, name: string, icon: string, owner: bool, permissions: int}> */
    public static function getUserGuilds(AccessToken $token): array
    {
        $provider = self::getProvider();

        $guildsRequest = $provider->getAuthenticatedRequest('GET', $provider->getResourceOwnerDetailsUrl($token) . '/guilds', $token);

        /** @var list<array{id: string, name: string, icon: string, owner: bool, permissions: int}> */
        return $provider->getParsedResponse($guildsRequest);
    }

    public static function checkIfTokenExpired(AccessToken $token): AccessToken
    {
        $provider = self::getProvider();
        try {
            if ($token->hasExpired()) {
                $newAccessToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $token->getRefreshToken(),
                ]);

                self::storeToken($newAccessToken);
                $token = $newAccessToken;
            }
        } catch (DiscordIdentityProviderException $e) {
            if (isset($_REQUEST['scope'])) {
                $scope = $_REQUEST['scope'];
            } else {
                $scope = null;
            }
            self::sendToDiscord($scope);
        }
        assert($token instanceof AccessToken);
        return $token;
    }

    public static function sendToDiscord(mixed $scope = null): never
    {
        // Step 1. Get authorization code
        $provider = self::getProvider();
        if (is_null($scope)) {
            $scope = 'identify email guilds';
        }
        $options = ['scope' => $scope];
        $authUrl = $provider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = $provider->getState();
        (new Redirect($authUrl))->send();
    }

}
