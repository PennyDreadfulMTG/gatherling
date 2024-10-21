<?php

declare(strict_types=1);

namespace Gatherling\Data;

use Gatherling\Exceptions\ConfigurationException;
use Gatherling\Exceptions\DatabaseException;
use Gatherling\Exceptions\MarshalException;
use Gatherling\Logger;
use PDOException;
use PDOStatement;
use PDO;
use TypeError;
use Gatherling\Models\Dto;

use function Gatherling\Helpers\config;
use function Gatherling\Helpers\logger;
use function Gatherling\Helpers\marshal;

// Do not access this directly, use Gatherling\Helpers\db() instead
class Db
{
    private PDO $pdo;
    private bool $connected = false;
    /** @var list<string> */
    private array $transactions = [];

    public function __construct()
    {
        $this->connect();
    }

    private function connect(bool $connectToDatabase = false): void
    {
        if (isset($this->pdo) && ($this->connected || !$connectToDatabase)) {
            return;
        }
        if (isset($this->pdo)) {
            $this->use();
            return;
        }

        try {
            $database = config()->string('db_database');
            $username = config()->string('db_username');
            $password = config()->string('db_password');
            $hostname = config()->string('db_hostname');
        } catch (MarshalException $e) {
            throw new ConfigurationException('Incorrect database configuration', 0, $e);
        }

        $dsn = 'mysql:host=' . $hostname . ';charset=utf8mb4';
        if ($connectToDatabase) {
            $dsn .= ';dbname=' . $database;
        }

        try {
            $this->pdo = new PDO($dsn, $username, $password);
            // Set explicitly despite being the default in PHP8 because we rely on this
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec("SET time_zone = 'America/New_York'");
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to connect to database: ' . $e->getMessage(), 0, $e);
        }
    }

    private function use(): void
    {
        try {
            $database = config()->string('db_database');
        } catch (MarshalException $e) {
            throw new ConfigurationException('No database name configured', 0, $e);
        }
        try {
            $this->pdo->exec('USE ' . $this->quoteIdentifier($database));
        } catch (PDOException $e) {
            throw new DatabaseException('Failed to connect to database: ' . $e->getMessage(), 0, $e);
        }
        $this->connected = true;
        return;
    }

    public function createDatabase(string $rawName): void
    {
        $dbName = $this->quoteIdentifier($rawName);
        $this->executeInternal("CREATE DATABASE IF NOT EXISTS $dbName", [], function ($sql, $_params) {
            $this->pdo->query($sql);
        }, false);
    }

    public function dropDatabase(string $rawName): void
    {
        $dbName = $this->quoteIdentifier($rawName);
        $this->executeInternal("DROP DATABASE IF EXISTS $dbName", [], function ($sql, $_params) {
            return $this->pdo->exec($sql);
        }, false);
    }

