<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Views\Components\TextInput;
use Gatherling\Views\Components\NumDropMenu;
use Gatherling\Views\Components\StringField;
use Gatherling\Views\Components\RoundDropMenu;
use Gatherling\Views\Components\FormatDropMenu;
use Gatherling\Views\Components\SeasonDropMenu;
use Gatherling\Views\Components\SeriesDropMenu;

class EventForm extends EventFrame
{
    public bool $currentlyEditing;
    /** @var list<array{text: string, link: string}> */
    public array $navLinks;
    public NumDropMenu $yearDropMenu;
    /** @var array<string, mixed> */
    public array $monthDropMenu;
    public NumDropMenu $dayDropMenu;
    /** @var array<string, mixed> */
    public array $timeDropMenu;
    public SeriesDropMenu $seriesDropMenu;
    public SeasonDropMenu $seasonDropMenu;
    public NumDropMenu $numberDropMenu;
    public FormatDropMenu $formatDropMenu;
    /** @var array<string, mixed> */
    public array $kValueDropMenu;
    public StringField $hostField;
    public StringField $cohostField;
    public TextInput $eventThreadUrlField;
    public TextInput $metagameUrlField;
    public TextInput $reportUrlField;
    public NumDropMenu $mainRoundsNumDropMenu;
    /** @var array<string, mixed> */
    public array $mainRoundsStructDropMenu;
    public NumDropMenu $finalRoundsNumDropMenu;
    /** @var array<string, mixed> */
    public array $finalRoundsStructDropMenu;
    /** @var array<string, mixed> */
    public array $preregistrationAllowedCheckbox;
    public TextInput $lateEntryLimitField;
    /** @var array<string, mixed> */
    public array $playerReportedResultsCheckbox;
    public TextInput $registrationCapField;
    /** @var array<string, mixed> */
    public array $deckPrivacyCheckbox;
    /** @var array<string, mixed> */
    public array $finalsListPrivacyCheckbox;
    /** @var array<string, mixed> */
    public array $playerReportedDrawsCheckbox;
    /** @var array<string, mixed> */
    public array $privateEventCheckbox;
    /** @var array<string, mixed> */
    public array $clientDropMenu;
    /** @var array<string, mixed> */
    public ?array $finalizeEventCheckbox;
    /** @var array<string, mixed> */
    public ?array $eventActiveCheckbox;
    public ?RoundDropMenu $currentRoundDropMenu;
    /** @var ?array<string, mixed> */
    public ?array $trophyField;
    public bool $showCreateNextEvent;
    public bool $showCreateNextSeason;

