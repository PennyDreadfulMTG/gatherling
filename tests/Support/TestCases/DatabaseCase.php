<?php

declare(strict_types=1);

namespace Gatherling\Tests\Support\TestCases;

use Gatherling\Models\Event;
use Gatherling\Models\Player;
use Gatherling\Models\Series;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;

use function Gatherling\Helpers\db;

// Slightly odd name because PHPUnit issues a warning if "Test" is in the name.
abstract class DatabaseCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!empty($this->requires())) {
            return;
        }
        db()->begin($this->transactionName());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (!empty($this->requires())) {
            return;
        }
        db()->rollback($this->transactionName());
    }

    private function transactionName(): string
    {
        $classHash = substr(md5(get_class($this)), 0, 7);
        return $this->name() . '_' . $classHash;
    }

    /**
     * @param array{
     *   series?: string,
     *   host?: string,
     *   cohost?: string,
     *   name?: string,
     *   start?: DateTimeImmutable,
     *   kvalue?: int,
     *   format?: string,
     *   season?: int,
     *   number?: int,
     *   mainstruct?: string,
     *   mainrounds?: int,
     *   finalstruct?: string,
     *   finalrounds?: int,
     *   threadurl?: string,
     *   reporturl?: string,
     *   metaurl?: string
     * } $overrides
     */
    protected function createTestEvent(array $overrides = []): Event
    {
        $seriesName = $overrides['series'] ?? 'Test Series for Test Event';
        if (Series::exists($seriesName)) {
            $series = new Series($seriesName);
        } else {
            $series = new Series('');
            $series->name = $seriesName;
            $series->start_day = 'Monday';
            $series->start_time = '00:00:00';
            $series->active = 1;
            $series->save();
        }

        $host = Player::findOrCreateByName($overrides['host'] ?? 'Test Host');
        $cohost = null;
        if (isset($overrides['cohost'])) {
            $cohost = Player::findOrCreateByName($overrides['cohost']);
        }

        $event = new Event('');
        $event->name = $overrides['name'] ?? 'Test Event';
        $event->host = $host->name;
        $event->cohost = $cohost->name ?? null;
        $event->start = $overrides['start'] ?? new DateTimeImmutable('2025-01-01');
        $event->kvalue = $overrides['kvalue'] ?? 8;
        $event->format = $overrides['format'] ?? 'Standard';
        $event->series = $series->name;
        $event->season = $overrides['season'] ?? 1;
        $event->number = $overrides['number'] ?? 1;
        $event->mainstruct = $overrides['mainstruct'] ?? 'Swiss';
        $event->mainrounds = $overrides['mainrounds'] ?? 4;
        $event->finalstruct = $overrides['finalstruct'] ?? 'Single Elimination';
        $event->finalrounds = $overrides['finalrounds'] ?? 2;
        $event->threadurl = $overrides['threadurl'] ?? '';
        $event->reporturl = $overrides['reporturl'] ?? '';
        $event->metaurl = $overrides['metaurl'] ?? '';
        $event->save();
        $event = new Event($event->name);
        return $event;
    }
}
