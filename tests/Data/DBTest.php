<?php

declare(strict_types=1);

namespace Gatherling\Tests\Data;

use Gatherling\Data\DB;
use Gatherling\Tests\Support\TestDto;
use Gatherling\Exceptions\DatabaseException;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

class DBTest extends DatabaseCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::execute('DROP TABLE IF EXISTS test_table');
        DB::execute('CREATE TABLE IF NOT EXISTS test_table (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255))');
    }

    public function testInsert(): void
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name)';
        $params = [':name' => 'Test Name'];
        DB::execute($sql, $params);
        $rows = DB::select('SELECT name FROM test_table WHERE name = :name', TestDto::class, ['name' => 'Test Name']);
        $this->assertCount(1, $rows);
        $this->assertEquals('Test Name', $rows[0]->name);
    }

    public function testSelect(): void
    {
        DB::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        DB::execute("INSERT INTO test_table (name) VALUES ('Test2')");

        $rows = DB::select('SELECT name FROM test_table', TestDto::class);
        $this->assertCount(2, $rows);
        $this->assertEquals('Test1', $rows[0]->name);
        $this->assertEquals('Test2', $rows[1]->name);
    }

    public function testSelectOnlyNoData(): void
    {
        $this->expectException(DatabaseException::class);
        DB::selectOnly('SELECT * FROM test_table WHERE id = 1', TestDto::class);
    }

    public function testSelectOnly(): void
    {
        DB::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        DB::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $row = DB::selectOnly('SELECT name FROM test_table WHERE id = 1', TestDto::class);
        $this->assertEquals('Test1', $row->name);
        $this->expectException(DatabaseException::class);
        $row = DB::selectOnly('SELECT * FROM test_table', TestDto::class);
    }

    public function testSelectOnlyOrNull(): void
    {
        $row = DB::selectOnlyOrNull('SELECT * FROM test_table WHERE id = 1', TestDto::class);
        $this->assertNull($row);
        DB::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        DB::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $row = DB::selectOnlyOrNull('SELECT name FROM test_table WHERE id = 1', TestDto::class);
        $this->assertNotNull($row);
        $this->assertEquals('Test1', $row->name);
        $this->expectException(DatabaseException::class);
        $row = DB::selectOnlyOrNull('SELECT * FROM test_table', TestDto::class);
    }

    public function testValue(): void
    {
        DB::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        $value = DB::value('SELECT name FROM test_table WHERE id = 1');
        $this->assertEquals('Test1', $value);
        $value = DB::value('SELECT id FROM test_table WHERE id = 1');
        $this->assertEquals(1, $value);
        $value = DB::value('SELECT id FROM test_table WHERE id = 9999', [], true);
        $this->assertNull($value);
        $this->expectException(DatabaseException::class);
        DB::value('SELECT id FROM test_table WHERE id = 9999');
    }

    public function testValues(): void
    {
        DB::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        DB::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $values = DB::values('SELECT name FROM test_table', 'string');
        $this->assertEquals(['Test1', 'Test2'], $values);
    }

    public function testCommit(): void
    {
        DB::begin('my_transaction');
        DB::execute("INSERT INTO test_table (name) VALUES ('Test for Commit')");
        DB::commit('my_transaction');

        $rows = DB::select("SELECT * FROM test_table WHERE name = 'Test for Commit'", TestDto::class);
        $this->assertCount(1, $rows);
    }

    public function testRollback(): void
    {
        DB::begin('test_rollback');
        DB::execute("INSERT INTO test_table (name) VALUES ('Test for Rollback')");
        DB::rollback('test_rollback');

        $rows = DB::select("SELECT * FROM test_table WHERE name = 'Test for Rollback'", TestDto::class);
        $this->assertCount(0, $rows);
    }

    public function testNestedTransaction(): void
    {
        DB::begin('test_nested_transaction');
        DB::execute("INSERT INTO test_table (name) VALUES ('Test for Nested Transaction')");
        DB::begin('test_nested_transaction_inner');
        DB::execute("INSERT INTO test_table (name) VALUES ('Test for Nested Transaction Inner')");
        DB::rollback('test_nested_transaction_inner');
        DB::commit('test_nested_transaction');

        $rows = DB::select("SELECT * FROM test_table WHERE name = 'Test for Nested Transaction'", TestDto::class);
        $this->assertCount(1, $rows);
    }
}
