<?php

declare(strict_types=1);

namespace Gatherling\Tests\Data;

use Gatherling\Tests\Support\TestDto;
use Gatherling\Exceptions\DatabaseException;
use Gatherling\Tests\Support\TestCases\DatabaseCase;

use function Gatherling\Helpers\db;

class DbTest extends DatabaseCase
{
    protected function setUp(): void
    {
        db()->execute('DROP TABLE IF EXISTS test_table');
        $sql = '
            CREATE TABLE IF NOT EXISTS
                test_table
                (id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255),
                value DECIMAL(4, 3),
                is_active BOOLEAN)';
        db()->execute($sql);
        // Don't start the transaction until after we've created the table, because mariadb will COMMIT
        // when given DDL.
        parent::setUp();
    }

    public function testExecute(): void
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name)';
        $params = [':name' => 'Test Name'];
        db()->execute($sql, $params);
        $rows = db()->select('SELECT name FROM test_table WHERE name = :name', TestDto::class, ['name' => 'Test Name']);
        self::assertCount(1, $rows);
        self::assertEquals('Test Name', $rows[0]->name);
    }

    public function testInsert(): void
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name)';
        $params = [':name' => 'Test Insert'];
        $id = db()->insert($sql, $params);
        self::assertGreaterThan(0, $id);

        $row = db()->selectOnly('SELECT * FROM test_table WHERE id = :id', TestDto::class, ['id' => $id]);
        self::assertEquals('Test Insert', $row->name);
    }

    public function testInsertMany(): void
    {
        $sql = 'INSERT INTO test_table (name) VALUES (:name1), (:name2), (:name3)';
        $params = [
            ':name1' => 'Test Insert 1',
            ':name2' => 'Test Insert 2',
            ':name3' => 'Test Insert 3'
        ];
        $ids = db()->insertMany($sql, $params);

        self::assertCount(3, $ids);
        foreach ($ids as $id) {
            self::assertGreaterThan(0, $id);
            $params = ['id' => $id];
            $rows = db()->select('SELECT * FROM test_table WHERE id = :id', TestDto::class, $params);
            self::assertCount(1, $rows);
        }
    }

    public function testUpdate(): void
    {
        db()->execute("INSERT INTO test_table (name) VALUES ('Initial Name')");
        $initialId = db()->int('SELECT LAST_INSERT_ID()');

        $sql = 'UPDATE test_table SET name = :name WHERE id = :id';
        $params = [':name' => 'Updated Name', ':id' => $initialId];
        $affectedRows = db()->modify($sql, $params);
        self::assertEquals(1, $affectedRows);

        $updatedRow = db()->selectOnly('SELECT * FROM test_table WHERE id = :id', TestDto::class, ['id' => $initialId]);
        self::assertEquals('Updated Name', $updatedRow->name);

        $nonExistentId = $initialId + 1;
        $sql = 'UPDATE test_table SET name = :name WHERE id = :id';
        $params = [':name' => 'This Should Not Update', ':id' => $nonExistentId];
        $affectedRows = db()->modify($sql, $params);
        self::assertEquals(0, $affectedRows);

        $rows = db()->select('SELECT * FROM test_table', TestDto::class);
        self::assertCount(1, $rows);
        self::assertEquals('Updated Name', $rows[0]->name);
    }

    public function testSelect(): void
    {
        db()->execute("INSERT INTO test_table (name) VALUES ('Test1')");
        db()->execute("INSERT INTO test_table (name) VALUES ('Test2')");

        $rows = db()->select('SELECT name FROM test_table', TestDto::class);
        self::assertCount(2, $rows);
        self::assertEquals('Test1', $rows[0]->name);
        self::assertEquals('Test2', $rows[1]->name);
    }

    public function testSelectOnlyNoData(): void
    {
        $this->expectException(DatabaseException::class);
        db()->selectOnly('SELECT * FROM test_table WHERE id = 1', TestDto::class);
    }

    public function testSelectOnly(): void
    {
        db()->execute("INSERT INTO test_table (name) VALUES ('Test1')");
        db()->execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $row = db()->selectOnly('SELECT name FROM test_table WHERE id = 1', TestDto::class);
        self::assertEquals('Test1', $row->name);
        $this->expectException(DatabaseException::class);
        db()->selectOnly('SELECT * FROM test_table', TestDto::class);
    }

    public function testSelectOnlyOrNull(): void
    {
        $row = db()->selectOnlyOrNull('SELECT * FROM test_table WHERE id = 1', TestDto::class);
        self::assertNull($row);
        db()->execute("INSERT INTO test_table (name) VALUES ('Test1')");
        db()->execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $row = db()->selectOnlyOrNull('SELECT name FROM test_table WHERE id = 1', TestDto::class);
        self::assertNotNull($row);
        self::assertEquals('Test1', $row->name);
        $this->expectException(DatabaseException::class);
        db()->selectOnlyOrNull('SELECT * FROM test_table', TestDto::class);
    }
    public function testInt(): void
    {
        db()->execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = db()->int('SELECT id FROM test_table WHERE id = 1');
        self::assertSame(1, $value);
        $this->expectException(DatabaseException::class);
        db()->int('SELECT id FROM test_table WHERE id = 9999');
    }

    public function testOptionalInt(): void
    {
        db()->execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = db()->optionalInt('SELECT id FROM test_table WHERE id = 1');
        self::assertSame(1, $value);
        $value = db()->optionalInt('SELECT id FROM test_table WHERE id = 9999');
        self::assertNull($value);
    }

    public function testString(): void
    {
        db()->execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = db()->string('SELECT name FROM test_table WHERE id = 1');
        self::assertSame('Test1', $value);
        $this->expectException(DatabaseException::class);
        db()->string('SELECT name FROM test_table WHERE id = 9999');
    }

    public function testOptionalString(): void
    {
        db()->execute("INSERT INTO test_table (id, name) VALUES (1, 'Test1')");
        $value = db()->optionalString('SELECT name FROM test_table WHERE id = 1');
        self::assertSame('Test1', $value);
        $value = db()->optionalString('SELECT name FROM test_table WHERE id = 9999');
        self::assertNull($value);
    }

    public function testFloat(): void
    {
        db()->execute("INSERT INTO test_table (id, value) VALUES (1, 3.14)");
        $value = db()->float('SELECT value FROM test_table WHERE id = 1');
        self::assertSame(3.14, $value);
        $this->expectException(DatabaseException::class);
        db()->float('SELECT value FROM test_table WHERE id = 9999');
    }

    public function testOptionalFloat(): void
    {
        db()->execute("INSERT INTO test_table (id, value) VALUES (1, 3.14)");
        $value = db()->optionalFloat('SELECT value FROM test_table WHERE id = 1');
        self::assertSame(3.14, $value);
        $value = db()->optionalFloat('SELECT value FROM test_table WHERE id = 9999');
        self::assertNull($value);
    }

    public function testBool(): void
    {
        db()->execute("INSERT INTO test_table (id, is_active) VALUES (1, true)");
        $value = db()->bool('SELECT is_active FROM test_table WHERE id = 1');
        self::assertSame(true, $value);
        $this->expectException(DatabaseException::class);
        db()->bool('SELECT is_active FROM test_table WHERE id = 9999');
    }

    public function testOptionalBool(): void
    {
        db()->execute("INSERT INTO test_table (id, is_active) VALUES (1, true)");
        $value = db()->optionalBool('SELECT is_active FROM test_table WHERE id = 1');
        self::assertSame(true, $value);
        $value = db()->optionalBool('SELECT is_active FROM test_table WHERE id = 9999');
        self::assertNull($value);
    }

    public function testValues(): void
    {
        db()->execute("INSERT INTO test_table (name) VALUES ('Test1')");
        db()->execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $values = db()->strings('SELECT name FROM test_table');
        self::assertEquals(['Test1', 'Test2'], $values);
        $values = db()->ints('SELECT id FROM test_table');
        self::assertEquals([1, 2], $values);
    }

    public function testStringsThrowsOnInts(): void
    {
        db()->execute("INSERT INTO test_table (name) VALUES ('Test1')");
        db()->execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $this->expectException(DatabaseException::class);
        db()->strings('SELECT id FROM test_table');
    }

    public function testIntsThrowsOnStrings(): void
    {
        db()->execute("INSERT INTO test_table (name) VALUES ('Test1')");
        db()->execute("INSERT INTO test_table (name) VALUES ('Test2')");
        $this->expectException(DatabaseException::class);
        db()->ints('SELECT name FROM test_table');
    }

    public function testCommit(): void
    {
        db()->begin('my_transaction');
        db()->execute("INSERT INTO test_table (name) VALUES ('Test for Commit')");
        db()->commit('my_transaction');

        $rows = db()->select("SELECT * FROM test_table WHERE name = 'Test for Commit'", TestDto::class);
        self::assertCount(1, $rows);
    }

    public function testRollback(): void
    {
        db()->begin('test_rollback');
        db()->execute("INSERT INTO test_table (name) VALUES ('Test for Rollback')");
        db()->rollback('test_rollback');

        $rows = db()->select("SELECT * FROM test_table WHERE name = 'Test for Rollback'", TestDto::class);
        self::assertCount(0, $rows);
    }

    public function testNestedTransaction(): void
    {
        db()->begin('test_nested_transaction');
        db()->execute("INSERT INTO test_table (name) VALUES ('Test for Nested Transaction')");
        db()->begin('test_nested_transaction_inner');
        db()->execute("INSERT INTO test_table (name) VALUES ('Test for Nested Transaction Inner')");
        db()->rollback('test_nested_transaction_inner');
        db()->commit('test_nested_transaction');

        $rows = db()->select("SELECT * FROM test_table WHERE name = 'Test for Nested Transaction'", TestDto::class);
        self::assertCount(1, $rows);
    }

    public function testLikeEscape(): void
    {
        self::assertEquals('\\%', db()->likeEscape('%'));
        self::assertEquals('\\_', db()->likeEscape('_'));
        self::assertEquals('\\%\\_\\%', db()->likeEscape('%_%'));
        self::assertEquals('\\%StartMiddle\\_End', db()->likeEscape('%StartMiddle_End'));
        self::assertEquals('Test!@#$^&*()', db()->likeEscape('Test!@#$^&*()'));
        self::assertEquals('Complex\\%Test\\_Case!', db()->likeEscape('Complex%Test_Case!'));
        self::assertEquals('', db()->likeEscape(''));
        $longString = str_repeat('%_', 100);
        $expectedLongString = str_repeat('\\%\\_', 100);
        self::assertEquals($expectedLongString, db()->likeEscape($longString));
    }

    public function testIn(): void
    {
        db()->execute("INSERT INTO test_table (name) VALUES ('Test1')");
        db()->execute("INSERT INTO test_table (name) VALUES ('Test2')");
        db()->execute("INSERT INTO test_table (name) VALUES ('Test3')");

        $ids = db()->ints("SELECT id FROM test_table WHERE name IN (:names)", ['names' => ['Test2', 'Test3', 'Test99', "O'Leary"]]);
        self::assertEquals([2, 3], $ids);

        db()->execute("UPDATE test_table SET name = 'Test4' WHERE id IN (:ids)", ['ids' => [1, 3]]);
        $results = db()->select("SELECT name FROM test_table WHERE id IN (:ids)", TestDto::class, ['ids' => [1, 2, 3, 99]]);
        self::assertEquals(['Test4', 'Test2', 'Test4'], array_map(fn (TestDto $dto) => $dto->name, $results));

        $ids = db()->ints("SELECT id FROM test_table WHERE name IN (:names)", ['names' => ['Test2', "Smith, John"]]);
        self::assertEquals([2], $ids);
    }

    public function testModify(): void
    {
        db()->execute("INSERT INTO test_table (name) VALUES ('Test1')");
        db()->execute("INSERT INTO test_table (name) VALUES ('Test2')");

        $affectedRows = db()->modify('UPDATE test_table SET name = :name WHERE id = :id', ['name' => 'Test Modify', 'id' => 1]);
        self::assertEquals(1, $affectedRows);

        $affectedRows = db()->modify('DELETE FROM test_table WHERE id = :id', ['id' => 99]);
        self::assertEquals(0, $affectedRows);

        $affectedRows = db()->modify('DELETE FROM test_table WHERE id = :id', ['id' => 1]);
        self::assertEquals(1, $affectedRows);

        $affectedRows = db()->modify('UPDATE test_table SET name = :name WHERE id = :id', ['name' => 'Test Modify', 'id' => 1]);
        self::assertEquals(0, $affectedRows);
    }
}
