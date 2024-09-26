<?php

declare(strict_types=1);

namespace Gatherling\Tests\Models;

use Gatherling\Models\Pagination;
use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['QUERY_STRING'] = 'foo=1&bar=2';
    }

    public function testSetProperties(): void
    {
        $pagination = new Pagination();
        $pagination->records(100);
        $pagination->records_per_page(10);
        $pagination->set_page(2);

        $this->assertEquals(2, $pagination->get_page());
        ob_start();
        $pagination->render();
        $html = ob_get_clean();
        $this->assertStringContainsString('<a href="/test?foo=1&amp;bar=2&amp;page=2" class="current">02</a>', $html);
    }

    public function testGetPages(): void
    {
        $pagination = new Pagination();

        $pagination->records(100);
        $pagination->records_per_page(10);
        $this->assertEquals(10, $pagination->get_pages());

        $pagination->records(101);
        $pagination->records_per_page(10);
        $this->assertEquals(11, $pagination->get_pages());

        $pagination->records(5);
        $pagination->records_per_page(10);
        $this->assertEquals(1, $pagination->get_pages());
    }
}
