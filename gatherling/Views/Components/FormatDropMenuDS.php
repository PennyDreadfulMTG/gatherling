<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use function Gatherling\Helpers\db;

class FormatDropMenuDS extends DropMenu
{
    public function __construct(public string $formatName, int $useAll = 0, string $formName = 'format')
    {
        $sql = 'SELECT name FROM formats ORDER BY priority desc, name';
        $formatNames = db()->strings($sql);
        $title = ($useAll == 0) ? '- Format -' : 'All';
        $options = [];
        foreach ($formatNames as $name) {
            $options[] = [
                'value' => $name,
                'text' => $name,
                'isSelected' => strcmp($name, $formatName) == 0,
            ];
        }
        parent::__construct($formName, $options, $title, 'ds_select');
    }
}
