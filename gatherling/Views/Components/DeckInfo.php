<?php

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Format;
use InvalidArgumentException;

class DeckInfo extends Component
{
    public string $formatName;
    public string $colorImagesSafe;
    public string $tribe = '';
    public string $archetype;

    public function __construct(Event $event, Deck $deck)
    {
        parent::__construct('partials/deckInfo');

        if (!$event->format) {
            throw new InvalidArgumentException('Event format is required');
        }

        $this->formatName = $event->format;
        $this->colorImagesSafe = $deck->getColorImages();

        $format = new Format($event->format);
        if ($format->tribal > 0) {
            $this->tribe = $deck->tribe ?? '';
        }
        $this->archetype = $deck->archetype ?? '';
    }
}
