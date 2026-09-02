<?php

declare(strict_types=1);

namespace Gatherling\Views;

class ICal extends TemplateResponse
{
    // No native type on this constant: they need PHP 8.3 and composer.json says ^8.2,
    // where this file would fail to parse and take calendar.php down with it.
    private const ICAL_DATE_FORMAT = 'Ymd\THis';

    /** @var list<array{start: string, end: string, name: string, url: string|null}> */
    public array $events;

    /** @param list<array{start: int, end: int, name: string, url: string|null}> $inputEvents */
    public function __construct(public string $name, public string $description, array $inputEvents)
    {
        $this->setHeader('Content-Type', 'text/calendar');
        $this->events = [];
        foreach ($inputEvents as $event) {
            $this->events[] = [
                'start' => date(self::ICAL_DATE_FORMAT, $event['start']),
                'end' => date(self::ICAL_DATE_FORMAT, $event['end']),
                'name' => $event['name'],
                'url' => $event['url'] ?? null,
            ];
        }
    }

    public function body(): string
    {
        # iCal requires CRLF line endings
        return str_replace("\n", "\r\n", parent::body());
    }
}