    public function __construct(Event $event, bool $edit)
    {
        parent::__construct($event);
        if ($event->start != null) {
            $date = $event->start;
            preg_match('/([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+):.*/', $date, $datearr);
            $year = $datearr[1];
            $month = $datearr[2];
            $day = $datearr[3];
            $hour = $datearr[4];
            $minutes = $datearr[5];
        } else {
            $year = date('Y', time());
            $month = date('n', time());
            $day = date('j', time());
            $hour = date('H', time());
            $minutes = date('i', time());
        }

        $navLinks = [];
        $prevEvent = $event->findPrev();
        if ($prevEvent) {
            $navLinks[] = $prevEvent->makeLinkArgs('Previous');
        }
        $nextEvent = $event->findNext();
        if ($nextEvent) {
            $navLinks[] = $nextEvent->makeLinkArgs('Next');
        }
        $yearDropMenu = new NumDropMenu('year', '- Year -', (int) date('Y') + 1, $year, 2011);
        $monthDropMenu = monthDropMenuArgs($month);
        $dayDropMenu = new NumDropMenu('day', '- Day- ', 31, $day, 1);
        $timeDropMenu = timeDropMenuArgs($hour, $minutes);

        $seriesList = Player::getSessionPlayer()->organizersSeries();
        if ($event->series) {
            $seriesList[] = $event->series;
        }
        $seriesList = array_unique($seriesList);
        $seriesDropMenu = new SeriesDropMenu($event->series, '- Series -', $seriesList);

        $seasonDropMenu = new SeasonDropMenu($event->season);
        $numberDropMenu = new NumDropMenu('number', '- Event Number -', Event::largestEventNum() + 5, $event->number, 0, 'Custom');
        $formatDropMenu = new FormatDropMenu($event->format);

        if (is_null($event->kvalue)) {
            $event->kvalue = 16;
        }
        $kValueDropMenu = kValueSelectInput($event->kvalue);
        $hostField = new StringField('host', $event->host, 20);
        $cohostField = new StringField('cohost', $event->cohost, 20);
        $eventThreadUrlField = new TextInput('Event Thread URL', 'threadurl', $event->threadurl, 60);
        $metagameUrlField = new TextInput('Metagame URL', 'metaurl', $event->metaurl, 60);
        $reportUrlField = new TextInput('Report URL', 'reporturl', $event->reporturl, 60);
        $mainRoundsNumDropMenu = new NumDropMenu('mainrounds', '- No. of Rounds -', 10, $event->mainrounds, 1);
        $mainRoundsStructDropMenu = structDropMenuArgs('mainstruct', $event->mainstruct);
        $finalRoundsNumDropMenu = new NumDropMenu('finalrounds', '- No. of Rounds -', 10, $event->finalrounds, 0);
        $finalRoundsStructDropMenu = structDropMenuArgs('finalstruct', $event->finalstruct);
        $preregistrationAllowedCheckbox = checkboxInputArgs('Allow Pre-Registration', 'prereg_allowed', (bool) $event->prereg_allowed, null);
        $lateEntryLimitField = new TextInput('Late Entry Limit', 'late_entry_limit', $event->late_entry_limit, 4, 'The event host may still add players after this round.');
        $playerReportedResultsCheckbox = checkboxInputArgs('Allow Players to Report Results', 'player_reportable', (bool) $event->player_reportable);
        $registrationCapField = new TextInput('Player initiatied registration cap', 'prereg_cap', $event->prereg_cap, 4, 'The event host may still add players beyond this limit. 0 is disabled.', null);
        $deckPrivacyCheckbox = checkboxInputArgs('Deck List Privacy', 'private_decks', (bool) $event->private_decks);
        $finalsListPrivacyCheckbox = checkboxInputArgs('Finals List Privacy', 'private_finals', (bool) $event->private_finals);
        $playerReportedDrawsCheckbox = checkboxInputArgs('Allow Player Reported Draws', 'player_reported_draws', (bool) $event->player_reported_draws, 'This allows players to report a draw result for matches.');
        $privateEventCheckbox = checkboxInputArgs('Private Event', 'private', (bool) $event->private, 'This event is invisible to non-participants');
        $clientDropMenu = clientDropMenuArgs('client', $event->client);

        $finalizeEventCheckbox = $eventActiveCheckbox = $currentRoundDropMenu = $trophyField = null;
        $showCreateNextEvent = $showCreateNextSeason = false;
        if ($edit) {
            $finalizeEventCheckbox = checkboxInputArgs('Finalize Event', 'finalized', (bool) $event->finalized);
            $eventActiveCheckbox = checkboxInputArgs('Event Active', 'active', (bool) $event->active);
            $currentRoundDropMenu = new RoundDropMenu($event, $event->current_round);
            $trophyField = trophyFieldArgs($event);
            $nextEventName = sprintf('%s %d.%02d', $event->series, $event->season, $event->number + 1);
            $nextSeasonName = sprintf('%s %d.%02d', $event->series, $event->season + 1, 1);
            $showCreateNextEvent = !Event::exists($nextEventName);
            $showCreateNextSeason = !Event::exists($nextSeasonName);
        }

        $this->currentlyEditing = $edit;
        $this->event = getObjectVarsCamelCase($event);
        $this->navLinks = $navLinks;
        $this->yearDropMenu = $yearDropMenu;
        $this->monthDropMenu = $monthDropMenu;
        $this->dayDropMenu = $dayDropMenu;
        $this->timeDropMenu = $timeDropMenu;
        $this->seriesDropMenu = $seriesDropMenu;
        $this->seasonDropMenu = $seasonDropMenu;
        $this->numberDropMenu = $numberDropMenu;
        $this->formatDropMenu = $formatDropMenu;
        $this->kValueDropMenu = $kValueDropMenu;
        $this->hostField = $hostField;
        $this->cohostField = $cohostField;
        $this->eventThreadUrlField = $eventThreadUrlField;
        $this->metagameUrlField = $metagameUrlField;
        $this->reportUrlField = $reportUrlField;
        $this->mainRoundsNumDropMenu = $mainRoundsNumDropMenu;
        $this->mainRoundsStructDropMenu = $mainRoundsStructDropMenu;
        $this->finalRoundsNumDropMenu = $finalRoundsNumDropMenu;
        $this->finalRoundsStructDropMenu = $finalRoundsStructDropMenu;
        $this->preregistrationAllowedCheckbox = $preregistrationAllowedCheckbox;
        $this->lateEntryLimitField = $lateEntryLimitField;
        $this->playerReportedResultsCheckbox = $playerReportedResultsCheckbox;
        $this->registrationCapField = $registrationCapField;
        $this->deckPrivacyCheckbox = $deckPrivacyCheckbox;
        $this->finalsListPrivacyCheckbox = $finalsListPrivacyCheckbox;
        $this->playerReportedDrawsCheckbox = $playerReportedDrawsCheckbox;
        $this->privateEventCheckbox = $privateEventCheckbox;
        $this->clientDropMenu = $clientDropMenu;
        $this->finalizeEventCheckbox = $finalizeEventCheckbox;
        $this->eventActiveCheckbox = $eventActiveCheckbox;
        $this->currentRoundDropMenu = $currentRoundDropMenu;
        $this->trophyField = $trophyField;
        $this->showCreateNextEvent = $showCreateNextEvent;
        $this->showCreateNextSeason = $showCreateNextSeason;
    }
}

