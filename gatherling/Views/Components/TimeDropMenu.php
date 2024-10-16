<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class TimeDropMenu extends DropMenu
{
    public function __construct(string $name, int|string $hour, int|string $minutes = 0)
    {
        if (strcmp((string) $hour, '') == 0) {
            $hour = -1;
        }
        $options = [];
        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m += 30) {
                $hstring = $h;
                if ($m == 0) {
                    $mstring = ':00';
                } else {
                    $mstring = ":$m";
                }
                if ($h == 0) {
                    $hstring = '12';
                }
                $apstring = ' AM';
                if ($h >= 12) {
                    $hstring = $h != 12 ? $h - 12 : $h;
                    $apstring = ' PM';
                }
                if ($h == 0 && $m == 0) {
                    $hstring = 'Midnight';
                    $mstring = '';
                    $apstring = '';
                } elseif ($h == 12 && $m == 0) {
                    $hstring = 'Noon';
                    $mstring = '';
                    $apstring = '';
                }
                $options[] = [
                    'value'      => "$h:$m",
                    'text'       => "$hstring$mstring$apstring",
                    'isSelected' => $hour == $h && $minutes == $m,
                ];
            }
        }
        parent::__construct($name, $options, '- Hour -');
    }
}
