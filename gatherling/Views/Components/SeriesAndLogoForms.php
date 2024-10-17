<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Series;

class SeriesAndLogoForms extends Component
{
    public SeriesForm $seriesForm;
    public LogoForm $logoForm;

    public function __construct(Series $series)
    {
        parent::__construct('partials/seriesAndLogoForms');
        $this->seriesForm = new SeriesForm($series);
        $this->logoForm = new LogoForm($series);
    }
}
