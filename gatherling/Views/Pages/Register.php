<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\EmailStatusDropDown;
use Gatherling\Views\Components\TimeZoneDropMenu;

class Register extends Page
{
    public EmailStatusDropDown $emailStatusDropDown;
    public TimeZoneDropMenu $timeZoneDropMenu;

    public function __construct(public bool $showRegForm, public string $message)
    {
        parent::__construct();
        $this->title = 'Register';
        $this->emailStatusDropDown = new EmailStatusDropDown();
        $this->timeZoneDropMenu = new TimeZoneDropMenu();
    }
}
