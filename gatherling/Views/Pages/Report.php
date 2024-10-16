<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\Component;

class Report extends Page
{
    public string $viewSafe;

    public function __construct(public string $result, public Component $viewComponent)
    {
        parent::__construct();
        $this->title = 'Player Control Panel';
        $this->viewSafe = $viewComponent->render();
    }
}
