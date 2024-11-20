<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\Format;
use Gatherling\Views\Components\EntryListItem;
use Gatherling\Views\Components\StringField;

class PlayerList extends EventFrame
{
    public bool $isActive;
    public bool $isOngoing;
    public bool $isFinished;
    public bool $notYetStarted;
    public bool $hasStarted;
    public bool $hasEntries;
    public int $numEntries;
    /** @var list<EntryListItem> */
    public array $entries;
    public bool $isSwiss;
    public bool $isSingleElim;
    public bool $isNeitherSwissNorSingleElim;
    public Format $format;
    public ?StringField $newEntry;
    public bool $showCreateNextEvent;
    public bool $showCreateNextSeason;
    public string $deckless;

    public function __construct(Event $event)
    {
        parent::__construct($event);
        $isActive = $event->active == 1;
        $isOngoing = $event->active == 1 && !$event->finalized;
        $notYetStarted = $event->active == 0 && !$event->finalized;
        $entries = $event->getEntries();
        $numEntries = count($entries);
        $format = new Format($event->format);

        $deckless = $entryInfoList = [];
        foreach ($entries as $entry) {
            $entryInfoList[] = new EntryListItem($entry, $numEntries, (bool) $format->tribal);
            if (!$entry->deck) {
                $deckless[] = $entry->player->name;
            }
        }

        $newEntry = null;
        if ($notYetStarted || $isOngoing) {
            $newEntry = new StringField('newentry', '', 40);
        }

        $showCreateNextEvent = $showCreateNextSeason = false;
        if ($event->isFinished()) {
            $nextEventName = sprintf('%s %d.%02d', $event->series, $event->season, $event->number + 1);
            $nextSeasonName = sprintf('%s %d.%02d', $event->series, $event->season + 1, 1);
            $showCreateNextEvent = Event::exists($nextEventName);
            $showCreateNextSeason = Event::exists($nextSeasonName);
        }

        $this->isActive = $isActive;
        $this->isOngoing = $isOngoing;
        $this->isFinished = $event->isFinished();
        $this->notYetStarted = $notYetStarted;
        $this->hasStarted = $event->hasStarted();
        $this->hasEntries = $numEntries > 0;
        $this->numEntries = $numEntries;
        $this->entries = $entryInfoList;
        $this->isSwiss = $event->isSwiss();
        $this->isSingleElim = $event->isSingleElim();
        $this->isNeitherSwissNorSingleElim = !$event->isSwiss() && !$event->isSingleElim();
        $this->format = $format;
        $this->newEntry = $newEntry;
        $this->showCreateNextEvent = $showCreateNextEvent;
        $this->showCreateNextSeason = $showCreateNextSeason;
        $this->deckless = implode(', ', $deckless);
    }
}
