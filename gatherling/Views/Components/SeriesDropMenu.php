<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Series;

class SeriesDropMenu extends DropMenu
{
    public function __construct(?string $seriesName, ?string $default = '- Series -', array $limitTo = [])
    {
        $allSeries = empty($limitTo) ? Series::allNames() : $limitTo;
        $options = [];
        foreach ($allSeries as $name) {
            $options[] = [
                'text'       => $name,
                'value'      => $name,
                'isSelected' => $seriesName && strcmp($seriesName, $name) == 0,
            ];
        }
        parent::__construct('series', $options, $default);
    }
}
