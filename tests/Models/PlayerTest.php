<?php

namespace Gatherling\Tests\Models;

use Gatherling\Models\Player;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

final class PlayerTest extends DatabaseCase
{
    public function testFindOrCreateByName()
    {
        $player = Player::findOrCreateByName('test');
        $this->assertNotNull($player);
        $this->assertEquals('test', $player->name);
        $this->assertNull($player->password);

        $player->password = 'password';
        $player->save();
        $this->assertEquals('password', $player->password);

        $player2 = Player::findOrCreateByName('test');
        $this->assertEquals($player, $player2);
        $this->assertEquals('password', $player2->password);

        $player3 = Player::findOrCreateByName(null);
        $this->assertNull($player3);
    }
}
