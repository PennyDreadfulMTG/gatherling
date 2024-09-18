<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class TimeZoneDropMenu extends DropMenu
{
    public function __construct(public ?float $selected = null)
    {
        $options = [
            ['value' => '-12', 'text' => '[UTC - 12] Baker Island Time'],
            ['value' => '-11', 'text' => '[UTC - 11] Niue Time, Samoa Standard Time'],
            ['value' => '-10', 'text' => '[UTC - 10] Hawaii-Aleutian Standard Time, Cook Island Time'],
            ['value' => '-9.5', 'text' => '[UTC - 9:30] Marquesas Islands Time'],
            ['value' => '-9', 'text' => '[UTC - 9] Alaska Standard Time, Gambier Island Time'],
            ['value' => '-8', 'text' => '[UTC - 8] Pacific Standard Time'],
            ['value' => '-7', 'text' => '[UTC - 7] Mountain Standard Time'],
            ['value' => '-6', 'text' => '[UTC - 6] Central Standard Time'],
            ['value' => '-5', 'text' => '[UTC - 5] Eastern Standard Time (Gatherling.com Default Time'],
            ['value' => '-4.5', 'text' => '[UTC - 4:30] Venezuelan Standard Time'],
            ['value' => '-4', 'text' => '[UTC - 4] Atlantic Standard Time'],
            ['value' => '-3.5', 'text' => '[UTC - 3:30] Newfoundland Standard Time'],
            ['value' => '-3', 'text' => '[UTC - 3] Amazon Standard Time, Central Greenland Time'],
            ['value' => '-2', 'text' => '[UTC - 2] Fernando de Noronha Time, South Georgia & the South Sandwich Islands Time'],
            ['value' => '-1', 'text' => '[UTC - 1] Azores Standard Time, Cape Verde Time, Eastern Greenland Time'],
            ['value' => '0', 'text' => '[UTC] Western European Time, Greenwich Mean Time'],
            ['value' => '1', 'text' => '[UTC + 1] Central European Time, West African Time'],
            ['value' => '2', 'text' => '[UTC + 2] Eastern European Time, Central African Time'],
            ['value' => '3', 'text' => '[UTC + 3] Moscow Standard Time, Eastern African Time'],
            ['value' => '3.5', 'text' => '[UTC + 3:30] Iran Standard Time'],
            ['value' => '4', 'text' => '[UTC + 4] Gulf Standard Time, Samara Standard Time'],
            ['value' => '4.5', 'text' => '[UTC + 4:30] Afghanistan Time'],
            ['value' => '5', 'text' => '[UTC + 5] Pakistan Standard Time, Yekaterinburg Standard Time'],
            ['value' => '5.5', 'text' => '[UTC + 5:30] Indian Standard Time, Sri Lanka Time'],
            ['value' => '5.75', 'text' => '[UTC + 5:45] Nepal Time'],
            ['value' => '6', 'text' => '[UTC + 6] Bangladesh Time, Bhutan Time, Novosibirsk Standard Time'],
            ['value' => '6.5', 'text' => '[UTC + 6:30] Cocos Islands Time, Myanmar Time'],
            ['value' => '7', 'text' => '[UTC + 7] Indochina Time, Krasnoyarsk Standard Time'],
            ['value' => '8', 'text' => '[UTC + 8] Chinese Standard Time, Australian Western Standard Time, Irkutsk Standard Time'],
            ['value' => '8.75', 'text' => '[UTC + 8:45] Southeastern Western Australia Standard Time'],
            ['value' => '9', 'text' => '[UTC + 9] Japan Standard Time, Korea Standard Time, Chita Standard Time'],
            ['value' => '9.5', 'text' => '[UTC + 9:30] Australian Central Standard Time'],
            ['value' => '10', 'text' => '[UTC + 10] Australian Eastern Standard Time, Vladivostok Standard Time'],
            ['value' => '10.5', 'text' => '[UTC + 10:30] Lord Howe Standard Time'],
            ['value' => '11', 'text' => '[UTC + 11] Solomon Island Time, Magadan Standard Time'],
            ['value' => '11.5', 'text' => '[UTC + 11:30] Norfolk Island Time'],
            ['value' => '12', 'text' => '[UTC + 12] New Zealand Time, Fiji Time, Kamchatka Standard Time'],
            ['value' => '12.75', 'text' => '[UTC + 12:45] Chatham Islands Time'],
            ['value' => '13', 'text' => '[UTC + 13] Tonga Time, Phoenix Islands Time'],
            ['value' => '14', 'text' => '[UTC + 14] Line Island Time'],
        ];
        foreach ($options as $option) {
            $option['isSelected'] = $selected && $option['value'] == $selected;
        }
        parent::__construct('timezone', $options, null, 'timezone');
    }
}
