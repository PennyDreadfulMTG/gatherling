<?php

use Gatherling\Event;
use Gatherling\Player;
use Gatherling\Standings;

require_once 'lib.php';

function textInput(string $label, string $name, mixed $value = '', int $size = 0, ?string $reminderText = null, ?string $id = null): string
{
    $args = textInputArgs($label, $name, $value, $size, $reminderText, $id);

    return renderTemplate('partials/textInput', $args);
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

    return renderTemplate('partials/checkboxInput', $args);
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

function select(string $name, array $options = [], mixed $selected = null, ?string $id = null): string
{
    $args = selectArgs($name, $options, $selected, $id);

    return renderTemplate('partials/select', $args);
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

    return renderTemplate('partials/selectInput', $args);
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

    return renderTemplate('partials/stringField', $args);
}

function stringFieldArgs(string $field, mixed $def, int $len): array
{
    return [
        'field' => $field,
        'def'   => $def,
        'len'   => $len,
    ];
}

function timeZoneDropMenu($selected = null)
{
    $timezones = [];
    $timezones['-12'] = '[UTC - 12] Baker Island Time';
    $timezones['-11'] = '[UTC - 11] Niue Time, Samoa Standard Time';
    $timezones['-10'] = '[UTC - 10] Hawaii-Aleutian Standard Time, Cook Island Time';
    $timezones['-9.5'] = '[UTC - 9:30] Marquesas Islands Time';
    $timezones['-9'] = '[UTC - 9] Alaska Standard Time, Gambier Island Time';
    $timezones['-8'] = '[UTC - 8] Pacific Standard Time</option>';
    $timezones['-7'] = '[UTC - 7] Mountain Standard Time</option>';
    $timezones['-6'] = '[UTC - 6] Central Standard Time</option>';
    $timezones['-5'] = '[UTC - 5] Eastern Standard Time (Gatherling.com Default Time';
    $timezones['-4.5'] = '[UTC - 4:30] Venezuelan Standard Time';
    $timezones['-4'] = '[UTC - 4] Atlantic Standard Time';
    $timezones['-3.5'] = '[UTC - 3:30] Newfoundland Standard Time';
    $timezones['-3'] = '[UTC - 3] Amazon Standard Time, Central Greenland Time';
    $timezones['-2'] = '[UTC - 2] Fernando de Noronha Time, South Georgia &amp; the South Sandwich Islands Time';
    $timezones['-1'] = '[UTC - 1] Azores Standard Time, Cape Verde Time, Eastern Greenland Time';
    $timezones['0'] = '[UTC] Western European Time, Greenwich Mean Time';
    $timezones['1'] = '[UTC + 1] Central European Time, West African Time';
    $timezones['2'] = '[UTC + 2] Eastern European Time, Central African Time';
    $timezones['3'] = '[UTC + 3] Moscow Standard Time, Eastern African Time';
    $timezones['3.5'] = '[UTC + 3:30] Iran Standard Time';
    $timezones['4'] = '[UTC + 4] Gulf Standard Time, Samara Standard Time';
    $timezones['4.5'] = '[UTC + 4:30] Afghanistan Time';
    $timezones['5'] = '[UTC + 5] Pakistan Standard Time, Yekaterinburg Standard Time';
    $timezones['5.5'] = '[UTC + 5:30] Indian Standard Time, Sri Lanka Time';
    $timezones['5.75'] = '[UTC + 5:45] Nepal Time';
    $timezones['6'] = '[UTC + 6] Bangladesh Time, Bhutan Time, Novosibirsk Standard Time';
    $timezones['6.5'] = '[UTC + 6:30] Cocos Islands Time, Myanmar Time';
    $timezones['7'] = '[UTC + 7] Indochina Time, Krasnoyarsk Standard Time';
    $timezones['8'] = '[UTC + 8] Chinese Standard Time, Australian Western Standard Time, Irkutsk Standard Time';
    $timezones['8.75'] = '[UTC + 8:45] Southeastern Western Australia Standard Time';
    $timezones['9'] = '[UTC + 9] Japan Standard Time, Korea Standard Time, Chita Standard Time';
    $timezones['9.5'] = '[UTC + 9:30] Australian Central Standard Time';
    $timezones['10'] = '[UTC + 10] Australian Eastern Standard Time, Vladivostok Standard Time';
    $timezones['10.5'] = '[UTC + 10:30] Lord Howe Standard Time';
    $timezones['11'] = '[UTC + 11] Solomon Island Time, Magadan Standard Time';
    $timezones['11.5'] = '[UTC + 11:30] Norfolk Island Time';
    $timezones['12'] = '[UTC + 12] New Zealand Time, Fiji Time, Kamchatka Standard Time';
    $timezones['12.75'] = '[UTC + 12:45] Chatham Islands Time';
    $timezones['13'] = '[UTC + 13] Tonga Time, Phoenix Islands Time';
    $timezones['14'] = '[UTC + 14] Line Island Time';
    echo select('timezone', $timezones, $selected);
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
