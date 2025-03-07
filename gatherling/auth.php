<?php

declare(strict_types=1);

use Gatherling\Auth\DiscordAuth;
use Gatherling\Models\Player;
use Gatherling\Views\Redirect;
use Gatherling\Views\Pages\AuthDebug;
use Gatherling\Views\Pages\PromptLinkAccount;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;
use League\OAuth2\Client\Token\AccessToken;

use function Gatherling\Helpers\request;
use function Gatherling\Helpers\server;
use function Gatherling\Helpers\session;

require_once __DIR__ . '/lib.php';

function main(): never
{
    $provider = DiscordAuth::getProvider();

    if (isset($_GET['debug']) && isset($_SESSION['DISCORD_TOKEN'])) {
        $token = DiscordAuth::loadCachedToken();
        (new AuthDebug($token))->send();
    }

    if (!isset($_GET['code']) && isset($_SESSION['DISCORD_TOKEN'])) {
        $token = DiscordAuth::loadCachedToken();
        $token = DiscordAuth::checkIfTokenExpired($token);

        // We might be here to upgrade our requested Discord permissions (Series Organizers setting up Discord Channels, for example)
        if (isset($_REQUEST['scope'])) {
            $needed = explode(' ', request()->string('scope', ''));
            $current = explode(' ', $token->getValues()['scope']);

            if (!empty(array_diff($needed, $current))) {
                $scope = array_unique(array_merge($current, $needed));
                DiscordAuth::sendToDiscord($scope);
            }
        }
        doLogin($token);
    } elseif (!isset($_GET['code'])) {
        if (isset($_REQUEST['scope'])) {
            $scope = $_REQUEST['scope'];
        } else {
            $scope = null;
        }
        DiscordAuth::sendToDiscord($scope);

    // Check given state against previously stored one to mitigate CSRF attack
    } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
        exit('Failed CSRF check. Please try again, or disable any browser extensions that might be causing issues.');
    } else {
        // Step 2. Get an access token using the provided authorization code
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);
        assert($token instanceof AccessToken);

        DiscordAuth::storeToken($token);
        doLogin($token);
    }
}

function doLogin(AccessToken $token): never
{
    $provider = DiscordAuth::getProvider();

    $user = $provider->getResourceOwner($token);
    assert($user instanceof DiscordResourceOwner);

    $_SESSION['DISCORD_ID'] = $user->getId();
    $_SESSION['DISCORD_NAME'] = "{$user->getUsername()}#{$user->getDiscriminator()}";

    $player = Player::getSessionPlayer();
    if ($player) {
        $player->discord_id = $_SESSION['DISCORD_ID'];
        $player->discord_handle = $_SESSION['DISCORD_NAME'];
        if (empty($player->emailAddress) && $user->getVerified()) {
            $player->emailAddress = $user->getEmail();
        }
        $player->save();
        (new Redirect('player.php'))->send();
    }

    $player = findPlayer($user);
    if (!$player) {
        (new PromptLinkAccount($user->getEmail() ?? ''))->send();
    }
    $_SESSION['username'] = $player->name;
    if ($player->discord_handle != $_SESSION['DISCORD_NAME']) {
        $player->discord_handle = $_SESSION['DISCORD_NAME'];
        $player->discord_id = session()->optionalString('DISCORD_ID');
        if (empty($player->emailAddress) && $user->getVerified()) {
            $player->emailAddress = $user->getEmail();
        }
        $player->save();
    }
    (new Redirect('player.php'))->send();
}

function findPlayer(DiscordResourceOwner $user): ?Player
{
    $id = $user->getId();
    $email = $user->getEmail();
    $player = null;
    if ($id) {
        $player = Player::findByDiscordID($id);
    }
    if (!$player && $email) {
        $player = Player::findByEmail($email);
    }
    return $player;
}

if (basename(__FILE__) == basename(server()->string('PHP_SELF'))) {
    main();
}
