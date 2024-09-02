<?php

namespace Gatherling\Pages;

use Gatherling\Entry;
use Gatherling\Event;
use Gatherling\Format;
use Gatherling\Standings;

class PlayerList extends EventFrame {
    public bool $isActive;
    public bool $isOngoing;
    public bool $isFinished;
    public bool $notYetStarted;
    public bool $hasStarted;
    public bool $hasEntries;
    public int $numEntries;
    public array $entries;
    public bool $isSwiss;
    public bool $isSingleElim;
    public bool $isNeitherSwissNorSingleElim;
    public Format $format;
    public ?array $newEntry;
    public bool $showCreateNextEvent;
    public bool $showCreateNextSeason;
    public array $deckless;

    public function __construct(Event $event) {
        parent::__construct($event);
        $isActive = $event->active == 1;
        $isOngoing = $event->active == 1 && !$event->finalized;
        $notYetStarted = $event->active == 0 && !$event->finalized;
        $entries = $event->getEntries();
        $numEntries = count($entries);
        $format = new Format($event->format);

        $deckless = $entryInfoList = [];
        foreach ($entries as $entry) {
            $entryInfoList[] = entryListArgs($entry, $numEntries, (bool)$format->tribal);
            if (!$entry->deck) {
                $deckless[] = $entry->player->gameNameArgs($entry->event->client);
            }
        }

        $newEntry = null;
        if ($notYetStarted || $isOngoing) {
            $newEntry = stringFieldArgs('newentry', '', 40);
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
        $this->deckless = $deckless;
    }
}

function entryListArgs(Entry $entry, int $numEntries, bool $isTribal): array
{
    $entryInfo = getObjectVarsCamelCase($entry);
    if ($entry->event->active == 1) {
        $playerActive = Standings::playerActive($entry->event->name, $entry->player->name);
        $entryInfo['canDrop'] = $playerActive;
        $entryInfo['canUndrop'] = !$playerActive;
    }
    if ($entry->event->isFinished() && strcmp('', $entry->medal) != 0) {
        $entryInfo['medalImg'] = theme_file("images/{$entry->medal}.png");
    }
    $entryInfo['gameName'] = $entry->player->gameNameArgs($entry->event->client);
    if ($entry->deck) {
        $entryInfo['linkTo'] = $entry->deck->linkToArgs();
    } else {
        $entryInfo['createDeckLink'] = $entry->createDeckLinkArgs();
    }
    $entryInfo['invalidRegistration'] = $entry->deck != null && !$entry->deck->isValid();
    $entryInfo['tribe'] = $isTribal && $entry->deck != null ? $entry->deck->tribe : '';
    if ($entry->event->isSwiss() && !$entry->event->hasStarted()) {
        $entryInfo['initialByeDropMenu'] = initialByeDropMenuArgs('initial_byes[]', $entry->player->name, $entry->initial_byes);
    } elseif ($entry->event->isSingleElim() && !$entry->event->hasStarted()) {
        $entryInfo['initialSeedDropMenu'] = initialSeedDropMenuArgs('initial_seed[]', $entry->player->name, $entry->initial_seed, $numEntries);
    }
    if ($entry->canDelete()) {
        $entryInfo['canDelete'] = $entry->canDelete();
    } else {
        $entryInfo['notAllowed'] = notAllowedArgs("Can't delete player, they have matches recorded.");
    }
    return $entryInfo;
}

function initialByeDropMenuArgs(string $name = 'initial_byes', string $playerName = '', int $currentByes = 0): array
{
    $options = [];
    for ($i = 0; $i < 3; $i++) {
        $options[] = [
            'value' => "$playerName $i",
            'text' => $i == 0 ? 'None' : "$i",
            'isSelected' => $currentByes == $i,
        ];
    }
    return [
        'name' => $name,
        'options' => $options,
    ];
}

function initialSeedDropMenuArgs(string $name, string $playerName, int $currentSeed, int $numEntries): array
{
    $options = [
        ['value' => "$playerName 127", 'text' => 'None', 'isSelected' => $currentSeed == 127],
    ];
    for ($i = 1; $i <= $numEntries; $i++) {
        $options[] = [
            'value' => "$playerName $i",
            'text' => "$i",
            'isSelected' => $currentSeed == $i,
        ];
    }
    return [
        'name' => $name,
        'options' => $options,
    ];
}
