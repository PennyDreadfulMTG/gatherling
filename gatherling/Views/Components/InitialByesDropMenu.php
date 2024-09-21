<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class InitialByesDropMenu extends DropMenu
{
    public function __construct(string $name = 'initial_byes', string $playerName = '', int $currentByes = 0)
    {
        $options = [];
        for ($i = 0; $i < 3; $i++) {
            $options[] = [
                'value'      => "$playerName $i",
                'text'       => $i == 0 ? 'None' : "$i",
                'isSelected' => $currentByes == $i,
            ];
        }
        $this->name = $name;
        $this->options = $options;
    }
}
