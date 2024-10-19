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
        $sql = '
            CREATE TABLE IF NOT EXISTS
                test_table
                (id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255),
                value DECIMAL(4, 3),
                is_active BOOLEAN)';
        DB::execute($sql);
    }

    public function testExecute(): void
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name)';
        $params = [':name' => 'Test Name'];
        DB::execute($sql, $params);
        $rows = DB::select('SELECT name FROM test_table WHERE name = :name', TestDto::class, ['name' => 'Test Name']);
        $this->assertCount(1, $rows);
        $this->assertEquals('Test Name', $rows[0]->name);
    }

    public function testInsert(): void
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name)';
        $params = [':name' => 'Test Insert'];
        $id = DB::insert($sql, $params);
        $this->assertGreaterThan(0, $id);

        $row = DB::selectOnly('SELECT * FROM test_table WHERE id = :id', TestDto::class, ['id' => $id]);
        $this->assertEquals('Test Insert', $row->name);
    }

    public function testInsertMany(): void
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name1), (:name2), (:name3)';
        $params = [
            ':name1' => 'Test Insert 1',
            ':name2' => 'Test Insert 2',
            ':name3' => 'Test Insert 3'
        ];
        $ids = DB::insertMany($sql, $params);

        $this->assertCount(3, $ids);
        foreach ($ids as $id) {
            $this->assertGreaterThan(0, $id);
            $params = ['id' => $id];
            $rows = DB::select('SELECT * FROM test_table WHERE id = :id', TestDto::class, $params);
            $this->assertCount(1, $rows);
        }
    }

    public function testUpdate(): void
    {
        DB::execute("INSERT INTO test_table (name) VALUES ('Initial Name')");
        $initialId = DB::int('SELECT LAST_INSERT_ID()');

        $sql = 'UPDATE test_table SET name = :name WHERE id = :id';
        $params = [':name' => 'Updated Name', ':id' => $initialId];
        $affectedRows = DB::update($sql, $params);
        $this->assertEquals(1, $affectedRows);

        $updatedRow = DB::selectOnly('SELECT * FROM test_table WHERE id = :id', TestDto::class, ['id' => $initialId]);
        $this->assertEquals('Updated Name', $updatedRow->name);

        $nonExistentId = $initialId + 1;
        $sql = 'UPDATE test_table SET name = :name WHERE id = :id';
        $params = [':name' => 'This Should Not Update', ':id' => $nonExistentId];
        $affectedRows = DB::update($sql, $params);
        $this->assertEquals(0, $affectedRows);

        $rows = DB::select('SELECT * FROM test_table', TestDto::class);
        $this->assertCount(1, $rows);
        $this->assertEquals('Updated Name', $rows[0]->name);
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
    public function testInt(): void
    {
        DB::execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = DB::int('SELECT id FROM test_table WHERE id = 1');
        $this->assertSame(1, $value);
        $this->expectException(DatabaseException::class);
        DB::int('SELECT id FROM test_table WHERE id = 9999');
    }

    public function testOptionalInt(): void
    {
        DB::execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = DB::optionalInt('SELECT id FROM test_table WHERE id = 1');
        $this->assertSame(1, $value);
        $value = DB::optionalInt('SELECT id FROM test_table WHERE id = 9999');
        $this->assertNull($value);
    }

    public function testString(): void
    {
        DB::execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = DB::string('SELECT name FROM test_table WHERE id = 1');
        $this->assertSame('Test1', $value);
        $this->expectException(DatabaseException::class);
        DB::string('SELECT name FROM test_table WHERE id = 9999');
    }

    public function testOptionalString(): void
    {
        DB::execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = DB::optionalString('SELECT name FROM test_table WHERE id = 1');
        $this->assertSame('Test1', $value);
        $value = DB::optionalString('SELECT name FROM test_table WHERE id = 9999');
        $this->assertNull($value);
    }

    public function testFloat(): void
    {
        DB::execute("INSERT INTO test_table (id, value) VALUES (1, 3.14)");
        $value = DB::float('SELECT value FROM test_table WHERE id = 1');
        $this->assertSame(3.14, $value);
        $this->expectException(DatabaseException::class);
        DB::float('SELECT value FROM test_table WHERE id = 9999');
    }

    public function testOptionalFloat(): void
    {
        DB::execute("INSERT INTO test_table (id, value) VALUES (1, 3.14)");
        $value = DB::optionalFloat('SELECT value FROM test_table WHERE id = 1');
        $this->assertSame(3.14, $value);
        $value = DB::optionalFloat('SELECT value FROM test_table WHERE id = 9999');
        $this->assertNull($value);
    }

    public function testBool(): void
    {
        DB::execute("INSERT INTO test_table (id, is_active) VALUES (1, true)");
        $value = DB::bool('SELECT is_active FROM test_table WHERE id = 1');
        $this->assertSame(true, $value);
        $this->expectException(DatabaseException::class);
        DB::bool('SELECT is_active FROM test_table WHERE id = 9999');
    }

    public function testOptionalBool(): void
    {
        DB::execute("INSERT INTO test_table (id, is_active) VALUES (1, true)");
        $value = DB::optionalBool('SELECT is_active FROM test_table WHERE id = 1');
        $this->assertSame(true, $value);
        $value = DB::optionalBool('SELECT is_active FROM test_table WHERE id = 9999');
        $this->assertNull($value);
    }

    public function testValues(): void
    {
        DB::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        DB::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $values = DB::strings('SELECT name FROM test_table');
        $this->assertEquals(['Test1', 'Test2'], $values);
        $values = DB::ints('SELECT id FROM test_table');
        $this->assertEquals([1, 2], $values);
    }

    public function testStringsThrowsOnInts(): void
    {
        DB::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        DB::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $this->expectException(DatabaseException::class);
        DB::strings('SELECT id FROM test_table');
    }

    public function testIntsThrowsOnStrings(): void
    {
        DB::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        DB::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $this->expectException(DatabaseException::class);
        DB::ints('SELECT name FROM test_table');
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
