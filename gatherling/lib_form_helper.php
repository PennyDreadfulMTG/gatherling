<?php

declare(strict_types=1);

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;
use Gatherling\Views\TemplateHelper;
use Gatherling\Views\Components\Submit;
use Gatherling\Views\Components\TextInput;
use Gatherling\Views\Components\TimeZoneDropMenu;

require_once 'lib.php';

function textInput(string $label, string $name, mixed $value = '', int $size = 0, ?string $reminderText = null, ?string $id = null): string
{
    return (new TextInput($label, $name, $value, $size, $reminderText, $id))->render();
}

function checkboxInput(string $label, string $name, bool $isChecked = false, ?string $reminderText = null): string
{
    $args = checkboxInputArgs($label, $name, $isChecked, $reminderText);

    return TemplateHelper::render('partials/checkboxInput', $args);
}

/** @return array<string, string|bool|null> */
function checkboxInputArgs(string $label, string $name, bool $isChecked = false, ?string $reminderText = null): array
{
    return [
        'name'         => $name,
        'label'        => $label,
        'isChecked'    => $isChecked,
        'reminderText' => $reminderText,
    ];
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
 * @return array<string, string|bool|null>
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
 * @return array<string, string|array<string, string|bool|null>>
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

function timeZoneDropMenu(?float $selected = null): string
{
    return (new TimeZoneDropMenu($selected))->render();
}

function leagueOpponentDropMenu(string $eventname, int $round, Player $player, int $subevent): void
{
    $event = new Event($eventname);
    $player_standings = new Standings($eventname, $player->name);
    $playernames = $player_standings->League_getAvailable_Opponents($subevent, $round, $event->leagueLength());

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
