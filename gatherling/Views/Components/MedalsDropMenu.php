<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class MedalsDropMenu extends DropMenu
{
    /** @param list<string> $options */
    public function __construct(string $name, array $options, ?string $selected = null)
    {
        $opts = [];
        foreach ($options as $option) {
            $opts[] = [
                'value' => $option,
                'text' => $option,
                'isSelected' => !is_null($selected) && $selected == $option,
            ];
        }
        parent::__construct($name, $opts, '- Medals -', 'ds_select');
    }
}
