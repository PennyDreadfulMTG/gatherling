<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

class InsertCardSet extends Page
{
    /** @param list<string> $messages */
    public function __construct(public array $messages)
    {
        parent::__construct();
        $this->title = 'Insert Card Set';
    }
}
