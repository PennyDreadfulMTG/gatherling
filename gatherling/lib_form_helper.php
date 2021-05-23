<?php

use Gatherling\Standings;

require_once 'lib.php';

function print_text_input($label, $name, $value = '', $len = 0, $reminder_text = null, $id = null, $advanced = false)
{
    if (is_null($id)) {
        $id = $name;
    }
    if (!isset($value)) {
        $value = '';
    }
    $class = '';
    if ($advanced) {
        $class = 'advanced';
    }
    echo "<tr class=\"$class\"><th><label for='$id'>{$label}</label></th>";
    echo "<td><input class=\"inputbox\" type=\"text\" name=\"{$name}\" id='$id' value=\"{$value}\"";
    if ($len > 0) {
        echo " size=\"$len\"";
    }
    echo ' /> ';
    if ($reminder_text) {
        echo $reminder_text;
    }
    echo "</td></tr>\n";
}

function print_checkbox_input($label, $name, $checked = false, $reminder_text = null, $advanced = false)
{
    echo "<tr><th><label for='$name'>{$label}</label></th>";
    echo "<td><input type=\"checkbox\" name=\"{$name}\" id='$name' value=\"1\"";
    if ($checked) {
        echo ' checked="yes"';
    }
    echo ' /> ';
    if ($reminder_text) {
        echo $reminder_text;
    }
    echo "</td></tr>\n";
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

function print_select($name, $options = [], $selected = null, $id = null)
{
    if (is_null($id)) {
        $id = $name;
    }
    echo "<select class='inputbox' name=\"{$name}\" id='$id'>";
    if (!is_assoc($options)) {
        $new_options = [];
        foreach ($options as $option) {
            $new_options[$option] = $option;
        }
    }
    foreach ($options as $option => $text) {
        $setxt = '';
        if (!is_null($selected) && $selected == $option) {
            $setxt = ' selected';
        }
        echo "<option value=\"{$option}\"{$setxt}>{$text}</option>";
    }
    echo '</select>';
}

function print_select_input($label, $name, $options, $selected = null, $id = null)
{
    if (is_null($id)) {
        $id = $name;
    }
    echo "<tr><th><label for='$id'>{$label}</label></th><td>";
    print_select($name, $options, $selected, $id);
    echo "</td></tr>\n";
}

function stringField($field, $def, $len)
{
    echo "<input class=\"inputbox\" type=\"text\" name=\"$field\" value=\"$def\" size=\"$len\">";
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
    print_select('timezone', $timezones, $selected);
}

/**
 * @param string $event
 * @param string $round
 * @param mixed  $player
 * @param int    $subevent
 *
 * @return void
 */
function leagueOpponentDropMenu($event, $round, $player, $subevent)
{
    $player_standings = new Standings($event, $player->name);
    $playernames = $player_standings->League_getAvailable_Opponents($subevent, $round);

    echo '<select class="inputbox" name="opponent"> Opponent';

    if (count($playernames)) {
        foreach ($playernames as $playername) {
            echo "<option value=\"{$playername}\">{$playername}</option>";
        }
    } else {
        echo '<option value="">-No Available Opponents-</option>';
    }
    echo '</select>';
}
