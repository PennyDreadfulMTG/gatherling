<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use Gatherling\Models\Standings;
use Gatherling\Views\Components\CurrentMatchesTable;
use Gatherling\Views\Components\RatingsTableSmall;
use Gatherling\Views\Components\RecentDecksTable;
use Gatherling\Views\Components\RecentMatchTable;
use Gatherling\Views\Components\StatsTable;

use function Gatherling\Helpers\session;

class MainPlayerControlPanel extends Component
{
    public Preregistration $preregistration;
    public ActiveEvents $activeEvents;
    public ?CurrentMatchesTable $currentMatchesTable;
    public RecentDecksTable $recentDecksTable;
    public RecentMatchTable $recentMatchTable;
    public RatingsTableSmall $ratingsTableSmall;
    public StatsTable $statsTable;
    public ?string $emailAddress;
    public ?string $mtgoUsername;
    public ?string $mtgaUsername;
    public bool $promptToLinkDiscord = false;
    public string $unlinkedDiscordName = '';
    public string $discordHandle = '';

    public function __construct(Player $player)
    {
        $this->preregistration = new Preregistration($player);
        ['events' => $events, 'leagueMatches' => $leagueMatches] = $this->activeEventsAndMatches($player);
        $this->activeEvents = new ActiveEvents($events);
        $matches = $player->getCurrentMatches();
        if (!empty($matches) || !empty($leagueMatches)) {
            $this->currentMatchesTable = new CurrentMatchesTable($player, $matches, $leagueMatches);
        }
        $this->recentDecksTable = new RecentDecksTable($player);
        $this->recentMatchTable = new RecentMatchTable($player);
        $this->ratingsTableSmall = new RatingsTableSmall($player);
        $this->statsTable = new StatsTable($player);
        $this->emailAddress = $player->emailAddress;
        $this->mtgoUsername = $player->mtgo_username;
        $this->mtgaUsername = $player->mtga_username;
        if (empty($player->discord_id) && session()->optionalString('DISCORD_ID')) {
            $this->unlinkedDiscordName = session()->string('DISCORD_NAME', '');
        } elseif (empty($player->discord_id)) {
            $this->promptToLinkDiscord = true;
        } else {
            $this->discordHandle = $player->discord_handle ?? '';
        }
    }

    /** @return array{events: array<array{eventLink: string, eventName: string, showDiscordRoom: bool, discordChannelName: string, discordGuildName: string, showMtgoRoom: bool, mtgoRoom: string, standingsLink: string, dropLink: string, joinLink: string, joinLinkText: string, createDeckLink: ?CreateDeckLink, showCreateDeckLink: bool}>, leagueMatches: list<array{eventName: string, matchCount: int, reportLink: string}>} */
    private function activeEventsAndMatches(Player $player): array
    {
        $eventInfo = [];
        $leagueMatches = [];

        $events = Event::getActiveEvents();

        foreach ($events as $event) {
            if (!$event->name || !$event->id) {
                continue;
            }
            $playerActive = $player->name ? Standings::playerActive($event->name, $player->name) : false;
            if (!$playerActive && $event->private) {
                continue;
            }
            $targetUrl = 'eventreport';
            if ($event->authCheck($player->name)) {
                $targetUrl = 'event';
            }
            $eventLink = $targetUrl . '.php?event=' . rawurlencode($event->name);
            $eventName = $event->name;
            $series = new Series($event->series);
            $showDiscordRoom = $series->discord_guild_name && $series->discord_channel_name;
            $discordChannelName = $series->discord_channel_name ?? '';
            $discordGuildName = $series->discord_guild_name ?? '';
            $showMtgoRoom = !$showDiscordRoom && $series->mtgo_room;
            $mtgoRoom = $showMtgoRoom ? $series->mtgo_room ?? '' : '';
            $standingsLink = 'player.php?mode=standings&event=' . rawurlencode($event->name);
            if ($event->current_round > $event->mainrounds) {
                $structure = $event->finalstruct;
                $subevent_id = $event->finalid;
            } else {
                $structure = $event->mainstruct;
                $subevent_id = $event->mainid;
            }
            $isLeague = $structure === 'League' || $structure === 'League Match';
            $dropLink = $joinLink = $joinLinkText = '';
            $createDeckLink = null;
            if ($playerActive) {
                assert($player->name !== null); // You can't be active if you don't exist
                $entry = new Entry($event->id, $player->name);
                if (is_null($entry->deck) || !$entry->deck->isValid()) {
                    $createDeckLink = new CreateDeckLink($entry);
                } elseif ($structure == 'League') {
                    $count = $event->getPlayerLeagueMatchCount($player->name) + 1;
                    if ($count <= $event->leagueLength()) {
                        $leagueMatches[] = [
                            'eventName' => $event->name,
                            'matchCount' => $count,
                            'reportLink' => reportLeagueGameLink($event, $subevent_id),
                        ];
                    }
                } elseif ($structure == 'League Match') {
                    $count = $event->getPlayerLeagueMatchCount($player->name);
                    if ($count < 1) {
                        $leagueMatches[] = [
                            'eventName' => $event->name,
                            'matchCount' => $count,
                            'reportLink' => reportLeagueGameLink($event, $subevent_id),
                        ];
                    }
                }
                if ($structure !== 'Single Elimination') {
                    $dropLink = 'report.php?mode=drop_form&event=' . rawurlencode($event->name);
                }
            } else {
                $alreadyRegistered = $player->name && Entry::playerRegistered($event->id, $player->name);
                if ($event->late_entry_limit > 0 && $event->late_entry_limit >= $event->current_round && !$alreadyRegistered) {
                    if ($isLeague) {
                        $joinLinkText = 'Join League';
                    } else {
                        $joinLinkText = 'Submit Late Entry';
                    }
                    $joinLink = 'prereg.php?action=reg&event=' . rawurlencode($event->name);
                }
            }
            $eventInfo[] = [
                'eventLink' => $eventLink,
                'eventName' => $eventName,
                'showDiscordRoom' => $showDiscordRoom,
                'discordChannelName' => $discordChannelName,
                'discordGuildName' => $discordGuildName,
                'showMtgoRoom' => $showMtgoRoom,
                'mtgoRoom' => $mtgoRoom,
                'standingsLink' => $standingsLink,
                'dropLink' => $dropLink,
                'joinLink' => $joinLink,
                'joinLinkText' => $joinLinkText,
                'createDeckLink' => $createDeckLink,
                'showCreateDeckLink' => (bool) $createDeckLink,
            ];
        }
        return ['events' => $eventInfo, 'leagueMatches' => $leagueMatches];
    }
}

function reportLeagueGameLink(Event $event, ?int $subeventId): string
{
    $eventName = $event->name ?? '';
    $round = (string) ($event->current_round ?? '');
    $subeventId = (string) ($subeventId ?? '');
    return 'report.php?mode=submit_league_result&event=' . rawurlencode($eventName) . '&round=' . rawurlencode($round) . '&subevent=' . rawurlencode($subeventId);
}
