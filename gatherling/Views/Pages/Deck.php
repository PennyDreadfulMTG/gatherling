<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Views\Components\Component;

class Deck extends Page
{
    public string $viewSafe;

    public function __construct(public string $title, Component $viewComponent)
    {
        parent::__construct();
        $this->title = 'Deck Database';
        $this->viewSafe = $viewComponent->render();
    }
}
