<?php

declare(strict_types=1);

namespace Gatherling\Data;

use PDO;
use TypeError;
use PDOException;
use PDOStatement;
use Gatherling\Log;
use Gatherling\Models\Dto;
use function Gatherling\Helpers\marshal;
use Gatherling\Exceptions\MarshalException;

use Gatherling\Exceptions\DatabaseException;
use Gatherling\Exceptions\ConfigurationException;

class DB
{
    private static ?DB $db = null;

    /**
     * @param list<string> $transactions
     */
    public function __construct(private PDO $pdo, private array $transactions = [])
    {
    }

    private static function connect(bool $connectToDatabase = true): DB
    {
        global $CONFIG;

        if (self::$db !== null) {
            return self::$db;
        }

        $requiredKeys = ['db_database', 'db_username', 'db_password'];
        foreach ($requiredKeys as $key) {
            if (!isset($CONFIG[$key])) {
                throw new ConfigurationException("Missing configuration key: $key");
            }
        }
        $dsn = 'mysql:host=' . $CONFIG['db_hostname'] . ';charset=utf8mb4';
        if ($connectToDatabase) {
            $dsn .= ';dbname=' . $CONFIG['db_database'];
        }

        try {
            $pdo = new PDO($dsn, $CONFIG['db_username'], $CONFIG['db_password']);
            // Set explicitly despite being the default in PHP8 because we rely on this
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$db = new self($pdo);
            self::execute('SET time_zone = ?', ['America/New_York']);

            return self::$db;
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to connect to database', 0, $e);
        }
    }

    public static function createDatabase(string $rawName): void
    {
        $dbName = self::quoteIdentifier($rawName);
        self::executeInternal("CREATE DATABASE IF NOT EXISTS $dbName", [], function ($sql, $params) use ($dbName) {
            $stmt = self::connect(false)->pdo->prepare($sql);
            $stmt->execute($params);
            $stmt = self::connect(false)->pdo->prepare("USE $dbName");
            $stmt->execute();
        }, false);
    }

    public static function dropDatabase(string $rawName): void
    {
        $dbName = self::quoteIdentifier($rawName);
        self::executeInternal("DROP DATABASE IF EXISTS $dbName", [], function ($sql, $params) {
            $stmt = self::connect(false)->pdo->prepare($sql);
            return $stmt->execute($params);
        }, false);
    }

    public static function execute(string $sql, mixed $params = []): void
    {
        // No return here because PDO::ERROMODE_EXCEPTION means we'd throw if anything went wrong
        self::executeInternal($sql, $params, function ($sql, $params) {
            $stmt = self::connect()->pdo->prepare($sql);
            $stmt->execute($params);
        });
    }
    /** @param array<string, mixed> $params */
    public static function insert(string $sql, array $params = []): int
    {
        $ids = self::insertMany($sql, $params);
        if (count($ids) !== 1) {
            throw new DatabaseException("Expected 1 id, got " . count($ids) . " for query: $sql with params " . json_encode($params));
        }
        return $ids[0];
    }

    /**
     * @param array<string, mixed> $params
     * @return list<int>
     */
    public static function insertMany(string $sql, array $params = []): array
    {
        $sql = $sql . ' RETURNING id';
        /** @var list<int> */
        return self::executeInternal($sql, $params, function ($sql, $params) {
            $stmt = self::connect()->pdo->prepare($sql);
            $stmt->execute($params);
            /** @var list<int> */
            $insertedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_map(fn (mixed $id) => intval($id), $insertedIds);
        });
    }

    /** @param array<string, mixed> $params */
    public static function update(string $sql, array $params = []): int
    {
        /** @var int */
        return self::executeInternal($sql, $params, function ($sql, $params) {
            $stmt = self::connect()->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        });
    }

    /**
     * @template T of Dto
     * @param class-string<T> $class
     * @param array<string, mixed> $params
     * @return list<T>
     */
    public static function select(string $sql, string $class, array $params = []): array
    {
        /** @var list<T> */
        return self::executeInternal($sql, $params, function ($sql, $params) use ($class) {
            $stmt = self::connect()->pdo->prepare($sql);
            self::bindParams($stmt, $params);
            $stmt->execute();
            try {
                $rows = $stmt->fetchAll(PDO::FETCH_CLASS, $class);
            } catch (TypeError $e) {
                throw new DatabaseException("Failed to fetch class $class for query: $sql with params " . json_encode($params), 0, $e);
            }
            return $rows;
        });
    }

