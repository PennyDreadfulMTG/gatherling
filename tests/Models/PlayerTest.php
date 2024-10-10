<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Player;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

final class PlayerTest extends DatabaseCase
{
    public function testFindOrCreateByName(): void
    {
        $player = Player::findOrCreateByName('test');
        $this->assertEquals('test', $player->name);
        $this->assertNull($player->password);

        $player->password = 'password';
        $player->save();
        $this->assertEquals('password', $player->password);

        $player2 = Player::findOrCreateByName('test');
        $this->assertEquals($player, $player2);
        $this->assertEquals('password', $player2->password);
    }

    public function testFindByName(): void
    {
        $player = Player::findByName('foo');
        $this->assertNull($player);

        Player::findOrCreateByName('foo');

        $player = Player::findByName('foo');
        $this->assertNotNull($player);

        $player2 = Player::findByName('bar');
        $this->assertNull($player2);
    }
}
