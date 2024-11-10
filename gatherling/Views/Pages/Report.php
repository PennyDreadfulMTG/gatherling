<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\Component;

class Report extends Page
{
    public string $viewSafe;

    public function __construct(public string $result, public Component $viewComponent)
    {
        parent::__construct('Player Control Panel', true);
        $this->viewSafe = $viewComponent->render();
    }
}
