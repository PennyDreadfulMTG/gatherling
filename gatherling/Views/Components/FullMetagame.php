<?php

namespace Gatherling\Views\Components;

use Gatherling\Data\DB;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Format;
use Gatherling\Models\Player;

class FullMetagame extends Component
{
    public bool $decklistsAreVisible;
    public array $meta;
    public array $players;
    public bool $showTribes = false;
    public EventStandings $eventStandings;

    public function __construct(Event $event)
    {
        parent::__construct('partials/fullMetagame');
        $decks = $event->getDecks();
        $players = [];
        $format = new Format($event->format);
        $this->showTribes = $format->tribal && $event->current_round > 1;
        foreach ($decks as $deck) {
            $info = [
                'player' => $deck->playername,
                'deckname' => $deck->name,
                'archetype' => $deck->archetype,
                'medal' => $deck->medal,
                'id' => $deck->id,
            ];
            $info['colors'] = $deck->colorStr();
            if ($info['medal'] == 'dot') {
                $info['medal'] = 'z';
            }
            $players[] = $info;
        }
        $sql = '
            CREATE TEMPORARY TABLE meta
                (
                    player VARCHAR(40),
                    deckname VARCHAR(120),
                    archetype VARCHAR(20),
                    colors VARCHAR(10),
                    medal VARCHAR(10),
                    id BIGINT UNSIGNED,
                    srtordr TINYINT UNSIGNED DEFAULT 0
                )';
        DB::execute($sql);
        $sql = '
            INSERT INTO meta
                (player, deckname, archetype, colors, medal, id)
            VALUES
                (:player, :deckname, :archetype, :colors, :medal, :id)';
        foreach ($players as $player) {
            DB::execute($sql, $player);
        }
        $result = DB::select('SELECT colors, COUNT(player) AS cnt FROM meta GROUP BY(colors)');
        $sql = 'UPDATE meta SET srtordr = :cnt WHERE colors = :colors';
        foreach ($result as $row) {
            DB::execute($sql, $row);
        }
        $sql = '
            SELECT
                player, deckname, archetype, colors, medal, id, srtordr
            FROM
                meta
            ORDER BY
                srtordr DESC, colors, medal, player';
        $result = DB::select($sql);
        $this->decklistsAreVisible = $event->decklistsVisible();
        $this->meta = $this->players = [];
        $color = null;
        $idx = -1;
        foreach ($result as $row) {
            $player = new Player($row['player']);
            $entry = new Entry($event->id, $player->name);
            $info = [
                'playerLink' => new PlayerLink($player),
                'record' => $entry->recordString(),
                'tribe' => $this->showTribes ? $entry->deck->tribe ?? null : null,
            ];
            if ($this->decklistsAreVisible) {
                if ($color !== $row['colors']) {
                    $idx++;
                    $color = $row['colors'];
                    $this->meta[] = [
                        'numPlayers' => $row['srtordr'],
                        'manaSrc' => 'styles/images/mana' . rawurlencode($row['colors']) . '.png',
                        'entries' => [],
                    ];
                }
                if ($row['medal'] != 'z') {  // puts medal next to name of person who won it
                    $info['medal'] = $row['medal'];
                }
                $info['deckName'] = $row['deckname'];
                $info['archetype'] = $row['archetype'];
                $this->meta[$idx]['entries'][] = $info;
            } else {
                $this->players[] = $info;
            }
        }
        if ($event->active || $event->finalized) {
            $this->eventStandings = new EventStandings($event->name, $_SESSION['username'] ?? null);
        }
    }
}
