<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class MonthDropMenu extends DropMenu
{
    public function __construct(int $month)
    {
        $names = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];
        $options = [];
        for ($m = 1; $m <= 12; $m++) {
            $options[] = [
                'isSelected' => $month === $m,
                'value'      => (string) $m,
                'text'       => $names[$m - 1],
            ];
        }

        parent::__construct('month', $options, '- Month -');
    }
}
