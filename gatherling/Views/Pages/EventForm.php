<?php

declare(strict_types=1);

namespace Gatherling\Views\Pages;

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Views\Components\ClientDropMenu;
use Gatherling\Views\Components\TextInput;
use Gatherling\Views\Components\MonthDropMenu;
use Gatherling\Views\Components\NumDropMenu;
use Gatherling\Views\Components\SelectInput;
use Gatherling\Views\Components\StringField;
use Gatherling\Views\Components\CheckboxInput;
use Gatherling\Views\Components\RoundDropMenu;
use Gatherling\Views\Components\FormatDropMenu;
use Gatherling\Views\Components\SeasonDropMenu;
use Gatherling\Views\Components\SeriesDropMenu;
use Gatherling\Views\Components\StructDropMenu;
use Gatherling\Views\Components\TimeDropMenu;
use Gatherling\Views\Components\TrophyField;
use function Gatherling\Helpers\getObjectVarsCamelCase;
use function Safe\preg_match;

class EventForm extends EventFrame
{
    public bool $currentlyEditing;
    /** @var list<array{text: string, link: string}> */
    public array $navLinks;
    public NumDropMenu $yearDropMenu;
    public MonthDropMenu $monthDropMenu;
    public NumDropMenu $dayDropMenu;
    public TimeDropMenu $timeDropMenu;
    public SeriesDropMenu $seriesDropMenu;
    public SeasonDropMenu $seasonDropMenu;
    public NumDropMenu $numberDropMenu;
    public FormatDropMenu $formatDropMenu;
    public SelectInput $kValueDropMenu;
    public StringField $hostField;
    public StringField $cohostField;
    public TextInput $eventThreadUrlField;
    public TextInput $metagameUrlField;
    public TextInput $reportUrlField;
    public NumDropMenu $mainRoundsNumDropMenu;
    public StructDropMenu $mainRoundsStructDropMenu;
    public NumDropMenu $finalRoundsNumDropMenu;
    public StructDropMenu $finalRoundsStructDropMenu;
    public CheckboxInput $preregistrationAllowedCheckbox;
    public TextInput $lateEntryLimitField;
    public CheckboxInput $playerReportedResultsCheckbox;
    public TextInput $registrationCapField;
    public CheckboxInput $deckPrivacyCheckbox;
    public CheckboxInput $finalsListPrivacyCheckbox;
    public CheckboxInput $playerReportedDrawsCheckbox;
    public CheckboxInput $privateEventCheckbox;
    public ClientDropMenu $clientDropMenu;
    public ?CheckboxInput $finalizeEventCheckbox;
    public ?CheckboxInput $eventActiveCheckbox;
    public ?RoundDropMenu $currentRoundDropMenu;
    public ?TrophyField $trophyField;
    public bool $showCreateNextEvent;
    public bool $showCreateNextSeason;

