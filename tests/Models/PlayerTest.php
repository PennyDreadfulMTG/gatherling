<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Player;
use Gatherling\Models\Series;
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

    public function testOrganizersSeries(): void
    {
        // BAKERT and test with actual series, and maybe try and test adding an invalid series
        $player = Player::findOrCreateByName('An Organizer');
        $this->assertEmpty($player->organizersSeries());

        $player->save();
        $this->assertEmpty($player->organizersSeries());

        $series = new Series('');
        $series->name = 'My Test Series';
        $series->start_day = 'Monday';
        $series->start_time = '12:00:00';
        $series->save();
        $this->assertEmpty($player->organizersSeries());

        $player->super = 1;
        $player->save();
        $this->assertContains($series->name, $player->organizersSeries());

        $player->super = 0;
        $player->save();
        $this->assertEmpty($player->organizersSeries());

        $this->assertNotEmpty($player->name);
        $series->addOrganizer($player->name);
        $this->assertEquals([$series->name], $player->organizersSeries());
    }
}
