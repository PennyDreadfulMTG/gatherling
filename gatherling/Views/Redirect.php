<?php

declare(strict_types=1);

namespace Gatherling\Views;

class Redirect extends Response
{
    public function __construct(string $url)
    {
        $this->setHeader('Location', $url);
    }

    public function send(): never
    {
        parent::send();
        exit;
    }
}
