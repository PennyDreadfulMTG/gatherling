<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

use Gatherling\Models\Series;

class PointsForm extends Component
{
    public string $seriesName;
    public SeasonDropMenu $seasonDropMenu;
    /** @var list<PointsRule> */
    public array $rules;

    public function __construct(public Series $series, public int $season)
    {
        parent::__construct('partials/pointsForm');
        $this->seriesName = $series->name;
        $this->seasonDropMenu = new SeasonDropMenu($season);
        $seasonRules = $series->getSeasonRules($season);
        $this->rules = [
            new PointsRule('First Place', 'first_pts', $seasonRules),
            new PointsRule('Second Place', 'second_pts', $seasonRules),
            new PointsRule('Top 4', 'semi_pts', $seasonRules),
            new PointsRule('Top 8', 'quarter_pts', $seasonRules),
            new PointsRule('Participating', 'participation_pts', $seasonRules),
            new PointsRule('Each round played', 'rounds_pts', $seasonRules),
            new PointsRule('Match win', 'win_pts', $seasonRules),
            new PointsRule('Match loss', 'loss_pts', $seasonRules),
            new PointsRule('Round bye', 'bye_pts', $seasonRules),
            new PointsRule('Posting a decklist', 'decklist_pts', $seasonRules),
            new PointsRule('Require decklist for points', 'must_decklist', $seasonRules, 'checkbox'),
            new PointsRule('WORLDS Cutoff (players)', 'cutoff_ord', $seasonRules),
            new PointsRule('Master Document Location', 'master_link', $seasonRules, 'text', 50),
            new PointsRule('Season Format', 'format', $seasonRules, 'format'),
        ];
    }
}
