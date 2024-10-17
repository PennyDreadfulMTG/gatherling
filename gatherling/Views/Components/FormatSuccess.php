<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class FormatSuccess extends Component
{
    public function __construct(public string $message, public string $formatName = '')
    {
    }
}