/** @return array{id: string, name: string, default: string, options: array<int, array{isSelected: bool, value: int, text: string}>} */
function clientDropMenuArgs(string $field, int $def): array
{
    $clients = [
        1 => 'MTGO',
        2 => 'Arena',
        3 => 'Other',
    ];
    $options = [];
    foreach ($clients as $value => $text) {
        $options[] = [
            'isSelected' => $def == $value,
            'value'      => $value,
            'text'       => $text,
        ];
    }

    return [
        'id'      => $field,
        'name'    => $field,
        'default' => '- Client -',
        'options' => $options,
    ];
}

/** @return array<string, mixed> */
function kValueSelectInput(int $kvalue): array
{
    $names = [
        '' => '- K-Value -',
        8 => 'Casual (Alt Event)',
        16 => 'Regular (less than 24 players)',
        24 => 'Large (24 or more players)',
        32 => 'Championship',
    ];
    return selectInputArgs('K-Value', 'kvalue', $names, $kvalue);
}

/** @return array{name: string, default: string, options: array<int, array{isSelected: bool, value: int, text: string}>} */
function monthDropMenuArgs(string|int $month): array
{
    if (strcmp($month, '') == 0) {
        $month = -1;
    }
    $names = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];
    $options = [];
    for ($m = 1; $m <= 12; $m++) {
        $options[] = [
            'isSelected' => $month == $m,
            'value'      => $m,
            'text'       => $names[$m - 1],
        ];
    }

    return [
        'name'    => 'month',
        'default' => '- Month -',
        'options' => $options,
    ];
}

/** @return array{name: string, default: string, options: list<array{isSelected: bool, value: string, text: string}>} */
function structDropMenuArgs(string $field, string $def): array
{
    $names = ['Swiss', 'Single Elimination', 'League', 'League Match'];
    if ($def == 'Swiss (Blossom)') {
        $def = 'Swiss';
    }
    if ($def == 'Round Robin') {
        $names[] = 'Round Robin';
    }
    $options = [];
    foreach ($names as $name) {
        $options[] = [
            'value'      => $name,
            'text'       => $name,
            'isSelected' => strcmp($def, $name) == 0,
        ];
    }

    return [
        'name'    => $field,
        'default' => '- Structure -',
        'options' => $options,
    ];
}

/** @return array{hasTrophy: bool, trophySrc: string} */
function trophyFieldArgs(Event $event): array
{
    return [
        'hasTrophy' => (bool) $event->hastrophy,
        'trophySrc' => 'displayTrophy.php?event=' . rawurlencode($event->name),
    ];
}
