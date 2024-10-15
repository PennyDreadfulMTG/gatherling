<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\Component;

class AdminControlPanel extends Page
{
    public string $viewSafe;

    public function __construct(public string $result, Component $viewComponent)
    {
        parent::__construct();
        $this->title = 'Admin Control Panel';
        $this->viewSafe = $viewComponent->render();
    }
}
