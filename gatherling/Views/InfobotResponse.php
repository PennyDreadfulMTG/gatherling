<?php

declare(strict_types=1);

namespace Gatherling\Views;

abstract class InfobotResponse extends Response
{
    public function __construct(public string $message)
    {
    }

    abstract public function template(): string;

    public function body(): string
    {
        return TemplateHelper::render($this->template(), $this);
    }
}
