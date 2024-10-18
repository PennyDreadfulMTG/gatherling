<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Exceptions\NotFoundException;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Player;

class Preregistration extends Component
{
    public bool $hasUpcomingEvents = false;
    /** @var array<array{eventLink: string, eventName: string, startingSoon: bool, startTime: Time, createDeckLink: CreateDeckLink|null, deckLink: DeckLink|null, unregLink: string}> */
    public array $upcomingEvents = [];
    /** @var array<array{eventLink: string, eventName: string, startTime: Time, isFull: bool, requiresMtgo: bool, requiresMtga: bool, isOpen: bool}> */
    public array $availableEvents = [];
    public bool $promptToLinkMtgo = false;
    public bool $promptToLinkMtga = false;

    public function __construct(Player $player)
    {
        if (!$player->name) {
            throw new NotFoundException("Tried to display preregistration for a player with no name");
        }

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
        $now = time();
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
            $eventLink = $targetUrl . '.php?event=' . rawurlencode($event->name ?? '');
            $eventName = $event->name ?? '';
            if (!$event->start || !strtotime($event->start)) {
                throw new NotFoundException("Event start time not found for event {$event->name}");
            }
            $startingSoon = time() >= strtotime($event->start);
            $startTime = new Time(strtotime($event->start), $now);
            if (!$event->id) {
                throw new NotFoundException("Event ID not found for event {$event->name}");
            }
            $entry = new Entry($event->id, $player->name);

            $createDeckLink = $deckLink = null;
            if (is_null($entry->deck)) {
                $createDeckLink = new CreateDeckLink($entry);
            } else {
                $deckLink = new DeckLink($entry->deck);
            }

            $unregLink = 'prereg.php?action=unreg&event=' . rawurlencode($event->name ?? '');
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
            $eventLink = 'eventreport.php?event=' . rawurlencode($event->name ?? '');
            $eventName = $event->name ?? '';
            if (!$event->start || !strtotime($event->start)) {
                throw new NotFoundException("Event start time not found for event {$event->name}");
            }
            $startTime = new Time(strtotime($event->start), time());
            $isFull = $event->isFull();
            $requiresMtgo = $event->client == 1 && empty($player->mtgo_username);
            $requiresMtga = $event->client == 2 && empty($player->mtga_username);
            $isOpen = !$isFull && !$requiresMtgo && !$requiresMtga;
            $this->availableEvents[] = [
                'eventLink' => $eventLink,
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
