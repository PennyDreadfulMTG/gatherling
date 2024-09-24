<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

class PointsAdjustmentForm extends EventFrame
{
    public array $entries;

    public function __construct($event)
    {
        parent::__construct($event);
        $eventEntries = $event->getEntries();
        $entries = [];
        foreach ($eventEntries as $entry) {
            $player = getObjectVarsCamelCase($entry);
            $player['player'] = $entry->player;
            $player['adjustment'] = $event->getSeasonPointAdjustment($entry->player->name);
            if ($entry->medal != '') {
                $player['medalSrc'] = "styles/images/{$entry->medal}.png";
            }
            if ($entry->deck != null) {
                $player['verifiedSrc'] = 'styles/images/verified.png';
            }
            $entries[] = $player;
        }
        $this->entries = $entries;
    }
}
