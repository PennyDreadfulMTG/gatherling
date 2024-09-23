<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

class FormatCPMenu extends Component
{
    public string $activeFormatName;
    public string $formatSettingsLink;
    public string $bAndRLink;
    public string $tribalLink = '';
    public string $cardsetsLink = '';
    public ?Tooltip $restrictedTooltip;

    public function __construct(public string $seriesName, Format $activeFormat)
    {
        parent::__construct('partials/formatCPMenu');
        $this->activeFormatName = $activeFormat->name;
        $escaped = rawurlencode($activeFormat->name);
        $this->formatSettingsLink = "formatcp.php?view=settings&format={$escaped}";
        $this->bAndRLink = "formatcp.php?view=bandr&format={$escaped}";
        if ($activeFormat->tribal) {
            $this->tribalLink= "formatcp.php?view=tribal&format={$escaped}";
        }
        if ($activeFormat->eternal) {
            $this->restrictedTooltip = new Tooltip('Legal Sets', 'All sets are legal, as this is an Eternal format');
        } elseif ($activeFormat->modern) {
            $this->restrictedTooltip = new Tooltip('Legal Sets', 'This format uses Modern Legality to determine legal sets');
        } elseif ($activeFormat->standard) {
            $this->restrictedTooltip = new Tooltip('Legal Sets', 'This format uses Standard Legality to determine legal sets');
        } else {
            $this->cardsetsLink = "formatcp.php?view=cardsets&format={$escaped}";
        }
    }
}
