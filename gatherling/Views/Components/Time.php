<?php

declare(strict_types=1);

namespace Gatherling\Views\Components;

class Time extends Component
{
    public string $datetime;
    public string $text;

    public function __construct(int $time, int $now, public bool $long = false)
    {
        $this->datetime = date('c', $time);
        $this->text = $this->humanDate($time, $now);
    }

    private function humanDate(int $datetime, int $now): string
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
                return $this->pluralize(intdiv($elapsed, $duration), $interval) . " $suffix";
            }
        }
        return 'unknown';
    }

    private function pluralize(int $n, string $noun): string
    {
        return $n . ' ' . $noun . ($n != 1 ? 's' : '');
    }
}
