<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Views\TemplateHelper;

abstract class Component
{
    protected string $template;

    public function __construct(string $template)
    {
        $this->template = $template;
    }

    public function render(): string
    {
        return TemplateHelper::render($this->template, $this);
    }
}
