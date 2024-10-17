<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Series;
use Gatherling\Views\Components\Component;
use Gatherling\Views\Components\SeriesControlPanelMenu;

class SeriesControlPanel extends Page
{
    public string $orientationSafe;
    public ?SeriesControlPanelMenu $seriesControlPanelMenu;
    public string $viewSafe;

    public function __construct(?Series $activeSeries, ?Component $orientationComponent, public string $errorMsg, Component $viewComponent)
    {
        parent::__construct();
        $this->title = 'Series Control Panel';
        $this->orientationSafe = $orientationComponent ? $orientationComponent->render() : '';
        $this->seriesControlPanelMenu = $activeSeries ? new SeriesControlPanelMenu($activeSeries) : null;
        $this->viewSafe = $viewComponent->render();
    }
}
