<?php

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Views\Components\Prereg;
use Gatherling\Views\Components\InfoCell;
use Gatherling\Views\Components\Finalists;
use Gatherling\Views\Components\MetaStats;
use Gatherling\Views\Components\TrophyCell;
use Gatherling\Views\Components\FullMetagame;

class EventReport extends Page
{
    public string $seriesLogoSrc;
    public InfoCell $infoCell;
    public bool $isFinalized;
    public TrophyCell $trophyCell;
    public Finalists $finalists;
    public bool $isRegistrationOpen;
    public Prereg $prereg;
    public bool $hasStarted;
    public MetaStats $metaStats;
    public FullMetagame $fullMetagame;

    public function __construct(Event $event, bool $canPrereg)
    {
        parent::__construct();
        $this->title = 'Event Report';
        $this->seriesLogoSrc = 'displaySeries.php?series=' . rawurlencode($event->series);
        $this->infoCell = new InfoCell($event);
        if ($event->finalized) {
            $this->isFinalized = true;
            $this->trophyCell = new TrophyCell($event);
            $this->finalists = new Finalists($event->getFinalists());
        } elseif ($canPrereg) {
            $this->isRegistrationOpen = true;
            $this->prereg = new Prereg($event);
        }
        if ($event->active || $event->finalized) {
            $this->hasStarted = true;
            $this->metaStats = new MetaStats($event);
        }
        $this->fullMetagame = new FullMetagame($event);
    }
}
