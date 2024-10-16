<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use InvalidArgumentException;

class DeckForm extends Component
{
    public bool $create;
    public string $contents = '';
    public string $sideboard = '';
    public string $desc = '';
    public string $archetype = '';
    public string $name = '';
    public FileInput $fileInput;
    public TextInput $nameTextInput;
    public SelectInput $deckArchetypeDropMenu;
    /** @var array<array{id: int, name: string}> */
    public array $recentDecks = [];
    public bool $showErrors = false;
    /** @var array<string> */
    public array $errors = [];
    public ?int $deckId;
    public string $playerName;
    public ?int $eventId;

    public function __construct(?Deck $deck, string $playerName, Event $event)
    {
        parent::__construct('partials/deckForm');

        $this->create = is_null($deck) || $deck->id == 0;
        if (!$this->create) {
            $playerName = $deck->playername ?? false;
        }
        if (!$playerName) {
            throw new InvalidArgumentException('Player name is required');
        }

        if ($deck && !$this->create) {
            foreach ($deck->maindeck_cards as $card => $amt) {
                $line = $amt . ' ' . $card . "\n";
                $this->contents .= $line;
            }
            foreach ($deck->sideboard_cards as $card => $amt) {
                $line = $amt . ' ' . $card . "\n";
                $this->sideboard .= $line;
            }
            $this->desc = $deck->notes ?? '';
            $this->archetype = $deck->archetype ?? '';
            $this->name = $deck->name ?? '';
        }

        $deckplayer = new Player($playerName);
        $recentdecks = $deckplayer->getRecentDecks();
        foreach ($recentdecks as $aDeck) {
            if ($aDeck->id && $aDeck->name) {
                $this->recentDecks[] = [
                    'id' => $aDeck->id,
                    'name' => $aDeck->name,
                ];
            }
        }

        if ($deck && !$this->create && count($deck->errors) > 0) {
            $this->showErrors = true;
            $this->errors = $deck->errors;
        }
        $this->fileInput = new FileInput('Import File', 'txt');
        $this->nameTextInput = new TextInput('Name', 'name', $this->name, 40, null, 'deck-name');
        if ($deck && !$this->create) {
            $this->deckId = $deck->id;
        }
        $this->deckArchetypeDropMenu = new DeckArchetypeDropMenu($this->archetype);
        $this->playerName = $playerName;
        $this->eventId = $event->id;
    }
}
