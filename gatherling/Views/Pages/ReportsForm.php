<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\Player;

class ReportsForm extends EventFrame
{
    public bool $hasEntries;
    /** @var list<array<string, int|string>> */
    public array $standings;
    /** @var list<array<string, int|string>> */
    public array $registrants;

    public function __construct(Event $event)
    {
        parent::__construct($event);
        $entriesByDateTime = $event->getEntriesByDateTime();
        $entriesByMedal = $event->getEntriesByMedal();
        $hasEntries = count($entriesByDateTime) > 0;

        $assembleEntries = function ($entries) {
            $count = 1;
            $result = [];
            foreach ($entries as $entryName) {
                $player = new Player($entryName);
                $result[] = [
                    'n'         => $count,
                    'entryName' => $entryName,
                    'emailAd'   => $player->emailAddress != '' ? $player->emailAddress : '---------',
                ];
                $count++;
            }

            return $result;
        };

        $this->hasEntries = $hasEntries;
        $this->standings = $assembleEntries($entriesByMedal);
        $this->registrants = $assembleEntries($entriesByDateTime);
    }
}
