<?php

declare(strict_types=1);

namespace Gatherling\Tests;

require_once 'gatherling/lib.php';

use PHPUnit\Framework\TestCase;

final class NamesTest extends TestCase
{
    public function testNames()
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
