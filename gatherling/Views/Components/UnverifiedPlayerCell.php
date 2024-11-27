<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;

class UnverifiedPlayerCell
{
    public string $playerName;
    public int|null|false $wins;
    public int|null|false $losses;
    public GameName $displayName;
    public GameName $displayNameText;
    public bool $hasDropped;
    public bool $hasGames;
    public ?string $matchResult;
    public bool $isDraw;
    public ?string $verification;

    public function __construct(Event $event, Matchup $match, Player $player)
    {
        $this->playerName = $player->name;
        $this->wins = $match->getPlayerWins($this->playerName);
        $this->losses = $match->getPlayerLosses($this->playerName);
        $this->hasGames = is_int($this->wins) && is_int($this->losses) && $this->wins + $this->losses > 0;
        $this->matchResult = $this->hasGames ? ($this->wins > $this->losses ? 'W' : 'L') : null;
        $this->displayName = new GameName($player, $event->client);
        $this->displayNameText = new GameName($player, $event->client, false);
        $this->hasDropped = $match->playerDropped($this->playerName);
        $this->isDraw = ($this->wins == 1 && $this->losses == 1);
        $this->verification = $match->verification;
    }
}
