<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Entry;
use Gatherling\Models\Standings;

class EntryListItem
{
    public int $dropRound;
    public string $eventName;
    public string $playerName;
    public ?string $emailAddress;
    public bool $canDrop = false;
    public bool $canUndrop = false;
    public string $undropLink = '';
    public string $medalSrc = '';
    public GameName $gameName;
    public ?DeckLink $deckLink = null;
    public ?CreateDeckLink $createDeckLink = null;
    public bool $invalidRegistration = false;
    public string $tribe = '';
    public ?InitialByesDropMenu $initialByeDropMenu = null;
    public ?InitialSeedDropMenu $initialSeedDropMenu = null;
    public bool $canDelete = false;
    public ?NotAllowed $notAllowed = null;
    public bool $isSwiss = false;
    public bool $hasStarted = false;
    public int $initialByes = 0;
    public bool $isSingleElim = false;
    public int $initialSeed = 0;

    public function __construct(Entry $entry, int $numEntries, public bool $isTribal)
    {
        $this->dropRound = $entry->drop_round;
        $this->eventName = $entry->event->name;
        $this->playerName = $entry->player->name;
        $this->emailAddress = $entry->player->emailAddress;
        if ($entry->event->active == 1) {
            $playerActive = Standings::playerActive($entry->event->name, $entry->player->name);
            $this->canDrop = $playerActive;
            $this->canUndrop = !$playerActive;
            $undropParams = [
                'view' => 'reg',
                'player' => $entry->player->name,
                'event' => $entry->event->id,
                'action' => 'undrop',
                'event_id' => $entry->event->id,
            ];
            $this->undropLink = 'event.php?' . http_build_query($undropParams, '', '&', PHP_QUERY_RFC3986);
        }
        if ($entry->event->isFinished() && $entry->medal !== '') {
            $this->medalSrc = "styles/images/{$entry->medal}.png";
        }
        $this->gameName = new GameName($entry->player, $entry->event->client);
        if ($entry->deck) {
            $this->deckLink = new DeckLink($entry->deck);
        } else {
            $this->createDeckLink = new CreateDeckLink($entry);
        }
        $this->invalidRegistration = $entry->deck != null && !$entry->deck->isValid();
        $this->tribe = $isTribal && $entry->deck !== null && $entry->deck->tribe !== null ? $entry->deck->tribe : '';
        $this->isSwiss = $entry->event->isSwiss();
        $this->hasStarted = $entry->event->hasStarted();
        $this->initialByes = $entry->initial_byes;
        $this->isSingleElim = $entry->event->isSingleElim();
        $this->initialSeed = $entry->initial_seed;
        if ($this->isSwiss && !$this->hasStarted) {
            $this->initialByeDropMenu = new InitialByesDropMenu('initial_byes[]', $entry->player->name, $entry->initial_byes);
        } elseif ($entry->event->isSingleElim() && !$entry->event->hasStarted()) {
            $this->initialSeedDropMenu = new InitialSeedDropMenu('initial_seed[]', $entry->player->name, $entry->initial_seed, $numEntries);
        }
        if ($entry->canDelete()) {
            $this->canDelete = true;
        } else {
            $this->notAllowed = new NotAllowed("Can't delete player, they have matches recorded.");
        }
    }
}
