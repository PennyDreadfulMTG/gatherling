<?php

declare(strict_types=1);

namespace Gatherling\Views;

// HTTP response that's just pure HTML like Hotwire or HTMX
class WireResponse extends Response
{
    public function __construct(private string $html)
    {
    }

    public function body(): string
    {
        return $this->html;
    }
}
