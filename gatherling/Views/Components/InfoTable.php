<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;

class InfoTable extends Component
{
    public string $line1;
    public ?string $mtgoUsername;
    public ?string $mtgaUsername;
    public ?string $discordHandle;
    public int $rating;
    public int $numMatches;
    public string $record;
    public int $hosted;
    public string $favF;
    public int $pcgF;
    public string $favS;
    public int $pcgS;
    public ?Time $lastEventTime;
    public ?string $lastEventName;
    public ?string $email;
    public bool $emailIsPublic;
    public string $timeZone;

    public function __construct(Player $player)
    {
        parent::__construct('partials/infoTable');
        $ndx = 0;
        $max = 0;
        $sum = 0;
        $favF = '';
        foreach ($player->getFormatsPlayedStats() as $tmprow) {
            $sum += $tmprow['cnt'];
            if ($ndx == 0) {
                $max = $tmprow['cnt'];
                $favF = $tmprow['format'];
            }
            $ndx++;
        }
        $pcgF = 0;
        if ($sum > 0) {
            $pcgF = (int) round(($max / $sum) * 100);
        }
        $ndx = 0;
        $sum = 0;
        $favS = '';
        foreach ($player->getSeriesPlayedStats() as $tmprow) {
            $sum += $tmprow['cnt'];
            if ($ndx == 0) {
                $max = $tmprow['cnt'];
                $favS = $tmprow['series'];
            }
            $ndx++;
        }
        $pcgS = 0;
        if ($sum > 0) {
            $pcgS = (int) round(($max / $sum) * 100);
        }

        $matches = $player->getAllMatches();
        $lastEvent = $player->getLastEventPlayed();

        $this->line1 = strtoupper($player->name);
        $this->mtgoUsername = $player->mtgo_username;
        $this->mtgaUsername = $player->mtga_username;
        $this->discordHandle = $player->discord_handle;
        $this->rating = $player->getRating();
        $this->record = $player->getRecord();
        $this->favF = $favF;
        $this->pcgF = $pcgF;
        $this->favS = $favS;
        $this->pcgS = $pcgS;
        $this->hosted = $player->getHostedEventsCount();
        if ($lastEvent) {
            $this->lastEventTime = new Time(strtotime($lastEvent->start), time(), true);
            $this->lastEventName = $lastEvent->name;
        }
        $this->numMatches = count($matches);
        $this->email = $player->emailAddress;
        $this->emailIsPublic = $player->emailIsPublic();
        $this->timeZone = $player->time_zone() ?? '';
    }
}
