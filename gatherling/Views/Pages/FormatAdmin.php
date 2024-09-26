<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Format;
use Gatherling\Views\Components\Component;
use Gatherling\Views\Components\FormatCPMenu;
use Gatherling\Views\Components\OrganizerSelect;

class FormatAdmin extends Page
{
    public OrganizerSelect $organizerSelect;
    public string $actionResultSafe = '';
    public FormatCPMenu $formatCPMenu;
    public string $viewSafe;

    /**
     * @param list<string> $playerSeries
     * @param Component|array<Component> $actionResultComponent
     */
    public function __construct(string $action, array $playerSeries, string $seriesName, Format $activeFormat, Component|array $actionResultComponent, ?Component $viewComponent = null)
    {
        parent::__construct();
        $this->title = 'Format Control Panel';
        $this->organizerSelect = count($playerSeries) > 1 ? new OrganizerSelect($action, $playerSeries, $seriesName) : null;

        $actionResultComponents = is_array($actionResultComponent) ? $actionResultComponent : [$actionResultComponent];
        foreach ($actionResultComponents as $actionResultComponent) {
            $this->actionResultSafe .= $actionResultComponent->render();
        }

        $this->formatCPMenu = new FormatCPMenu($seriesName, $activeFormat);

        $this->viewSafe = $viewComponent ? $viewComponent->render() : '';
    }
}
