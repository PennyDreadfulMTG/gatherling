<?php

declare(strict_types=1);

namespace Gatherling\Tests\Data;

use Gatherling\Data\DB;
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

    public function testInsert()
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name)';
        $params = [':name' => 'Test Name'];
        DB::execute($sql, $params);
        $rows = DB::select('SELECT * FROM test_table WHERE name = :name', [':name' => 'Test Name']);
        $this->assertCount(1, $rows);
        $this->assertEquals('Test Name', $rows[0]['name']);
    }

    public function testSelect()
    {
        DB::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        DB::execute("INSERT INTO test_table (name) VALUES ('Test2')");

        $rows = DB::select('SELECT * FROM test_table');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test1', $rows[0]['name']);
        $this->assertEquals('Test2', $rows[1]['name']);
    }

    public function testValue()
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

    public function testCommit()
    {
        DB::begin('my_transaction');
        DB::execute("INSERT INTO test_table (name) VALUES ('Test for Commit')");
        DB::commit('my_transaction');

        $rows = DB::select("SELECT * FROM test_table WHERE name = 'Test for Commit'");
        $this->assertCount(1, $rows);
    }

    public function testRollback()
    {
        DB::begin('test_rollback');
        DB::execute("INSERT INTO test_table (name) VALUES ('Test for Rollback')");
        DB::rollback('test_rollback');

        $rows = DB::select("SELECT * FROM test_table WHERE name = 'Test for Rollback'");
        $this->assertCount(0, $rows);
    }

    public function testNestedTransaction()
    {
        DB::begin('test_nested_transaction');
        DB::execute("INSERT INTO test_table (name) VALUES ('Test for Nested Transaction')");
        DB::begin('test_nested_transaction_inner');
        DB::execute("INSERT INTO test_table (name) VALUES ('Test for Nested Transaction Inner')");
        DB::rollback('test_nested_transaction_inner');
        DB::commit('test_nested_transaction');

        $rows = DB::select("SELECT * FROM test_table WHERE name = 'Test for Nested Transaction'");
        $this->assertCount(1, $rows);
    }
}
