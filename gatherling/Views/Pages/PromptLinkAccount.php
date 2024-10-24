<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

class PromptLinkAccount extends Page
{
    public function __construct(public string $email)
    {
        parent::__construct();
        $this->title = 'Login';
    }
}
