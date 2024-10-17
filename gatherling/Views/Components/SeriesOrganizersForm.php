<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Player;
use Gatherling\Models\Series;

class SeriesOrganizersForm extends Component
{
    public string $seriesName;
    /** @var array<array{name: string, isDisabled: bool}> */
    public array $organizers;

    public function __construct(Series $series)
    {
        parent::__construct('partials/seriesOrganizersForm');
        $player = Player::loginName() ? new Player(Player::loginName()) : null;
        $this->seriesName = $series->name;
        foreach ($series->organizers as $organizer) {
            $this->organizers[] = [
                'name' => $organizer,
                'isDisabled' => $player && ($organizer == $player->loginName() && !$player->isSuper()),
            ];
        }
    }
}
