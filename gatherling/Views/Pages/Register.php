<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\EmailStatusDropMenu;
use Gatherling\Views\Components\TimeZoneDropMenu;

class Register extends Page
{
    public EmailStatusDropMenu $emailStatusDropMenu;
    public TimeZoneDropMenu $timeZoneDropMenu;

    public function __construct(public bool $showRegForm, public string $message)
    {
        parent::__construct();
        $this->title = 'Register';
        $this->emailStatusDropMenu = new EmailStatusDropMenu();
        $this->timeZoneDropMenu = new TimeZoneDropMenu();
    }
}
