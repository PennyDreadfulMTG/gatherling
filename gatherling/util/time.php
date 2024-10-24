<?php

declare(strict_types=1);

use Gatherling\Views\Components\Time;

function human_date(int $datetime, int $now): string
{
    $elapsed = abs($now - $datetime);
    if ($elapsed == 0) {
        return 'just now';
    }
    if ($elapsed > 60 * 60 * 24 * 365) {
        return date('M Y', $datetime);
    }
    if ($elapsed > 60 * 60 * 24 * 28) {
        return date('M jS', $datetime);
    }
    $suffix = $datetime > $now ? 'from now' : 'ago';
    $INTERVALS = [
        'week'   => 60 * 60 * 24 * 7,
        'day'    => 60 * 60 * 24,
        'hour'   => 60 * 60,
        'minute' => 60,
        'second' => 1,
    ];
    foreach ($INTERVALS as $interval => $duration) {
        if ($elapsed >= $duration) {
            return pluralize(intdiv($elapsed, $duration), $interval) . " $suffix";
        }
    }
    return 'unknown';
}

function pluralize(int $n, string $noun): string
{
    return $n . ' ' . $noun . ($n != 1 ? 's' : '');
}
