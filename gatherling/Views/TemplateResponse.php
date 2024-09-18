<?php

declare(strict_types=1);

namespace Gatherling\Views;

class TemplateResponse extends Response
{
    public function template(): string
    {
        $className = get_called_class();
        $baseName = ($pos = strrpos($className, '\\')) !== false ? substr($className, $pos + 1) : $className;
        return lcfirst($baseName);
    }

    protected function render(): string
    {
        $contentType = $this->getHeader('Content-type') ?? 'text/html';
        $isHtml = $contentType === 'text/html';
        return TemplateHelper::render($this->template(), $this, $isHtml);
    }

    public function body(): string
    {
        return $this->render();
    }
}
