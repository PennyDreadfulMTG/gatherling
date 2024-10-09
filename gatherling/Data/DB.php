<?php

declare(strict_types=1);

namespace Gatherling\Data;

use Gatherling\Log;
use Gatherling\Exceptions\DatabaseException;
use Gatherling\Exceptions\ConfigurationException;
use Gatherling\Models\DTO;
use PDO;
use PDOException;

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
        $dsn = 'mysql:host='.$CONFIG['db_hostname'].';charset=utf8mb4';
        if ($connectToDatabase) {
            $dsn .= ';dbname='.$CONFIG['db_database'];
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
        self::_execute("CREATE DATABASE IF NOT EXISTS $dbName", [], function ($sql, $params) use ($dbName) {
            $stmt = self::connect(false)->pdo->prepare($sql);
            $stmt->execute($params);
            $stmt = self::connect(false)->pdo->prepare("USE $dbName");
            $stmt->execute();
        }, false);
    }

    public static function dropDatabase(string $rawName): void
    {
        $dbName = self::quoteIdentifier($rawName);
        self::_execute("DROP DATABASE IF EXISTS $dbName", [], function ($sql, $params) {
            $stmt = self::connect(false)->pdo->prepare($sql);

            return $stmt->execute($params);
        }, false);
    }

    public static function execute(string $sql, mixed $params = []): void
    {
        // No return here because PDO::ERROMODE_EXCEPTION means we'd throw if anything went wrong
        self::_execute($sql, $params, function ($sql, $params) {
            $stmt = self::connect()->pdo->prepare($sql);
            $stmt->execute($params);
        });
    }

    /**
     * @template T of DTO
     * @param class-string<T> $class
     * @param array<string, mixed> $params
     * @return list<T>
     */
    public static function select(string $sql, string $class, array $params = []): array
    {
        /** @var list<T> */
        return self::_execute($sql, $params, function ($sql, $params) use ($class) {
            $stmt = self::connect()->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(':' . $key, $value);
                }
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_CLASS, $class);
            return $rows;
        });
    }

    /**
     * @template T of DTO
     * @param class-string<T> $class
     * @param array<string, mixed> $params
     * @return T
     */
    public static function selectOnly(string $sql, string $class, array $params = []): DTO
    {
        $result = self::select($sql, $class, $params);
        if (count($result) !== 1) {
            throw new DatabaseException('Expected 1 row, got ' . count($result) . " for query: $sql");
        }
        return $result[0];
    }

    /**
     * @template T of DTO
     * @param class-string<T> $class
     * @param array<string, mixed> $params
     * @return T|null
     */
    public static function selectOnlyOrNull(string $sql, string $class, array $params = []): ?DTO
    {
        $result = self::select($sql, $class, $params);
        if (count($result) > 1) {
            throw new DatabaseException('Expected 1 row, got ' . count($result) . " for query: $sql");
        }
        return $result[0] ?? null;
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function value(string $sql, array $params = [], bool $missingOk = false): mixed
    {
        return self::_execute($sql, $params, function ($sql, $params) use ($missingOk) {
            $stmt = self::connect()->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_NUM);
            if ($result === false && !$missingOk) {
                throw new DatabaseException("Failed to fetch value for $sql");
            }
            return $result[0] ?? null;
        });
    }

    /**
     * @param array<string, mixed> $params
     * @param 'bool'|'int'|'float'|'string' $type
     * @return list<($type is 'bool'? bool : ($type is 'int' ? int : ($type is 'float' ? float : string)))>
     */
    public static function values(string $sql, string $type, array $params = []): array
    {
        /** @var list<($type is 'bool'? bool : ($type is 'int' ? int : ($type is 'float' ? float : string)))> */
        return self::_execute($sql, $params, function ($sql, $params) {
            $stmt = self::connect()->pdo->prepare($sql);
            $stmt->execute($params);
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
            self::execute('SET autocommit=0');
            self::execute('BEGIN');
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
            self::execute('ROLLBACK');

            throw new DatabaseException("Asked to commit $name, but $latestTransaction is open. ROLLBACK issued.");
        }
        if (count(self::connect()->transactions) === 1) {
            self::execute('COMMIT');
        }
        array_pop(self::connect()->transactions);
    }

    public static function rollback(string $rawName): void
    {
        $name = self::safeName($rawName);
        Log::debug("[DB] ROLLBACK $rawName ($name)");
        $numTransactions = count(self::connect()->transactions);
        if ($numTransactions === 0) {
            DB::execute('ROLLBACK');
            throw new DatabaseException("Asked to rollback $name, but no transaction is open. ROLLBACK issued.");
        }
        $latestTransaction = self::connect()->transactions[$numTransactions - 1];
        if ($latestTransaction !== $name) {
            DB::execute('ROLLBACK');

            throw new DatabaseException("Asked to rollback $name, but $latestTransaction is open. ROLLBACK issued.");
        }
        $isOuterTransaction = count(self::connect()->transactions) === 1;
        if ($isOuterTransaction) {
            self::execute('ROLLBACK'); // Rollback the whole transaction
            self::execute('SET autocommit=1');
        } else {
            self::execute("ROLLBACK TO SAVEPOINT $name"); // Rollback to the savepoint
        }
        array_pop(self::connect()->transactions);
    }

    /**
     * @param array<string, mixed> $params
     */
    private static function _execute(string $sql, array $params, callable $operation, bool $connectToDatabase = true): mixed
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
            $msg = "Failed to execute query: $sql";
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
            $safeName = 'sp_'.$safeName;
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
