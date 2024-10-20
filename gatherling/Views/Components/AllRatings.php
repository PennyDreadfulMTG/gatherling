<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Data\Db;
use Gatherling\Models\Entry;
use Gatherling\Models\Player;
use Gatherling\Models\Ratings;
use Gatherling\Models\RatingsDto;
use Gatherling\Views\Components\DropMenu;
use InvalidArgumentException;

class AllRatings extends Component
{
    /** @var array<array-key, array{formatName: string, rating: ?int, record: ?string, min: ?int, max: ?int}> */
    public array $ratingLines = [];
    public DropMenu $formatDropMenu;
    /** @var array<array-key, array{eventName: string, wl: string, medal: ?string, medalSrc: ?string, deckLink: ?DeckLink, preEventRating: float, postEventRating: float}> */
    public array $entries = [];

    public function __construct(Player $player, string $formatName)
    {
        if (!$player->name) {
            throw new InvalidArgumentException('Player name is required');
        }
        $ratings = new Ratings();
        $formatNames = $ratings->ratingNames;
        array_unshift($formatNames, 'Composite');
        array_push($formatNames, 'Other Formats');
        foreach ($formatNames as $ratingFormatName) {
            $rating = $player->getRating($ratingFormatName);
            $record = $player->getRatingRecord($ratingFormatName);
            $min = $player->getMinRating($ratingFormatName);
            if ($min !== null) {
                $max = $player->getMaxRating($ratingFormatName);
            } else {
                $max = null;
            }
            $this->ratingLines[] = [
                'formatName' => $ratingFormatName,
                'rating' => $rating,
                'record' => $record,
                'min' => $min,
                'max' => $max,
            ];
        }

        $options = [];
        foreach ($formatNames as $optionFormatName) {
            $options[] = [
                'value' => $optionFormatName,
                'text' => $optionFormatName,
                'isSelected' => $optionFormatName === $formatName,
            ];
        }
        $this->formatDropMenu = new DropMenu('format', $options);

        $sql = '
            SELECT
                e.name, e.id AS event_id, r.rating, n.medal, n.deck AS deck_id
            FROM
                events e, entries n, ratings r
            WHERE
                r.format = :format
            AND
                r.player = :player
            AND
                e.start = r.updated AND n.player = r.player AND n.event_id = e.id
            ORDER BY
                e.start DESC';
        $ratings = array_reverse(Db::select($sql, RatingsDto::class, ['format' => $formatName, 'player' => $player->name]));

        $prevRating = 1600;
        foreach ($ratings as $rating) {
            $entry = new Entry($rating->event_id, $player->name);
            $wl = $entry->recordString();
            $this->entries[] = [
                'eventName' => $rating->name,
                'wl' => $wl,
                'medal' => $rating->medal,
                'medalSrc' => $rating->medal ? 'styles/images/' . rawurlencode($rating->medal) . '.png' : null,
                'deckLink' => $entry->deck ? new DeckLink($entry->deck) : null,
                'preEventRating' => $prevRating,
                'postEventRating' => $rating->rating,
            ];
            $prevRating = $rating->rating;
        }
        $this->entries = array_reverse($this->entries);
    }
}
