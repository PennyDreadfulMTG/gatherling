<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Data\Db;

class SeriesDropMenuDS extends DropMenu
{
    public function __construct(?string $seriesName = null, int $useAll = 0, string $formName = 'series')
    {
        $sql = 'SELECT name FROM series ORDER BY name';
        $result = Db::strings($sql);
        $title = ($useAll == 0) ? '- Series -' : 'All';

        $options = [];
        foreach ($result as $name) {
            $options[] = [
                'text'       => $name,
                'value'      => $name,
                'isSelected' => $seriesName && strcmp($name, $seriesName) == 0,
            ];
        }
        parent::__construct($formName, $options, $title, 'ds_select');
    }
}
