<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

class BannedPlayer extends Page
{
    public function __construct()
    {
        parent::__construct();
        $this->title = 'You have been banned';
    }
}
