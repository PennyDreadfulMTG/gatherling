<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Format;

class FormatControlPanelMenu extends Component
{
    public string $formatName;
    public string $formatSettingsLink;
    public string $bAndRLink;
    public string $tribalLink = '';
    public string $cardsetsLink = '';
    public ?Tooltip $restrictedTooltip = null;

    public function __construct(public string $seriesName, Format $format)
    {
        $this->formatName = $format->name;
        $escaped = rawurlencode($format->name);
        $this->formatSettingsLink = "formatcp.php?view=settings&format={$escaped}";
        $this->bAndRLink = "formatcp.php?view=bandr&format={$escaped}";
        if ($format->tribal) {
            $this->tribalLink = "formatcp.php?view=tribal&format={$escaped}";
        }
        if ($format->eternal) {
            $this->restrictedTooltip = new Tooltip('Legal Sets', 'All sets are legal, as this is an Eternal format');
        } elseif ($format->modern) {
            $this->restrictedTooltip = new Tooltip('Legal Sets', 'This format uses Modern Legality to determine legal sets');
        } elseif ($format->standard) {
            $this->restrictedTooltip = new Tooltip('Legal Sets', 'This format uses Standard Legality to determine legal sets');
        } else {
            $this->cardsetsLink = "formatcp.php?view=cardsets&format={$escaped}";
        }
    }
}
