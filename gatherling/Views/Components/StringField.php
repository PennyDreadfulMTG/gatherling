<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class StringField extends Component
{
    public function __construct(public string $field, public mixed $def, public int $len)
    {
        parent::__construct('partials/stringField');
    }
}
