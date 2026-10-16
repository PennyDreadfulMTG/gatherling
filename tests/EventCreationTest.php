<?php

declare(strict_types=1);

namespace Gatherling\Tests;

require_once __DIR__ . '/../gatherling/event.php';

use DateInterval;
use Gatherling\Exceptions\NotFoundInDatabaseException;
use Gatherling\Models\Event;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

use function Gatherling\newEventFromEventName;

final class EventCreationTest extends DatabaseCase
{
    public function testFirstEventFromPlaceholder(): void
    {
        $event = newEventFromEventName('Brand New Series 1.00');

        self::assertSame('Brand New Series', $event->series);
        self::assertSame('Brand New Series 0.01', $event->name);
        self::assertSame(0, $event->season);
        self::assertSame(1, $event->number);
        self::assertSame(0, $event->id);
        self::assertFalse(Event::exists($event->name));
    }

    public function testNextEventCopiesExistingEvent(): void
    {
        $previous = new Event('Test 1.01');
        $event = newEventFromEventName($previous->name);

        self::assertSame('Test 1.02', $event->name);
        self::assertSame($previous->season, $event->season);
        self::assertSame($previous->number + 1, $event->number);
        self::assertEquals($previous->start->add(new DateInterval('P1W')), $event->start);
        self::assertSame($previous->series, $event->series);
        self::assertSame($previous->format, $event->format);
        self::assertSame($previous->host, $event->host);
        self::assertSame($previous->kvalue, $event->kvalue);
        self::assertSame($previous->mainrounds, $event->mainrounds);
        self::assertSame($previous->mainstruct, $event->mainstruct);
        self::assertSame($previous->private_decks, $event->private_decks);
        self::assertSame(0, $event->finalized);
        self::assertSame(0, $event->id);
    }

    public function testNextSeasonStartsWithFirstEvent(): void
    {
        $previous = new Event('Test 1.01');
        $event = newEventFromEventName($previous->name, true);

        self::assertSame('Test 2.01', $event->name);
        self::assertSame($previous->season + 1, $event->season);
        self::assertSame(1, $event->number);
        self::assertEquals($previous->start->add(new DateInterval('P1W')), $event->start);
        self::assertSame($previous->series, $event->series);
        self::assertSame($previous->format, $event->format);
        self::assertSame(0, $event->finalized);
        self::assertSame(0, $event->id);
    }

    public function testMissingEventIsNotAPlaceholder(): void
    {
        $this->expectException(NotFoundInDatabaseException::class);

        newEventFromEventName('Missing Series 9.99');
    }

    public function testMalformedPlaceholderIsRejected(): void
    {
        $this->expectException(NotFoundInDatabaseException::class);

        newEventFromEventName('Missing Series 1x00');
    }
}
