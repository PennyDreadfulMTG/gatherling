<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Series;

class LogoForm
{
    public string $seriesName;
    public string $logoSrc;
    public function __construct(Series $series)
    {
        $this->seriesName = $series->name;
        $this->logoSrc = Series::logoSrc($series->name);
    }
}
