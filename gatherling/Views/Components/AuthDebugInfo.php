<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Auth\DiscordAuth;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

class AuthDebugInfo extends Component
{
    public string $token;
    public ?string $refreshToken;
    public ?int $expires;
    public bool $hasExpired;
    /** @var array<array{key: string, value: string}> */
    public array $values = [];
    public ?string $username;
    public ?string $discriminator;
    public ?string $details;
    /** @var list<string> */
    public array $guilds;
    public ?string $errorMessage;

    public function __construct(AccessToken $token)
    {
        $provider = DiscordAuth::getProvider();

        $this->token = $token->getToken();
        $this->refreshToken = $token->getRefreshToken();
        $this->expires = $token->getExpires();
        $this->hasExpired = $token->hasExpired();
        foreach ($token->getValues() as $key => $value) {
            $this->values[] = ['key' => (string) $key, 'value' => (string) $value];
        }

        try {
            $user = $provider->getResourceOwner($token);
            assert($user instanceof DiscordResourceOwner);
            $this->username = $user->getUsername();
            $this->discriminator = $user->getDiscriminator();
            $this->details = var_export($user->toArray(), true);
            $guilds = DiscordAuth::getUserGuilds($token);
            $this->guilds = array_map(fn ($g) => var_export($g, true), $guilds);
        } catch (Exception $e) {
            // Failed to get user details
            $this->errorMessage = $e->getMessage();
        }
    }
}
