<?php

function time_element($datetime, $now): string
{
    return '<time datetime="' . date('c', $datetime) . '">' . human_date($datetime, $now) . '</time>';
}

function human_date($datetime, $now): string
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
        'week' => 60 * 60 * 24 * 7,
        'day' => 60 * 60 * 24,
        'hour' => 60 * 60,
        'minute' => 60,
        'second' => 1,
    ];
    foreach ($INTERVALS as $interval => $duration) {
        if ($elapsed > $duration) {
            return pluralize(intdiv($elapsed, $duration), $interval) . " $suffix";
        }
    }
}

function pluralize($n, $noun): string {
    return $n . ' ' . $noun . ($n != 1 ? 's' : '');
}
