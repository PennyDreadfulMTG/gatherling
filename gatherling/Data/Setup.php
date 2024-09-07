<?php

declare(strict_types=1);

namespace Gatherling\Data;

use Gatherling\Exceptions\ConfigurationException;
use Gatherling\Exceptions\DatabaseException;
use Gatherling\Exceptions\FileNotFoundException;
use Gatherling\Log;

require_once __DIR__ . '/../lib.php';

// Handles getting the database into the right state.
//
// If you start with nothing it will create the database, load the latest schema checkpoint from schema.sql
// and then run any migrations that are for version after the version recorded in schema.sql.
//
// There used to be a lot more migrations, but they became unwieldy so the database was checkpointed
// into schema.sql at version 51. If you ever want to do this again it looks something like this:
//
// $ export OUTFILE=gatherling/Data/sql/schema.sql
// $ export DATABASE=gatherling
// $ export FORMATSWHERE="name IN ('Standard', 'Modern', 'Penny Dreadful')"
// $ mysqldump --no-data --single-transaction gatherling>$OUTFILE
// $ mysqldump --no-create-info --single-transaction $DATABASE archetypes db_version client >>$OUTFILE
// $ mysqldump --no-create-info --single-transaction $DATABASE formats --where=$FORMATSWHERE >>$OUTFILE
//
// The first command dumps the schema, the second command makes sure the archetypes, client and
// db_version table are populated, and the third dumps the data for the most common formats.
//
// If you need to see the old migrations for any reason use git to investigate
// gatherling/admin/db-upgrade.php where they used to live.
//
// To add a migration create a pure SQL file in gatherling/Data/sql/migrations with the next available version number.
//
// See: admin/db-upgrade.php for a script that runs this stuff (web or cli).
//
// Setup can also bootstrap a test database. This is used in the tests. The test database checkpoint contains data
// as well as the schema, which the tests rely upon. See DatabaseTestListener for more about how this works.
// If you need to ADD something to the test database, then restore test-db.sql as your local, make your changes,
// and re-dump it and commit the result.

class Setup
{
    public static function setupDatabase(): void
    {
        Log::info('Initializing database');
        self::create();
        if (self::version() === 0) {
            self::restoreDump(__DIR__ . '/sql/schema.sql');
        }
        self::runMigrations();
    }

    // As well as setting up the test database this will "switch" you to the
    // test db for all future db queries in this request.
    public static function setupTestDatabase(): void
    {
        Log::info('Setting up test database');
        self::activateTestDatabase();
        self::dropTestDatabase();
        self::create();
        self::restoreDump(__DIR__ . '/sql/test-db.sql');
        self::runMigrations();
    }

    public static function dropTestDatabase(): void
    {
        global $CONFIG;

        Log::info('Dropping test database');
        DB::dropDatabase($CONFIG['db_test_database']);
    }

    private static function activateTestDatabase(): void
    {
        global $CONFIG;

        $toCopy = [
            'db_test_hostname' => 'db_hostname',
            'db_test_username' => 'db_username',
            'db_test_password' => 'db_password',
            'db_test_database' => 'db_database',
        ];
        Log::info('Activating test database. Future db calls will be made against the test database.');
        foreach ($toCopy as $from => $to) {
            if (!isset($CONFIG[$from])) {
                $msg = 'Test database is not configured. Current config: ' . json_encode($CONFIG);
                throw new ConfigurationException($msg);
            }
            $CONFIG[$to] = $CONFIG[$from];
        }
    }

    // Creates the database if it doesn't exist.
    private static function create(): void
    {
        global $CONFIG;

        Log::info('Creating database if necessary');
        DB::createDatabase($CONFIG['db_database']);
    }

    private static function restoreDump(string $path): void
    {
        global $CONFIG;

        if ($CONFIG['env'] === 'prod') {
            throw new DatabaseException('Refusing to restore dump in production environment');
        }
        $s = file_get_contents($path);
        if (!$s) {
            throw new FileNotFoundException("Dump file not found: $path");
        }
        $commands = explode(';', $s);
        foreach ($commands as $sql) {
            DB::execute($sql);
        }
        Log::info('Database restored from dump.');
    }

    private static function findMigrations(int $version): array
    {
        $migrationDirectory = __DIR__ . '/sql/migrations';
        $migrations = [];
        foreach (scandir($migrationDirectory) as $file) {
            if (!preg_match('/^[1-9]\d*\.sql$/', $file)) {
                continue;
            }
            $fileVersion = filter_var(basename($file, '.sql'), FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            if ($fileVersion === null) {
                throw new \InvalidArgumentException("Invalid migration filename: $file");
            }
            if ($fileVersion > $version) {
                $path = $migrationDirectory . DIRECTORY_SEPARATOR . $file;
                Log::debug("Loading migration $fileVersion from $path");
                $sql = file_get_contents($path);
                if (!$sql) {
                    throw new FileNotFoundException("Failed to read migration file: $path");
                }
                $migrations[] = new Migration($fileVersion, $sql);
            }
        }
        usort($migrations, function ($a, $b) {
            return $a->version <=> $b->version;
        });
        return $migrations;
    }

    private static function runMigrations(): void
    {
        $version = self::version();
        $migrations = self::findMigrations($version);
        Log::info('Found ' . count($migrations) . ' pending migrations');
        foreach ($migrations as $migration) {
            Log::info("Migration {$migration->version}: {$migration->sql}");
            DB::execute($migration->sql);
            DB::execute("UPDATE db_version SET version = :version", ['version' => $migration->version]);
        }
    }

    private static function version(): int
    {
        try {
            $v = DB::value('SELECT version FROM db_version LIMIT 1');
            return is_int($v) ? $v : 0;
        } catch (DatabaseException) {
            Log::debug('No version found in db_version table');
            return 0;
        }
    }
}
