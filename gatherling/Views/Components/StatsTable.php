<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;
use Gatherling\Models\Event;

class StatsTable extends Component
{
    public string $record;
    public int $longestWinningStreak;
    public int $longestLosingStreak;
    public bool $hasRival = false;
    public ?PlayerLink $rivalLink = null;
    public string $rivalRecord = '';
    public string $favoriteNonLand;
    public string $favoriteLand;
    public int $medalsWon;
    public int $eventsWon;
    public ?string $mostRecentTrophySrc;
    public ?string $eventLink;

    public function __construct(Player $player)
    {
        $this->record = $player->getRecord();
        $this->longestWinningStreak = $player->getStreak('W');
        $this->longestLosingStreak = $player->getStreak('L');
        $rival = $player->getRival();
        if ($rival != null && $rival->name) {
            $this->hasRival = true;
            $this->rivalLink = new PlayerLink($rival);
            $this->rivalRecord = $player->getRecordVs($rival->name);
        }
        $this->favoriteNonLand = $player->getFavoriteNonLand();
        $this->favoriteLand = $player->getFavoriteLand();
        $this->medalsWon = $player->getMedalCount();
        $this->eventsWon = $player->getMedalCount('1st');
        $trophyEvent = $player->getLastEventWithTrophy();
        if ($trophyEvent != null) {
            $event = new Event($trophyEvent);
            if ($event->name) {
                $this->mostRecentTrophySrc = Event::trophySrc($event->name);
                $this->eventLink = 'deck.php?mode=view&event=' . rawurlencode((string) $event->id);
            }
        }
    }
}
