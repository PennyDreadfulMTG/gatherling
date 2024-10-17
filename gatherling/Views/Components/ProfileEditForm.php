<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class ProfileEditForm extends Component
{
    public TimeZoneDropMenu $timezoneDropMenu;

    public function __construct(float $timezone, public string $email, public int $emailPrivacy)
    {
        $this->timezoneDropMenu = new TimeZoneDropMenu($timezone);
    }
}
