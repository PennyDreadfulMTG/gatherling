<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class TextComponent extends Component
{
    public function __construct(public string $text)
    {
        parent::__construct('partials/textComponent');
    }
}
