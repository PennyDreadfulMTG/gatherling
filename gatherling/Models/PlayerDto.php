<?php

declare(strict_types=1);

namespace Gatherling\Models;

class PlayerDto extends Dto
{
    public string $name;
    public ?string $password;
    public int $rememberMe;
    public ?string $ipAddress;
    public int $host;
    public int $super;
    public ?int $verified;
    public ?string $emailAddress;
    public int $emailPrivacy;
    public float $timezone;
    public ?string $theme;
    public ?string $discord_id;
    public ?string $discord_handle;
    public ?string $api_key;
    public ?string $mtga_username;
    public ?string $mtgo_username;
}
