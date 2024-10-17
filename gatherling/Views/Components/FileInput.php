<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class FileInput extends Component
{
    public function __construct(public string $label, public string $name)
    {
    }
}
