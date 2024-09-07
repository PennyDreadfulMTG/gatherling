<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Format;
use Gatherling\Tests\DatabaseCase;

final class FormatTest extends DatabaseCase
{
    private Format $model;

    public function setUp(): void
    {
        parent::setUp();
        $this->model = new Format('Test Format');
    }

    public function testGetCoreCardsets(): void
    {
        $coreSets = $this->model->getCoreCardsets();
        $this->assertEquals(['Magic 2010'], $coreSets);
    }

    public function testGetBlockCardsets(): void
    {
        $blockSets = $this->model->getBlockCardsets();
        $this->assertEqualsCanonicalizing(['Kaladesh', 'Throne of Eldraine'], $blockSets);
    }

    public function testGetExtraCardsets(): void
    {
        $extraSets = $this->model->getExtraCardsets();
        $this->assertEmpty($extraSets);
    }
}
