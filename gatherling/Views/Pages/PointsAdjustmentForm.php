<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;

class PointsAdjustmentForm extends EventFrame
{
    /** @var list<array{playerName: string, adjustment: int, reason: string, medalSrc: ?string, verifiedSrc: ?string}> */
    public array $entries;

    public function __construct(Event $event)
    {
        parent::__construct($event);
        $eventEntries = $event->getEntries();
        $entries = [];
        foreach ($eventEntries as $entry) {
            $adjustmentDetails = $event->getSeasonPointAdjustment($entry->player->name);
            $entries[] = [
                'playerName' => $entry->player->name,
                'adjustment' => $adjustmentDetails['adjustment'],
                'reason' => $adjustmentDetails['reason'],
                'medalSrc' => $entry->medal ? "styles/images/{$entry->medal}.png" : null,
                'verifiedSrc' => $entry->deck ? 'styles/images/verified.png' : null
            ];
        }
        $this->entries = $entries;
    }
}
