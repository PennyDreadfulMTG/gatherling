<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Format;
use Gatherling\Models\Standings;
use Gatherling\Views\Components\CreateDeckLink;
use Gatherling\Views\Components\DeckLink;
use Gatherling\Views\Components\GameName;
use Gatherling\Views\Components\NotAllowed;
use Gatherling\Views\Components\StringField;
use Gatherling\Views\Components\InitialByesDropMenu;
use Gatherling\Views\Components\InitialSeedDropMenu;

class PlayerList extends EventFrame
{
    public bool $isActive;
    public bool $isOngoing;
    public bool $isFinished;
    public bool $notYetStarted;
    public bool $hasStarted;
    public bool $hasEntries;
    public int $numEntries;
    /** @var list<array<string, mixed>> */
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
            $entryInfoList[] = entryListArgs($entry, $numEntries, (bool) $format->tribal);
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

/** @return array<string, mixed> */
function entryListArgs(Entry $entry, int $numEntries, bool $isTribal): array
{
    $entryInfo = getObjectVarsCamelCase($entry);
    if ($entry->event->active == 1) {
        $playerActive = Standings::playerActive($entry->event->name, $entry->player->name);
        $entryInfo['canDrop'] = $playerActive;
        $entryInfo['canUndrop'] = !$playerActive;
        $undropParams = [
            'view' => 'reg',
            'player' => $entry->player->name,
            'event' => $entry->event->id,
            'action' => 'undrop',
            'event_id' => $entry->event->id,
        ];
        $entryInfo['undropLink'] = 'event.php?' . http_build_query($undropParams, '', '&', PHP_QUERY_RFC3986);
    }
    if ($entry->event->isFinished() && strcmp('', $entry->medal) != 0) {
        $entryInfo['medalSrc'] = "styles/images/{$entry->medal}.png";
    }
    $entryInfo['gameName'] = new GameName($entry->player, $entry->event->client);
    if ($entry->deck) {
        $entryInfo['deckLink'] = new DeckLink($entry->deck);
    } else {
        $entryInfo['createDeckLink'] = new CreateDeckLink($entry);
    }
    $entryInfo['invalidRegistration'] = $entry->deck != null && !$entry->deck->isValid();
    $entryInfo['tribe'] = $isTribal && $entry->deck != null ? $entry->deck->tribe : '';
    if ($entry->event->isSwiss() && !$entry->event->hasStarted()) {
        $entryInfo['initialByeDropMenu'] = new InitialByesDropMenu('initial_byes[]', $entry->player->name, $entry->initial_byes);
    } elseif ($entry->event->isSingleElim() && !$entry->event->hasStarted()) {
        $entryInfo['initialSeedDropMenu'] = new InitialSeedDropMenu('initial_seed[]', $entry->player->name, $entry->initial_seed, $numEntries);
    }
    if ($entry->canDelete()) {
        $entryInfo['canDelete'] = $entry->canDelete();
    } else {
        $entryInfo['notAllowed'] = new NotAllowed("Can't delete player, they have matches recorded.");
    }

    return $entryInfo;
}
