<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Data\DB;
use Gatherling\Models\Entry;
use Gatherling\Models\Event;
use Gatherling\Models\Format;
use Gatherling\Models\Player;
use Gatherling\Models\MetaDto;
use Gatherling\Models\MetaColorsDto;

use function Gatherling\Helpers\session;

class FullMetagame extends Component
{
    public bool $decklistsAreVisible;
    /** @var array<int, array{numPlayers: int, manaSrc: string, entries: array<int, array{playerLink: PlayerLink, record: string, tribe: string|null, medal: string|null, deckName: string, deckLink: string, archetype: string}>}> */
    public array $meta;
    /** @var array<int, array{playerLink: PlayerLink, record: string, tribe: string|null}> */
    public array $players;
    public bool $showTribes = false;
    public EventStandings $eventStandings;

    public function __construct(Event $event)
    {
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
        $result = DB::select('SELECT colors, COUNT(player) AS cnt FROM meta GROUP BY(colors)', MetaColorsDto::class);
        $sql = 'UPDATE meta SET srtordr = :cnt WHERE colors = :colors';
        foreach ($result as $row) {
            DB::execute($sql, (array) $row);
        }
        $sql = '
            SELECT
                id, player, deckname, archetype, colors, medal, srtordr
            FROM
                meta
            ORDER BY
                srtordr DESC, colors, medal, player';
        $result = DB::select($sql, MetaDto::class);
        $this->decklistsAreVisible = $event->decklistsVisible();
        $this->meta = $this->players = [];
        $color = null;
        $idx = -1;
        foreach ($result as $row) {
            $player = new Player($row->player);
            $entry = new Entry($event->id, $player->name);
            $info = [
                'playerLink' => new PlayerLink($player),
                'record' => $entry->recordString(),
                'tribe' => $this->showTribes ? $entry->deck->tribe ?? null : null,
            ];
            if ($this->decklistsAreVisible) {
                if ($color !== $row->colors) {
                    $idx++;
                    $color = $row->colors;
                    $this->meta[] = [
                        'numPlayers' => $row->srtordr,
                        'manaSrc' => 'styles/images/mana' . rawurlencode($row->colors) . '.png',
                        'entries' => [],
                    ];
                }
                // puts medal next to name of person who won it
                $info['medal'] = $row->medal == 'z' ? '' : $row->medal;
                $info['deckName'] = $row->deckname;
                $info['deckLink'] = 'deck.php?mode=view&id=' . rawurlencode((string) $row->id);
                $info['archetype'] = $row->archetype;
                $this->meta[$idx]['entries'][] = $info;
            } else {
                $this->players[] = $info;
            }
        }
        if ($event->active || $event->finalized) {
            $this->eventStandings = new EventStandings($event->name, session()->optionalString('username'));
        }
    }
}
