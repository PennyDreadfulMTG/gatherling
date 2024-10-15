<?php

declare(strict_types=1);

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;
use Gatherling\Views\TemplateHelper;
use Gatherling\Views\Components\Submit;
use Gatherling\Views\Components\TextInput;
use Gatherling\Views\Components\CheckboxInput;
use Gatherling\Views\Components\TimeZoneDropMenu;

require_once 'lib.php';

function textInput(string $label, string $name, mixed $value = '', int $size = 0, ?string $reminderText = null, ?string $id = null): string
{
    return (new TextInput($label, $name, $value, $size, $reminderText, $id))->render();
}

function checkboxInput(string $label, string $name, bool $isChecked = false, ?string $reminderText = null): string
{
    return (new CheckboxInput($label, $name, $isChecked, $reminderText))->render();
}

function print_file_input(string $label, string $name): void
{
    echo "<tr><th><label for='$name'>{$label}</label></th>";
    echo "<td><input type=\"file\" name=\"{$name}\" id='$name' /></td></tr>";
}

function submit(string $label, string $name = 'action'): string
{
    return (new Submit($label, $name))->render();
}

/**
 * @param array<string, string> $options
 * @return array{id: string, name: string, options: list<array{isSelected: bool, value: string, text: string}>}
 */
function selectArgs(string $name, array $options = [], mixed $selected = null, ?string $id = null): array
{
    if (is_null($id)) {
        $id = $name;
    }
    $opts = [];
    foreach ($options as $option => $text) {
        $opts[] = [
            'isSelected' => !is_null($selected) && $selected == $option,
            'value'      => $option,
            'text'       => $text,
        ];
    }

    return [
        'id'      => $id,
        'name'    => $name,
        'options' => $opts,
    ];
}

/** @param array<string, string> $options */
function selectInput(string $label, string $name, ?array $options, mixed $selected = null, ?string $id = null): string
{
    $args = selectInputArgs($label, $name, $options, $selected, $id);

    return TemplateHelper::render('partials/selectInput', $args);
}

/**
 * @param array<string, string> $options
 * @return array{id: string, name: string, label: string, select: array{id: string, name: string, options: list<array{isSelected: bool, value: string, text: string}>}}
 */
function selectInputArgs(string $label, string $name, ?array $options, mixed $selected = null, ?string $id = null): array
{
    if (is_null($id)) {
        $id = $name;
    }

    return [
        'id'     => $id,
        'name'   => $name,
        'label'  => $label,
        'select' => selectArgs($name, $options, $selected, $id),
    ];
}

function leagueOpponentDropMenu(string $eventname, int $round, Player $player, int $subevent): void
{
    $event = new Event($eventname);
    $player_standings = new Standings($eventname, $player->name);
    $playernames = $player_standings->getAvailableLeagueOpponents($subevent, $round, $event->leagueLength());

    echo '<select class="inputbox" name="opponent"> Opponent';

    if (count($playernames)) {
        foreach ($playernames as $playername) {
            $oppplayer = new Player($playername);
            echo "<option value=\"{$playername}\">{$oppplayer->gameName($event->client, false)}</option>";
        }
    } else {
        echo '<option value="">-No Available Opponents-</option>';
    }
    echo '</select>';
}
