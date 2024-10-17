<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Views\TemplateHelper;

abstract class Component
{
    public function template(): string
    {
        $className = get_called_class();
        $baseName = ($pos = strrpos($className, '\\')) !== false ? substr($className, $pos + 1) : $className;
        return 'partials/' . lcfirst($baseName);
    }

    public function render(): string
    {
        return TemplateHelper::render($this->template(), $this);
    }
}
