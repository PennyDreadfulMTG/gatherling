<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Views\Components\Component;
use Gatherling\Views\Components\Time;

class Home extends Page
{
    /** @var list<array<string, string|int|Component>> */
    public array $activeEvents;
    public bool $hasActiveEvents;
    public bool $hasUpcomingEvents;
    /** @var array<string, string> */
    public array $playerInfo = [];
    /** @var array<string, string> */
    public array $mostRecentHostedEvent = [];
    /** @var list<array<string, string>> */
    public array $recentWinners;
    public bool $hasRecentWinners;

    /**
     * @param list<Event> $activeEvents
     * @param list<array{d: int, format: string, series: string, name: string, threadurl: string}> $upcomingEvents
     * @param array<string, int> $stats
     * @param list<array<string, int|string>> $recentWinners
     */
    public function __construct(array $activeEvents, public array $upcomingEvents, public array $stats, ?Player $player, ?Event $mostRecentHostedEvent, array $recentWinners)
    {
        parent::__construct();
        $this->activeEvents = [];
        foreach ($activeEvents as $event) {
            $this->activeEvents[] = [
                'name' => $event->name,
                'format' => $event->format,
                'currentRound' => $event->current_round,
                'reportLink' => 'eventreport.php?event=' . rawurlencode($event->name),
            ];
        }
        $this->hasActiveEvents = count($this->activeEvents) > 0;
        $this->upcomingEvents = [];
        foreach ($upcomingEvents as $event) {
            $this->upcomingEvents[] = [
                'name' => $event['name'],
                'format' => $event['format'],
                'reportLink' => 'eventreport.php?event=' . rawurlencode($event['name']),
                'time' => new Time($event['d'], time()),
            ];
        }
        $this->hasUpcomingEvents = count($this->upcomingEvents) > 0;
        if ($player) {
            $this->playerInfo = [
                'name' => $player->name,
                'link' => 'profile.php?name=' . rawurlencode($player->name),
            ];
        }
        if ($mostRecentHostedEvent) {
            $this->mostRecentHostedEvent = [
                'name' => $mostRecentHostedEvent->name,
                'link' => 'event.php?name=' . rawurlencode($mostRecentHostedEvent->name),
            ];
        }
        foreach ($recentWinners as $winner) {
            $this->recentWinners[] = [
                'eventName' => $winner['event'],
                'reportLink' => 'eventreport.php?event=' . rawurlencode($winner['event']),
                'playerLink' => 'profile.php?player=' . rawurlencode($winner['player']),
                'deckLink' => 'deck.php?mode=view&event=' . rawurlencode($winner['event']),
                'playerName' => $winner['player'],
                'deckName' => $winner['name'],
                'manaSymbolSafe' => $winner['manaSymbolSafe'],
            ];
        }
        $this->hasRecentWinners = count($recentWinners) > 0;
    }
}