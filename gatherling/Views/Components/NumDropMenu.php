<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Views\Components\DropMenu;

class NumDropMenu extends DropMenu
{
    public function __construct(string $field, string $title, int $max, int|string|null $def, int $min = 0, ?string $special = null)
    {
        if ($def === '') {
            $def = -1;
        }

        $options = [];
        if ($special) {
            $options[] = [
                'text'       => $special,
                'value'      => '128',
                'isSelected' => $def == 128,
            ];
        }
        for ($n = $min; $n <= $max; $n++) {
            $options[] = [
                'text'       => (string) $n,
                'value'      => (string) $n,
                'isSelected' => $n == $def,
            ];
        }

        parent::__construct($field, $options, $title);
    }
}
