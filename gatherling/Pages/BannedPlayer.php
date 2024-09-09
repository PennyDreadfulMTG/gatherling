<?php

namespace Gatherling\Pages;

class BannedPlayer extends Page
{
    public function __construct()
    {
        parent::__construct();
        $this->title = 'You have been banned';
    }
}
