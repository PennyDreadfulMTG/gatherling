<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\Component;

class PlayerControlPanel extends Page
{
    public string $result;
    public string $viewSafe;

    public function __construct(string $result, Component $viewComponent)
    {
        parent::__construct();
        $this->title = 'Player Control Panel';
        $this->result = $result;
        $this->viewSafe = $viewComponent->render();
    }
}
