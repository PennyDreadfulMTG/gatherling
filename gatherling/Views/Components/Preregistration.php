<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Safe\DateTimeImmutable;

class Preregistration extends Component
{
    public bool $hasUpcomingEvents = false;
    /** @var array<array{eventLink: string, eventName: string, startingSoon: bool, startTime: Time, createDeckLink: CreateDeckLink|null, deckLink: DeckLink|null, unregLink: string}> */
    public array $upcomingEvents = [];
    /** @var array<array{eventReportLink: string, eventName: string, startTime: Time, isFull: bool, requiresMtgo: bool, requiresMtga: bool, isOpen: bool, registerLink: string}> */
    public array $availableEvents = [];
    public bool $promptToLinkMtgo = false;
    public bool $promptToLinkMtga = false;

    public function __construct(Player $player)
    {
        $upcomingEvents = Event::getUpcomingEvents($player->name);
        $events = Event::getNextPreRegister();

        $availableEvents = [];
        $series = [];

        foreach ($events as $event) {
            if (in_array($event->series, $series)) {
                continue;
            }
            $series[] = $event->series;
            if ($event->hasRegistrant($player->name)) {
                continue;
            }
            $availableEvents[] = $event;
        }

        $this->hasUpcomingEvents = count($upcomingEvents) > 0;

        $arena = $mtgo = false;
        $now = new DateTimeImmutable();
        foreach ($upcomingEvents as $event) {
            if ($event->client == 1) {
                $mtgo = true;
            } elseif ($event->client == 2) {
                $arena = true;
            }

            $targetUrl = 'eventreport';
            if ($event->authCheck($player->name)) {
                $targetUrl = 'event';
            }
            $eventLink = $targetUrl . '.php?event=' . rawurlencode($event->name);
            $eventName = $event->name;
            $startingSoon = $now >= $event->start;
            $startTime = new Time($event->start, $now);
            $entry = new Entry($event->id, $player->name);

            $createDeckLink = $deckLink = null;
            if (is_null($entry->deck)) {
                $createDeckLink = new CreateDeckLink($entry);
            } else {
                $deckLink = new DeckLink($entry->deck);
            }

            $unregLink = 'prereg.php?action=unreg&event=' . rawurlencode($event->name);
            $this->upcomingEvents[] = [
                'eventLink' => $eventLink,
                'eventName' => $eventName,
                'startingSoon' => $startingSoon,
                'startTime' => $startTime,
                'createDeckLink' => $createDeckLink,
                'deckLink' => $deckLink,
                'unregLink' => $unregLink,
            ];
        }
        if ($mtgo && empty($player->mtgo_username)) {
            $this->promptToLinkMtgo = true;
        }
        if ($arena && empty($player->mtga_username)) {
            $this->promptToLinkMtga = true;
        }

        foreach ($availableEvents as $event) {
            $eventReportLink = 'eventreport.php?event=' . rawurlencode($event->name);
            $eventName = $event->name;
            $startTime = new Time($event->start, $now);
            $isFull = $event->isFull();
            $requiresMtgo = $event->client == 1 && empty($player->mtgo_username);
            $requiresMtga = $event->client == 2 && empty($player->mtga_username);
            $isOpen = !$isFull && !$requiresMtgo && !$requiresMtga;
            $this->availableEvents[] = [
                'eventReportLink' => $eventReportLink,
                'eventName' => $eventName,
                'startTime' => $startTime,
                'isFull' => $isFull,
                'requiresMtgo' => $requiresMtgo,
                'requiresMtga' => $requiresMtga,
                'isOpen' => $isOpen,
                'registerLink' => 'prereg.php?action=reg&event=' . rawurlencode($eventName),
            ];
        }
    }
}
