<?php

declare(strict_types=1);

namespace Gatherling\Tests;

require_once __DIR__ . '/../gatherling/bootstrap.php';

use PHPUnit\Framework\TestCase;

use function Gatherling\Helpers\normaliseCardName;

final class NamesTest extends TestCase
{
    public function testNames(): void
    {
        self::assertEquals(normaliseCardName('Æther'), 'Aether');
        self::assertEquals(normaliseCardName('Jötun Grunt'), 'Jotun Grunt');
        self::assertEquals(normaliseCardName('Jötun Grunt', true), 'jotun grunt');
        self::assertEquals(normaliseCardName('Dandân'), 'Dandan');
        self::assertEquals(normaliseCardName('Déjà Vu'), 'Deja Vu');
        self::assertEquals(normaliseCardName('Ifh-Bíff Efreet'), 'Ifh-Biff Efreet');
        self::assertEquals(normaliseCardName('Ifh-Bíff Efreet'), 'Ifh-Biff Efreet');
        self::assertEquals(normaliseCardName('Lim-Dûl'), 'Lim-Dul');
    }
}
