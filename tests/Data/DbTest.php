<?php

declare(strict_types=1);

namespace Gatherling\Tests\Data;

use Gatherling\Data\Db;
use Gatherling\Tests\Support\TestDto;
use Gatherling\Exceptions\DatabaseException;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

class DbTest extends DatabaseCase
{
    protected function setUp(): void
    {
        Db::execute('DROP TABLE IF EXISTS test_table');
        $sql = '
            CREATE TABLE IF NOT EXISTS
                test_table
                (id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255),
                value DECIMAL(4, 3),
                is_active BOOLEAN)';
        Db::execute($sql);
        // Don't start the transaction until after we've created the table, because mariadb will COMMIT
        // when given DDL.
        parent::setUp();
    }

    public function testExecute(): void
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name)';
        $params = [':name' => 'Test Name'];
        Db::execute($sql, $params);
        $rows = Db::select('SELECT name FROM test_table WHERE name = :name', TestDto::class, ['name' => 'Test Name']);
        $this->assertCount(1, $rows);
        $this->assertEquals('Test Name', $rows[0]->name);
    }

    public function testInsert(): void
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name)';
        $params = [':name' => 'Test Insert'];
        $id = Db::insert($sql, $params);
        $this->assertGreaterThan(0, $id);

        $row = Db::selectOnly('SELECT * FROM test_table WHERE id = :id', TestDto::class, ['id' => $id]);
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
        $ids = Db::insertMany($sql, $params);

        $this->assertCount(3, $ids);
        foreach ($ids as $id) {
            $this->assertGreaterThan(0, $id);
            $params = ['id' => $id];
            $rows = Db::select('SELECT * FROM test_table WHERE id = :id', TestDto::class, $params);
            $this->assertCount(1, $rows);
        }
    }

    public function testUpdate(): void
    {
        Db::execute("INSERT INTO test_table (name) VALUES ('Initial Name')");
        $initialId = Db::int('SELECT LAST_INSERT_ID()');

        $sql = 'UPDATE test_table SET name = :name WHERE id = :id';
        $params = [':name' => 'Updated Name', ':id' => $initialId];
        $affectedRows = Db::update($sql, $params);
        $this->assertEquals(1, $affectedRows);

        $updatedRow = Db::selectOnly('SELECT * FROM test_table WHERE id = :id', TestDto::class, ['id' => $initialId]);
        $this->assertEquals('Updated Name', $updatedRow->name);

        $nonExistentId = $initialId + 1;
        $sql = 'UPDATE test_table SET name = :name WHERE id = :id';
        $params = [':name' => 'This Should Not Update', ':id' => $nonExistentId];
        $affectedRows = Db::update($sql, $params);
        $this->assertEquals(0, $affectedRows);

        $rows = Db::select('SELECT * FROM test_table', TestDto::class);
        $this->assertCount(1, $rows);
        $this->assertEquals('Updated Name', $rows[0]->name);
    }

    public function testSelect(): void
    {
        Db::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        Db::execute("INSERT INTO test_table (name) VALUES ('Test2')");

        $rows = Db::select('SELECT name FROM test_table', TestDto::class);
        $this->assertCount(2, $rows);
        $this->assertEquals('Test1', $rows[0]->name);
        $this->assertEquals('Test2', $rows[1]->name);
    }

    public function testSelectOnlyNoData(): void
    {
        $this->expectException(DatabaseException::class);
        Db::selectOnly('SELECT * FROM test_table WHERE id = 1', TestDto::class);
    }

    public function testSelectOnly(): void
    {
        Db::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        Db::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $row = Db::selectOnly('SELECT name FROM test_table WHERE id = 1', TestDto::class);
        $this->assertEquals('Test1', $row->name);
        $this->expectException(DatabaseException::class);
        $row = Db::selectOnly('SELECT * FROM test_table', TestDto::class);
    }

    public function testSelectOnlyOrNull(): void
    {
        $row = Db::selectOnlyOrNull('SELECT * FROM test_table WHERE id = 1', TestDto::class);
        $this->assertNull($row);
        Db::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        Db::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $row = Db::selectOnlyOrNull('SELECT name FROM test_table WHERE id = 1', TestDto::class);
        $this->assertNotNull($row);
        $this->assertEquals('Test1', $row->name);
        $this->expectException(DatabaseException::class);
        $row = Db::selectOnlyOrNull('SELECT * FROM test_table', TestDto::class);
    }
    public function testInt(): void
    {
        Db::execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = Db::int('SELECT id FROM test_table WHERE id = 1');
        $this->assertSame(1, $value);
        $this->expectException(DatabaseException::class);
        Db::int('SELECT id FROM test_table WHERE id = 9999');
    }

    public function testOptionalInt(): void
    {
        Db::execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = Db::optionalInt('SELECT id FROM test_table WHERE id = 1');
        $this->assertSame(1, $value);
        $value = Db::optionalInt('SELECT id FROM test_table WHERE id = 9999');
        $this->assertNull($value);
    }

    public function testString(): void
    {
        Db::execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = Db::string('SELECT name FROM test_table WHERE id = 1');
        $this->assertSame('Test1', $value);
        $this->expectException(DatabaseException::class);
        Db::string('SELECT name FROM test_table WHERE id = 9999');
    }

    public function testOptionalString(): void
    {
        Db::execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = Db::optionalString('SELECT name FROM test_table WHERE id = 1');
        $this->assertSame('Test1', $value);
        $value = Db::optionalString('SELECT name FROM test_table WHERE id = 9999');
        $this->assertNull($value);
    }

    public function testFloat(): void
    {
        Db::execute("INSERT INTO test_table (id, value) VALUES (1, 3.14)");
        $value = Db::float('SELECT value FROM test_table WHERE id = 1');
        $this->assertSame(3.14, $value);
        $this->expectException(DatabaseException::class);
        Db::float('SELECT value FROM test_table WHERE id = 9999');
    }

    public function testOptionalFloat(): void
    {
        Db::execute("INSERT INTO test_table (id, value) VALUES (1, 3.14)");
        $value = Db::optionalFloat('SELECT value FROM test_table WHERE id = 1');
        $this->assertSame(3.14, $value);
        $value = Db::optionalFloat('SELECT value FROM test_table WHERE id = 9999');
        $this->assertNull($value);
    }

    public function testBool(): void
    {
        Db::execute("INSERT INTO test_table (id, is_active) VALUES (1, true)");
        $value = Db::bool('SELECT is_active FROM test_table WHERE id = 1');
        $this->assertSame(true, $value);
        $this->expectException(DatabaseException::class);
        Db::bool('SELECT is_active FROM test_table WHERE id = 9999');
    }

    public function testOptionalBool(): void
    {
        Db::execute("INSERT INTO test_table (id, is_active) VALUES (1, true)");
        $value = Db::optionalBool('SELECT is_active FROM test_table WHERE id = 1');
        $this->assertSame(true, $value);
        $value = Db::optionalBool('SELECT is_active FROM test_table WHERE id = 9999');
        $this->assertNull($value);
    }

    public function testValues(): void
    {
        Db::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        Db::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $values = Db::strings('SELECT name FROM test_table');
        $this->assertEquals(['Test1', 'Test2'], $values);
        $values = Db::ints('SELECT id FROM test_table');
        $this->assertEquals([1, 2], $values);
    }

    public function testStringsThrowsOnInts(): void
    {
        Db::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        Db::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $this->expectException(DatabaseException::class);
        Db::strings('SELECT id FROM test_table');
    }

    public function testIntsThrowsOnStrings(): void
    {
        Db::execute("INSERT INTO test_table (name) VALUES ('Test1')");
        Db::execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $this->expectException(DatabaseException::class);
        Db::ints('SELECT name FROM test_table');
    }

    public function testCommit(): void
    {
        Db::begin('my_transaction');
        Db::execute("INSERT INTO test_table (name) VALUES ('Test for Commit')");
        Db::commit('my_transaction');

        $rows = Db::select("SELECT * FROM test_table WHERE name = 'Test for Commit'", TestDto::class);
        $this->assertCount(1, $rows);
    }

    public function testRollback(): void
    {
        Db::begin('test_rollback');
        Db::execute("INSERT INTO test_table (name) VALUES ('Test for Rollback')");
        Db::rollback('test_rollback');

        $rows = Db::select("SELECT * FROM test_table WHERE name = 'Test for Rollback'", TestDto::class);
        $this->assertCount(0, $rows);
    }

    public function testNestedTransaction(): void
    {
        Db::begin('test_nested_transaction');
        Db::execute("INSERT INTO test_table (name) VALUES ('Test for Nested Transaction')");
        Db::begin('test_nested_transaction_inner');
        Db::execute("INSERT INTO test_table (name) VALUES ('Test for Nested Transaction Inner')");
        Db::rollback('test_nested_transaction_inner');
        Db::commit('test_nested_transaction');

        $rows = Db::select("SELECT * FROM test_table WHERE name = 'Test for Nested Transaction'", TestDto::class);
        $this->assertCount(1, $rows);
    }

    public function testLikeEscape(): void
    {
        $this->assertEquals('\\%', Db::likeEscape('%'));
        $this->assertEquals('\\_', Db::likeEscape('_'));
        $this->assertEquals('\\%\\_\\%', Db::likeEscape('%_%'));
        $this->assertEquals('\\%StartMiddle\\_End', Db::likeEscape('%StartMiddle_End'));
        $this->assertEquals('Test!@#$^&*()', Db::likeEscape('Test!@#$^&*()'));
        $this->assertEquals('Complex\\%Test\\_Case!', Db::likeEscape('Complex%Test_Case!'));
        $this->assertEquals('', Db::likeEscape(''));
        $longString = str_repeat('%_', 100);
        $expectedLongString = str_repeat('\\%\\_', 100);
        $this->assertEquals($expectedLongString, Db::likeEscape($longString));
    }
}
