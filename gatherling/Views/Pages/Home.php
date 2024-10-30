<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Views\Components\ColorImages;
use Gatherling\Views\Components\Time;
use Gatherling\Models\UpcomingEventDto;

class Home extends Page
{
    /** @var list<array{name: string, format: string, currentRound: int, reportLink: string}> */
    public array $activeEvents = [];
    public bool $hasActiveEvents;
    /** @var list<array{name: string, format: string, reportLink: string, time: Time}> */
    public array $upcomingEvents = [];
    public bool $hasUpcomingEvents;
    /** @var ?array{name: string, link: string} */
    public ?array $playerInfo = null;
    /** @var ?array{name: string, link: string} */
    public ?array $mostRecentHostedEvent = null;
    /** @var list<array{eventName: string, reportLink: string, playerLink: string, deckLink: string, playerName: string, deckName: string, colorImages: ColorImages}> */
    public array $recentWinners;
    public bool $hasRecentWinners;

    /**
     * @param list<Event> $activeEvents
     * @param list<UpcomingEventDto> $upcomingEvents
     * @param array<string, int> $stats
     * @param list<array{event: string, player: string, name: string, id: int, colorImages: ColorImages}> $recentWinners
     */
    public function __construct(array $activeEvents, array $upcomingEvents, public array $stats, ?Player $player, ?Event $mostRecentHostedEvent, array $recentWinners)
    {
        parent::__construct('Home');
        foreach ($activeEvents as $event) {
            $this->activeEvents[] = [
                'name' => $event->name,
                'format' => $event->format,
                'currentRound' => $event->current_round,
                'reportLink' => 'eventreport.php?event=' . rawurlencode($event->name),
            ];
        }
        $this->hasActiveEvents = count($this->activeEvents) > 0;
        foreach ($upcomingEvents as $event) {
            $this->upcomingEvents[] = [
                'name' => $event->name,
                'format' => $event->format,
                'reportLink' => 'eventreport.php?event=' . rawurlencode($event->name),
                'time' => new Time($event->d, time()),
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
                'colorImages' => $winner['colorImages'],
            ];
        }
        $this->hasRecentWinners = count($recentWinners) > 0;
    }
}
