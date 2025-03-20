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
        $this->assertEquals(normaliseCardName('Æther'), 'Aether');
        $this->assertEquals(normaliseCardName('Jötun Grunt'), 'Jotun Grunt');
        $this->assertEquals(normaliseCardName('Jötun Grunt', true), 'jotun grunt');
        $this->assertEquals(normaliseCardName('Dandân'), 'Dandan');
        $this->assertEquals(normaliseCardName('Déjà Vu'), 'Deja Vu');
        $this->assertEquals(normaliseCardName('Ifh-Bíff Efreet'), 'Ifh-Biff Efreet');
        $this->assertEquals(normaliseCardName('Ifh-Bíff Efreet'), 'Ifh-Biff Efreet');
        $this->assertEquals(normaliseCardName('Lim-Dûl'), 'Lim-Dul');
    }
}
