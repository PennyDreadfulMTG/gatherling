<?php

namespace Gatherling\Views\Components;

use Gatherling\Views\Components\DropMenu;

class NumDropMenu extends DropMenu
{
    public function __construct(string $field, string $title, int $max, int|string|null $def, int $min = 0, ?string $special = null)
    {
        if ($def && strcmp($def, '') == 0) {
            $def = -1;
        }

        $options = [];
        if ($special) {
            $options[] = [
                'text'       => $special,
                'value'      => 128,
                'isSelected' => $def == 128,
            ];
        }
        for ($n = $min; $n <= $max; $n++) {
            $options[] = [
                'text'       => $n,
                'value'      => $n,
                'isSelected' => $n == $def,
            ];
        }

        parent::__construct($field, $options, $title);
    }
}