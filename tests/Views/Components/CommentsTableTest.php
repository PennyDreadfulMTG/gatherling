<?php

declare(strict_types=1);

namespace Gatherling\Tests\Views\Components;

use Gatherling\Views\Components\CommentsTable;
use PHPUnit\Framework\TestCase;

class CommentsTableTest extends TestCase
{
    public function testCommentsTable(): void
    {
        $commentsTable = new CommentsTable('');
        $html = $commentsTable->render();
        $this->assertStringContainsString('No comments have been recorded for this deck.', $html);

        $commentsTable = new CommentsTable('Hello & goodbye <br> [b]Bold[/b]');
        $html = $commentsTable->render();
        $this->assertStringContainsString('Hello &amp; goodbye', $html);
        $this->assertStringContainsString('<b>Bold</b>', $html);
    }
}
