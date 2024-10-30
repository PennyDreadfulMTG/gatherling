<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Deck;
use Gatherling\Models\Event;
use Gatherling\Models\Format;
use InvalidArgumentException;

class DeckInfo extends Component
{
    public string $formatName;
    public ColorImages $colorImages;
    public string $tribe = '';
    public string $archetype;

    public function __construct(Event $event, Deck $deck)
    {
        if (!$event->format) {
            throw new InvalidArgumentException('Event format is required');
        }

        $this->formatName = $event->format;
        $this->colorImages = new ColorImages($deck);

        $format = new Format($event->format);
        if ($format->tribal > 0) {
            $this->tribe = $deck->tribe ?? '';
        }
        $this->archetype = $deck->archetype ?? '';
    }
}
