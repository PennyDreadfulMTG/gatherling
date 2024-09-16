<?php

declare(strict_types=1);

namespace Gatherling\Views;

class TextFileDownload extends TemplateResponse
{
    public function __construct(private string $filename)
    {
        $this->setHeader('Content-type', 'text/plain');
        $this->setHeader('Content-Disposition', "attachment; filename=$filename");
    }
}