    /**
     * @template T of Dto
     * @param class-string<T> $class
     * @param array<string, mixed> $params
     * @return T
     */
    public static function selectOnly(string $sql, string $class, array $params = []): Dto
    {
        $result = self::select($sql, $class, $params);
        if (count($result) !== 1) {
            throw new DatabaseException('Expected 1 row, got ' . count($result) . " for query: $sql with params " . json_encode($params));
        }
        return $result[0];
    }

    /**
     * @template T of Dto
     * @param class-string<T> $class
     * @param array<string, mixed> $params
     * @return T|null
     */
    public static function selectOnlyOrNull(string $sql, string $class, array $params = []): ?Dto
    {
        $result = self::select($sql, $class, $params);
        if (count($result) > 1) {
            throw new DatabaseException('Expected 1 row, got ' . count($result) . " for query: $sql with params " . json_encode($params));
        }
        return $result[0] ?? null;
    }

    /** @param array<string, mixed> $params */
    public static function int(string $sql, array $params = []): int
    {
        try {
            return marshal(self::value($sql, $params))->int();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected int value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public static function optionalInt(string $sql, array $params = []): ?int
    {
        try {
            return marshal(self::value($sql, $params))->optionalInt();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected int value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public static function string(string $sql, array $params = []): string
    {
        try {
            return marshal(self::value($sql, $params))->string();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected string value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public static function optionalString(string $sql, array $params = []): ?string
    {
        try {
            return marshal(self::value($sql, $params))->optionalString();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected string value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public static function float(string $sql, array $params = []): float
    {
        try {
            $v = marshal(self::value($sql, $params))->float();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected float value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
        return $v;
    }

    /** @param array<string, mixed> $params */
    public static function optionalFloat(string $sql, array $params = []): ?float
    {
        try {
            return marshal(self::value($sql, $params))->optionalFloat();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected float value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public static function bool(string $sql, array $params = []): bool
    {
        $v = self::optionalInt($sql, $params);
        if ($v === null) {
            throw new DatabaseException("Expected non-null bool value for query: $sql with params " . json_encode($params));
        }
        return (bool) $v;
    }

    /** @param array<string, mixed> $params */
    public static function optionalBool(string $sql, array $params = []): ?bool
    {
        $v = self::optionalInt($sql, $params);
        if ($v === null) {
            return null;
        }
        return (bool) $v;
    }

    /** @param array<string, mixed> $params */
    private static function value(string $sql, array $params = []): mixed
    {
        $values = self::values($sql, $params);
        if (count($values) > 1) {
            throw new DatabaseException("Expected 1 value, got " . count($values) . " for query: $sql with params " . json_encode($params));
        }
        if (count($values) === 0) {
            return null;
        }
        return $values[0];
    }

    /**
     * @param array<string, mixed> $params
     * @return list<string>
     */
    public static function strings(string $sql, array $params = []): array
    {
        try {
            return marshal(self::values($sql, $params))->strings();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected strings value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return list<int>
     */
    public static function ints(string $sql, array $params = []): array
    {
        try {
            return marshal(self::values($sql, $params))->ints();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected ints value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return list<mixed>
     */
    private static function values(string $sql, array $params = []): array
    {
        /** @var list<mixed> */
        return self::executeInternal($sql, $params, function ($sql, $params) {
            $stmt = self::connect()->pdo->prepare($sql);
            self::bindParams($stmt, $params);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_NUM);
            return array_column($result, 0);
        });
    }

    public static function begin(string $rawName): void
    {
        $name = self::safeName($rawName);
        Log::debug("[DB] BEGIN $rawName ($name)");
        $isOuterTransaction = !self::connect()->transactions;
        self::connect()->transactions[] = $name;
        if ($isOuterTransaction) {
            try {
                self::connect()->pdo->beginTransaction();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to begin transaction $rawName", 0, $e);
            }
        } else {
            self::execute("SAVEPOINT $name");
        }
    }

    public static function commit(string $rawName): void
    {
        $name = self::safeName($rawName);
        Log::debug("[DB] COMMIT $rawName ($name)");
        $numTransactions = count(self::connect()->transactions);
        if ($numTransactions === 0) {
            throw new DatabaseException("Asked to commit $name, but no transaction is open");
        }
        $latestTransaction = self::connect()->transactions[$numTransactions - 1];
        if ($latestTransaction !== $name) {
            try {
                self::connect()->pdo->rollback();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to rollback $latestTransaction while handling mismatch", 0, $e);
            }
            throw new DatabaseException("Asked to commit $name, but $latestTransaction is open. ROLLBACK issued.");
        }
        if (count(self::connect()->transactions) === 1) {
            try {
                self::connect()->pdo->commit();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to commit $name", 0, $e);
            }
        }
        array_pop(self::connect()->transactions);
    }

    public static function rollback(string $rawName): void
    {
        $name = self::safeName($rawName);
        Log::debug("[DB] ROLLBACK $rawName ($name)");
        $numTransactions = count(self::connect()->transactions);
        if ($numTransactions === 0) {
            try {
                self::connect()->pdo->rollback();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to rollback $name while handling faulty rollback call", 0, $e);
            }
            throw new DatabaseException("Asked to rollback $name, but no transaction is open. ROLLBACK issued.");
        }
        $latestTransaction = self::connect()->transactions[$numTransactions - 1];
        if ($latestTransaction !== $name) {
            try {
                self::connect()->pdo->rollback();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to rollback while handling incorrect rollback", 0, $e);
            }
            throw new DatabaseException("Asked to rollback $name, but $latestTransaction is open. ROLLBACK issued.");
        }
        $isOuterTransaction = count(self::connect()->transactions) === 1;
        if ($isOuterTransaction) {
            try {
                self::connect()->pdo->rollback();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to rollback $name", 0, $e);
            }
        } else {
            self::execute("ROLLBACK TO SAVEPOINT $name"); // Rollback to the savepoint
        }
        array_pop(self::connect()->transactions);
    }

    public static function getLock(string $name = 'lock_db', int $timeout = 0): int
    {
        $sql = 'SELECT GET_LOCK(:name, :timeout)';
        return self::int($sql, ['name' => $name, 'timeout' => $timeout]);
    }

    public static function releaseLock(string $name = 'lock_db'): void
    {
        $sql = 'SELECT RELEASE_LOCK(:name)';
        self::execute($sql, ['name' => $name]);
    }

    /** @param array<string, mixed> $params */
    private static function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $key, $value);
            }
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private static function executeInternal(string $sql, array $params, callable $operation, bool $connectToDatabase = true): mixed
    {
        $context = [];
        if ($params) {
            $context['params'] = $params;
        }
        $transactions = self::connect($connectToDatabase)->transactions;
        if ($transactions) {
            $context['transactions'] = $transactions;
        }
        Log::debug("[DB] $sql", $context);
        if ($transactions && self::isDdl($sql)) {
            Log::warning('[DB] DDL statement issued within transaction, this may cause issues.');
        }

        try {
            return $operation($sql, $params);
        } catch (PDOException $e) {
            if ($e->getCode() === '3D000') {
                Log::warning('Database connection lost, attempting to reconnect...');
                $stmt = self::connect($connectToDatabase)->pdo->prepare($sql);
                return $stmt->execute($params);
            }
            $msg = "Failed to execute query: $sql with params " . json_encode($params);
            Log::error($msg, $context);

            throw new DatabaseException($msg, 0, $e);
        }
    }

    private static function safeName(string $name): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
        if ($safeName === null) {
            throw new DatabaseException("Failed to safely name $name");
        }
        $safeName = trim($safeName, '_');
        if (empty($safeName) || is_numeric($safeName[0])) {
            $safeName = 'sp_' . $safeName;
        }

        return $safeName;
    }

    private static function quoteIdentifier(string $name): string
    {
        $escapedName = str_replace('`', '``', $name);

        return "`$escapedName`";
    }

    private static function isDdl(string $sql): bool
    {
        $ddlPatterns = [
            '/^\s*CREATE\s+(TABLE|DATABASE|INDEX|VIEW|PROCEDURE|FUNCTION|TRIGGER)/i',
            '/^\s*ALTER\s+(TABLE|DATABASE|VIEW|PROCEDURE|FUNCTION|TRIGGER)/i',
            '/^\s*DROP\s+(TABLE|DATABASE|INDEX|VIEW|PROCEDURE|FUNCTION|TRIGGER)/i',
            '/^\s*TRUNCATE\s+TABLE/i',
            '/^\s*RENAME\s+TABLE/i',
        ];

        foreach ($ddlPatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return true;
            }
        }

        return false;
    }
}
