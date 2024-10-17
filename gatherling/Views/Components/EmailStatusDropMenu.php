<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class EmailStatusDropMenu extends DropMenu
{
    public function __construct(int $currentStatus = 1)
    {
        $options = [
            ['value' => '0', 'text' => 'Private', 'isSelected' => $currentStatus == 0],
            ['value' => '1', 'text' => 'Public', 'isSelected' => $currentStatus == 1],
        ];
        parent::__construct('emailstatus', $options);
    }
}
