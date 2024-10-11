<?php

namespace Gatherling\Views\Components;

class ProfileEditForm extends Component
{
    public TimeZoneDropMenu $timezoneDropMenu;

    public function __construct(float $timezone, public string $email, public int $emailPrivacy)
    {
        parent::__construct('partials/profileEditForm');
        $this->timezoneDropMenu = new TimeZoneDropMenu($timezone);
    }
}