    /** @param array<string, mixed> $params */
    public function execute(string $sql, array $params = []): void
    {
        // No return here because PDO::ERROMODE_EXCEPTION means we'd throw if anything went wrong
        $this->executeInternal($sql, $params, function ($sql, $params) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        });
    }
    /** @param array<string, mixed> $params */
    public function insert(string $sql, array $params = []): int
    {
        $ids = $this->insertMany($sql, $params);
        if (count($ids) !== 1) {
            throw new DatabaseException("Expected 1 id, got " . count($ids) . " for query: $sql with params " . json_encode($params));
        }
        return $ids[0];
    }

    /**
     * @param array<string, mixed> $params
     * @return list<int>
     */
    public function insertMany(string $sql, array $params = []): array
    {
        $sql = $sql . ' RETURNING id';
        /** @var list<int> */
        return $this->executeInternal($sql, $params, function ($sql, $params) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            /** @var list<int> */
            $insertedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_map(fn (mixed $id) => intval($id), $insertedIds);
        });
    }

    /** @param array<string, mixed> $params */
    public function update(string $sql, array $params = []): int
    {
        /** @var int */
        return $this->executeInternal($sql, $params, function ($sql, $params) {
            $stmt = $this->pdo->prepare($sql);
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
    public function select(string $sql, string $class, array $params = []): array
    {
        /** @var list<T> */
        return $this->executeInternal($sql, $params, function ($sql, $params) use ($class) {
            $stmt = $this->pdo->prepare($sql);
            $this->bindParams($stmt, $params);
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
    public function selectOnly(string $sql, string $class, array $params = []): Dto
    {
        $result = $this->select($sql, $class, $params);
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
    public function selectOnlyOrNull(string $sql, string $class, array $params = []): ?Dto
    {
        $result = $this->select($sql, $class, $params);
        if (count($result) > 1) {
            throw new DatabaseException('Expected 1 row, got ' . count($result) . " for query: $sql with params " . json_encode($params));
        }
        return $result[0] ?? null;
    }

    /** @param array<string, mixed> $params */
    public function int(string $sql, array $params = []): int
    {
        try {
            return marshal($this->value($sql, $params))->int();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected int value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public function optionalInt(string $sql, array $params = []): ?int
    {
        try {
            return marshal($this->value($sql, $params))->optionalInt();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected int value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public function string(string $sql, array $params = []): string
    {
        try {
            return marshal($this->value($sql, $params))->string();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected string value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public function optionalString(string $sql, array $params = []): ?string
    {
        try {
            return marshal($this->value($sql, $params))->optionalString();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected string value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public function float(string $sql, array $params = []): float
    {
        try {
            $v = marshal($this->value($sql, $params))->float();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected float value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
        return $v;
    }

    /** @param array<string, mixed> $params */
    public function optionalFloat(string $sql, array $params = []): ?float
    {
        try {
            return marshal($this->value($sql, $params))->optionalFloat();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected float value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    public function bool(string $sql, array $params = []): bool
    {
        $v = $this->optionalInt($sql, $params);
        if ($v === null) {
            throw new DatabaseException("Expected non-null bool value for query: $sql with params " . json_encode($params));
        }
        return (bool) $v;
    }

    /** @param array<string, mixed> $params */
    public function optionalBool(string $sql, array $params = []): ?bool
    {
        $v = $this->optionalInt($sql, $params);
        if ($v === null) {
            return null;
        }
        return (bool) $v;
    }

    /** @param array<string, mixed> $params */
    private function value(string $sql, array $params = []): mixed
    {
        $values = $this->values($sql, $params);
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
    public function strings(string $sql, array $params = []): array
    {
        try {
            return marshal($this->values($sql, $params))->strings();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected strings value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return list<int>
     */
    public function ints(string $sql, array $params = []): array
    {
        try {
            return marshal($this->values($sql, $params))->ints();
        } catch (MarshalException $e) {
            throw new DatabaseException("Expected ints value for query: $sql with params " . json_encode($params) . ", got " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return list<mixed>
     */
    private function values(string $sql, array $params = []): array
    {
        /** @var list<mixed> */
        return $this->executeInternal($sql, $params, function ($sql, $params) {
            $stmt = $this->pdo->prepare($sql);
            $this->bindParams($stmt, $params);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_NUM);
            return array_column($result, 0);
        });
    }

    public function begin(string $rawName): void
    {
        $name = $this->safeName($rawName);
        logger()->debug("[DB] BEGIN $rawName ($name)");
        $isOuterTransaction = !$this->transactions;
        $this->transactions[] = $name;
        if ($isOuterTransaction) {
            try {
                $this->pdo->beginTransaction();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to begin transaction $rawName", 0, $e);
            }
        } else {
            $this->execute("SAVEPOINT $name");
        }
    }

    public function commit(string $rawName): void
    {
        $name = $this->safeName($rawName);
        logger()->debug("[DB] COMMIT $rawName ($name)");
        $numTransactions = count($this->transactions);
        if ($numTransactions === 0) {
            throw new DatabaseException("Asked to commit $name, but no transaction is open");
        }
        $latestTransaction = $this->transactions[$numTransactions - 1];
        if ($latestTransaction !== $name) {
            try {
                $this->pdo->rollback();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to rollback $latestTransaction while handling mismatch", 0, $e);
            }
            throw new DatabaseException("Asked to commit $name, but $latestTransaction is open. ROLLBACK issued.");
        }
        if (count($this->transactions) === 1) {
            try {
                $this->pdo->commit();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to commit $name", 0, $e);
            }
        }
        array_pop($this->transactions);
    }

    public function rollback(string $rawName): void
    {
        $name = $this->safeName($rawName);
        logger()->debug("[DB] ROLLBACK $rawName ($name)");
        $numTransactions = count($this->transactions);
        if ($numTransactions === 0) {
            try {
                $this->pdo->rollback();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to rollback $name while handling faulty rollback call", 0, $e);
            }
            throw new DatabaseException("Asked to rollback $name, but no transaction is open. ROLLBACK issued.");
        }
        $latestTransaction = $this->transactions[$numTransactions - 1];
        if ($latestTransaction !== $name) {
            try {
                $this->pdo->rollback();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to rollback while handling incorrect rollback", 0, $e);
            }
            throw new DatabaseException("Asked to rollback $name, but $latestTransaction is open. ROLLBACK issued.");
        }
        $isOuterTransaction = count($this->transactions) === 1;
        if ($isOuterTransaction) {
            try {
                $this->pdo->rollback();
            } catch (PDOException $e) {
                throw new DatabaseException("Failed to rollback $name", 0, $e);
            }
        } else {
            $this->execute("ROLLBACK TO SAVEPOINT $name"); // Rollback to the savepoint
        }
        array_pop($this->transactions);
    }

    public function getLock(string $name = 'lock_db', int $timeout = 0): int
    {
        $sql = 'SELECT GET_LOCK(:name, :timeout)';
        return $this->int($sql, ['name' => $name, 'timeout' => $timeout]);
    }

    public function releaseLock(string $name = 'lock_db'): void
    {
        $sql = 'SELECT RELEASE_LOCK(:name)';
        $this->execute($sql, ['name' => $name]);
    }

    public function likeEscape(string $value): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $value);
    }

    /** @param array<string, mixed> $params */
    private function bindParams(PDOStatement $stmt, array $params): void
    {
        try {
            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(':' . $key, $value);
                }
            }
        } catch (PDOException $e) {
            throw new DatabaseException("Failed to bind params " . json_encode($params), 0, $e);
        }
    }

    /** @param array<string, mixed> $params */
    private function executeInternal(string $sql, array $params, callable $operation, bool $connectToDatabase = true): mixed
    {
        $this->connect($connectToDatabase);
        $context = [];
        if ($params) {
            $context['params'] = $params;
        }
        if ($this->transactions) {
            $context['transactions'] = $this->transactions;
        }
        logger()->debug("[DB] $sql", $context);
        if ($this->transactions && $this->isDdl($sql)) {
            logger()->warning('[DB] DDL statement issued within transaction, this may cause issues.');
        }

        try {
            return $operation($sql, $params);
        } catch (PDOException $e) {
            if ($e->getCode() === '3D000') {
                logger()->warning('Database connection lost, attempting to reconnect...');
                try {
                    $stmt = $this->pdo->prepare($sql);
                    return $stmt->execute($params);
                } catch (PDOException $e) {
                    throw new DatabaseException("Failed to reconnect and execute query: $sql with params " . json_encode($params), 0, $e);
                }
            }
            $msg = "Failed to execute query: $sql with params " . json_encode($params);
            logger()->error($msg, $context);

            throw new DatabaseException($msg, 0, $e);
        }
    }

    private function safeName(string $name): string
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

    private function quoteIdentifier(string $name): string
    {
        $escapedName = str_replace('`', '``', $name);

        return "`$escapedName`";
    }

    private function isDdl(string $sql): bool
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
