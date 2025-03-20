<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Format;
use Gatherling\Views\Components\Component;
use Gatherling\Views\Components\FormatControlPanelMenu;
use Gatherling\Views\Components\OrganizerSelect;

class FormatAdmin extends Page
{
    public ?OrganizerSelect $organizerSelect;
    public string $actionResultSafe = '';
    public FormatControlPanelMenu $formatControlPanelMenu;
    public string $viewSafe;

    /**
     * @param list<string> $playerSeries
     * @param Component|array<Component> $actionResultComponent
     */
    public function __construct(string $action, array $playerSeries, string $seriesName, Format $format, Component|array $actionResultComponent, ?Component $viewComponent = null)
    {
        parent::__construct('Format Control Panel', true);
        $this->organizerSelect = count($playerSeries) > 1 ? new OrganizerSelect($action, $playerSeries, $seriesName) : null;

        $actionResultComponents = is_array($actionResultComponent) ? $actionResultComponent : [$actionResultComponent];
        foreach ($actionResultComponents as $actionResultComponent) {
            $this->actionResultSafe .= $actionResultComponent->render();
        }

        $this->formatControlPanelMenu = new FormatControlPanelMenu($seriesName, $format);

        $this->viewSafe = $viewComponent ? $viewComponent->render() : '';
    }
}
