<?php

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Standings;
use Gatherling\Views\TemplateHelper;
use Gatherling\Views\Components\TimeZoneDropMenu;

require_once 'lib.php';

function textInput(string $label, string $name, mixed $value = '', int $size = 0, ?string $reminderText = null, ?string $id = null): string
{
    $args = textInputArgs($label, $name, $value, $size, $reminderText, $id);

    return TemplateHelper::render('partials/textInput', $args);
}

function textInputArgs(string $label, string $name, mixed $value = '', int $size = 0, ?string $reminderText = null, ?string $id = null): array
{
    if (is_null($id)) {
        $id = $name;
    }
    if (!isset($value)) {
        $value = '';
    }

    return [
        'id'           => $id,
        'name'         => $name,
        'label'        => $label,
        'size'         => $size,
        'reminderText' => $reminderText,
        'value'        => $value,
    ];
}

function checkboxInput(string $label, string $name, bool $isChecked = false, ?string $reminderText = null): string
{
    $args = checkboxInputArgs($label, $name, $isChecked, $reminderText);

    return TemplateHelper::render('partials/checkboxInput', $args);
}

function checkboxInputArgs(string $label, string $name, bool $isChecked = false, ?string $reminderText = null): array
{
    return [
        'name'         => $name,
        'label'        => $label,
        'isChecked'    => $isChecked,
        'reminderText' => $reminderText,
    ];
}

function print_password_input($label, $name, $value = '')
{
    echo "<tr><th><label for='$name'>{$label}</label></th>";
    echo "<td><input type=\"password\" name=\"{$name}\" id='$name' value=\"{$value}\" /> </td></tr>";
}

function print_file_input($label, $name)
{
    echo "<tr><th><label for='$name'>{$label}</label></th>";
    echo "<td><input type=\"file\" name=\"{$name}\" id='$name' /></td></tr>";
}

function print_submit($label, $name = 'action')
{
    echo "<tr><td colspan=\"2\" class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" name=\"{$name}\" value=\"{$label}\" /></td></tr>\n";
}

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

function selectInput(string $label, string $name, ?array $options, mixed $selected = null, ?string $id = null): string
{
    $args = selectInputArgs($label, $name, $options, $selected, $id);

    return TemplateHelper::render('partials/selectInput', $args);
}

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

function stringField($field, $def, $len): string
{
    $args = stringFieldArgs($field, $def, $len);

    return TemplateHelper::render('partials/stringField', $args);
}

function stringFieldArgs(string $field, mixed $def, int $len): array
{
    return [
        'field' => $field,
        'def'   => $def,
        'len'   => $len,
    ];
}

function timeZoneDropMenu(?float $selected = null): string
{
    return (new TimeZoneDropMenu($selected))->render();
}

/**
 * @param string $eventname
 * @param string $round
 * @param mixed  $player
 * @param int    $subevent
 *
 * @return void
 */
function leagueOpponentDropMenu($eventname, $round, $player, $subevent)
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
