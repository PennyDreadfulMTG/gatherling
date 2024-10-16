<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class Select extends DropMenu
{
    /** @param array<string, string> $options */
    public function __construct(public string $name, array $options = [], mixed $selected = null, ?string $id = null)
    {
        $opts = [];
        foreach ($options as $option => $text) {
            $opts[] = [
                'isSelected' => !is_null($selected) && $selected == $option,
                'value'      => $option,
                'text'       => $text,
            ];
        }
        parent::__construct($name, $opts, null, $id);
    }
}
