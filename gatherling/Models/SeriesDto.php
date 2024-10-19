<?php

declare(strict_types=1);

namespace Gatherling\Models;

class SeriesDto extends Dto
{
    public ?int $active;
    public ?string $start_day;
    public ?string $start_time;
    public ?int $prereg_default;
    public ?string $mtgo_room;
    public ?string $discord_guild_id;
    public ?string $discord_channel_id;
    public ?string $discord_channel_name;
    public ?string $discord_guild_name;
    public ?string $discord_guild_invite;
    public ?int $discord_require_membership;
}
