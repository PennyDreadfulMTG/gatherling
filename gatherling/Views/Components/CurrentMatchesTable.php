<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Event;
use Gatherling\Models\Matchup;
use Gatherling\Models\Player;
use InvalidArgumentException;

class CurrentMatchesTable extends Component
{
    /** @var list<array{eventName: string, currentRound: string, isBye: bool, opponentLink?: PlayerLink, isReportSubmitted?: bool, reportLink?: string, reportInChannel?: bool, verificationFailed?: bool, isReported?: bool}> */
    public array $tournamentMatches;

    /**
     * @param list<Matchup> $tournamentMatches
     * @param list<array{eventName: string, matchCount: int, reportLink: string}> $leagueMatches
     */
    public function __construct(Player $player, array $tournamentMatches, public array $leagueMatches)
    {
        $matchInfoList = [];
        foreach ($tournamentMatches as $match) {
            $event = new Event($match->getEventNamebyMatchid());
            $opp = $match->playera;
            $player_number = 'b';
            if ($player->name && $opp && strcasecmp($player->name, $opp) == 0) {
                $opp = $match->playerb;
                $player_number = 'a';
            }
            $matchInfo = [
                'eventName' => $event->name ?? '',
                'currentRound' => ((string) $event->current_round) ?: '',
                'isBye' => $match->result == 'BYE',
            ];

            if (!$matchInfo['isBye']) {
                if (!$opp) {
                    throw new InvalidArgumentException("No opponent for match {$match->id}");
                }
                $oppplayer = new Player($opp);
                $matchInfo['opponentLink'] = new PlayerLink($oppplayer, $event->client);
                if ($match->verification == 'unverified') {
                    if ($player_number == 'b' and ((int) $match->playerb_wins + (int) $match->playerb_losses) > 0) {
                        $matchInfo['isReportSubmitted'] = true;
                    } elseif ($player_number == 'a' and ((int) $match->playera_wins + (int) $match->playera_losses) > 0) {
                        $matchInfo['isReportSubmitted'] = true;
                    } else {
                        if ($match->playerReportableCheck() == true) {
                            $matchInfo['reportLink'] = 'report.php?mode=submit_result&match_id=' . rawurlencode((string) $match->id) . '&player=' . rawurlencode($player_number);
                        } else {
                            $matchInfo['reportInChannel'] = true;
                        }
                    }
                } elseif ($match->verification == 'failed') {
                    $matchInfo['verificationFailed'] = true;
                } else {
                    $matchInfo['isReported'] = true;
                }
                $matchInfoList[] = $matchInfo;
            } elseif (($match->round == $event->current_round) && $event->active) {
                $matchInfoList[] = $matchInfo;
            }
        }
        $this->tournamentMatches = $matchInfoList;
    }
}
