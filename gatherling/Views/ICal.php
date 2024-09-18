<?php

declare(strict_types=1);

namespace Gatherling\Views;

class ICal extends TemplateResponse
{
    public array $events;

    public function __construct(public string $name, public string $description, array $inputEvents)
    {
        $this->setHeader('Content-Type', 'text/calendar');
        $this->events = [];
        foreach ($inputEvents as $event) {
            $this->events[] = [
                'start' => date('Ymd\THis', $event['start']),
                'end' => date('Ymd\THis', $event['end']),
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
