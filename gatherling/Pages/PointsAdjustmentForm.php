<?php

declare(strict_types=1);

namespace Gatherling\Pages;

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
                $player['medalImg'] = theme_file("images/{$entry->medal}.png");
            }
            if ($entry->deck != null) {
                $player['verifiedImg'] = theme_file('images/verified.png');
            }
            $entries[] = $player;
        }
        $this->entries = $entries;
    }
}