    public function __construct(Event $event, bool $edit)
    {
        parent::__construct($event);
        if ($event->start != null) {
            $date = $event->start;
            preg_match('/([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+):.*/', $date, $datearr);
            $year = (int) $datearr[1];
            $month = (int) $datearr[2];
            $day = (int) $datearr[3];
            $hour = (int) $datearr[4];
            $minutes = (int) $datearr[5];
        } else {
            $year = (int) date('Y', time());
            $month = (int) date('n', time());
            $day = (int) date('j', time());
            $hour = (int) date('H', time());
            $minutes = (int) date('i', time());
        }

        $navLinks = [];
        $prevEvent = $event->findPrev();
        if ($prevEvent) {
            $navLinks[] = [
                'link' => 'event.php?name=' . rawurlencode($prevEvent->name),
                'text' => 'Previous',
            ];
        }
        $nextEvent = $event->findNext();
        if ($nextEvent) {
            $navLinks[] = [
                'link' => 'event.php?name=' . rawurlencode($nextEvent->name),
                'text' => 'Next',
            ];
        }
        $yearDropMenu = new NumDropMenu('year', '- Year -', (int) date('Y') + 1, $year, 2011);
        $monthDropMenu = new MonthDropMenu($month);
        $dayDropMenu = new NumDropMenu('day', '- Day- ', 31, $day, 1);
        $timeDropMenu = new TimeDropMenu('hour', $hour, $minutes);

        $seriesList = Player::getSessionPlayer()?->organizersSeries() ?? [];
        if ($event->series) {
            $seriesList[] = $event->series;
        }
        $seriesList = array_unique($seriesList);
        $seriesDropMenu = new SeriesDropMenu($event->series, '- Series -', $seriesList);

        $seasonDropMenu = new SeasonDropMenu($event->season);
        $numberDropMenu = new NumDropMenu('number', '- Event Number -', Event::largestEventNum() + 5, $event->number, 0, 'Custom');
        $formatDropMenu = new FormatDropMenu($event->format);

        $kValueDropMenu = kValueSelectInput($event->kvalue);
        $hostField = new StringField('host', $event->host, 20);
        $cohostField = new StringField('cohost', $event->cohost, 20);
        $eventThreadUrlField = new TextInput('Event Thread URL', 'threadurl', $event->threadurl, 60);
        $metagameUrlField = new TextInput('Metagame URL', 'metaurl', $event->metaurl, 60);
        $reportUrlField = new TextInput('Report URL', 'reporturl', $event->reporturl, 60);
        $mainRoundsNumDropMenu = new NumDropMenu('mainrounds', '- No. of Rounds -', 10, $event->mainrounds, 1);
        $mainRoundsStructDropMenu = new StructDropMenu('mainstruct', $event->mainstruct);
        $finalRoundsNumDropMenu = new NumDropMenu('finalrounds', '- No. of Rounds -', 10, $event->finalrounds, 0);
        $finalRoundsStructDropMenu = new StructDropMenu('finalstruct', $event->finalstruct);
        $preregistrationAllowedCheckbox = new CheckboxInput('Allow Pre-Registration', 'prereg_allowed', (bool) $event->prereg_allowed, null);
        $lateEntryLimitField = new TextInput('Late Entry Limit', 'late_entry_limit', $event->late_entry_limit, 4, 'The event host may still add players after this round.');
        $playerReportedResultsCheckbox = new CheckboxInput('Allow Players to Report Results', 'player_reportable', (bool) $event->player_reportable);
        $registrationCapField = new TextInput('Player initiatied registration cap', 'prereg_cap', $event->prereg_cap, 4, 'The event host may still add players beyond this limit. 0 is disabled.', null);
        $deckPrivacyCheckbox = new CheckboxInput('Deck List Privacy', 'private_decks', (bool) $event->private_decks);
        $finalsListPrivacyCheckbox = new CheckboxInput('Finals List Privacy', 'private_finals', (bool) $event->private_finals);
        $playerReportedDrawsCheckbox = new CheckboxInput('Allow Player Reported Draws', 'player_reported_draws', (bool) $event->player_reported_draws, 'This allows players to report a draw result for matches.');
        $privateEventCheckbox = new CheckboxInput('Private Event', 'private', (bool) $event->private, 'This event is invisible to non-participants');
        $clientDropMenu = new ClientDropMenu('client', $event->client);

        $finalizeEventCheckbox = $eventActiveCheckbox = $currentRoundDropMenu = $trophyField = null;
        $showCreateNextEvent = $showCreateNextSeason = false;
        if ($edit) {
            $finalizeEventCheckbox = new CheckboxInput('Finalize Event', 'finalized', (bool) $event->finalized);
            $eventActiveCheckbox = new CheckboxInput('Event Active', 'active', (bool) $event->active);
            $currentRoundDropMenu = new RoundDropMenu($event, $event->current_round, 0);
            $trophyField = new TrophyField($event);
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

function kValueSelectInput(int $kvalue): SelectInput
{
    /** @var array<string, string> */
    $names = [
        '' => '- K-Value -',
        '8' => 'Casual (Alt Event)',
        '16' => 'Regular (less than 24 players)',
        '24' => 'Large (24 or more players)',
        '32' => 'Championship',
    ];
    return new SelectInput('K-Value', 'kvalue', $names, $kvalue);
}
