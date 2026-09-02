# Gatherling

A website for running Magic: the Gathering tournaments.

## Setup

Install PHP 8.2+, MariaDB, Composer and the `curl`, `mysqli`, `pdo`, `pdo_mysql`,
`simplexml` and `mbstring` PHP extensions. Then:

```bash
sudo mysql_tzinfo_to_sql /usr/share/zoneinfo | sudo mysql -u root mysql
sudo mysql -u root
mysql> CREATE USER 'gatherling'@'localhost' IDENTIFIED BY 'Pa$$w0rD';
mysql> GRANT ALL ON `gatherling`.* TO 'gatherling'@'localhost';
mysql> GRANT ALL ON `gatherling_test`.* TO 'gatherling'@'localhost';
```

```bash
bin/setup
```

`bin/setup` installs dependencies, writes `gatherling/config.php`, runs migrations,
seeds card data and smoke-tests the result. It is idempotent, so re-run it whenever
something looks off. Point it at a different database with `GATHERLING_DB_HOST`,
`GATHERLING_DB_USER`, `GATHERLING_DB_PASS`, `GATHERLING_DB_NAME` and
`GATHERLING_DB_TEST_NAME`.

To serve the site, symlink `gatherling/` into your web root.

### Docker

```bash
docker-compose up
docker-compose exec web sh -c "cd /var/www && php gatherling/admin/db-upgrade.php"
```

Website on ports 80 and 81, mysql on 3307, adminer on 8080. Change them in
`docker-compose.yml`.

## Checks

```bash
bin/verify              # everything CI runs; this is the definition of done
bin/verify --fast       # tests, phpstan, phpcs, ratchet - skips the slow psalm pass
bin/verify tests psalm  # just those
```

`bin/verify` runs every check even after one fails, so you see all the problems in one
pass. It exits non-zero if anything failed.

Individual checks:

```bash
composer tests                        # PHPUnit
composer tests -- --filter testFoo    # extra args are passed through
composer coverage                     # HTML report in .coverage/
composer phpstan
composer psalm
composer lint                         # phpcs
composer autofix                      # phpcbf
composer csslint                      # stylelint
bun test                              # JS tests (needs bun install)
```

`DEBUG=1 composer tests` shows DEBUG log output for failing tests.

In a dockerized setup:

```bash
docker exec -it gatherling-web-1 sh -c "cd /var/www && vendor/bin/phpunit tests"
```

## The ratchet

`bin/ratchet` tracks technical debt metrics in `.ratchet.json`. They may go down, never
up, and CI enforces it. Improved one? Lock the gain in:

```bash
bin/ratchet --update    # then commit .ratchet.json
bin/ratchet --report    # just show the table
```

Never regenerate the phpstan or psalm baseline to make an error go away. Fix the error.

## Working on the cleanup

The long-running modernisation is tracked in `docs/TASKS.md`, generated from
`docs/braindump.txt`.

```bash
bin/fanout                      # plan N agents on non-colliding tasks
bin/fanout 5 --go               # claim them and write the prompts to .fanout/
bin/tasks                       # what's left
bin/tasks list --pinned         # the ones that matter most
bin/tasks show T-5155AD
bin/claim next --pinned         # pick one and claim it so nobody else takes it
bin/claim --list                # who's working on what
bin/claim --where               # which coordination mode is active
```

Claims are local git refs by default, which covers Conductor workspaces on one machine.
For agents that don't share a filesystem set `GATHERLING_CLAIMS=remote`.

`CLAUDE.md` has the conventions. `docs/WORKFLOW.md` has how to pick up a task and what
"done" means. `docs/PARALLEL.md` covers running several agents at once — claiming,
sharding, which lanes conflict, and what has to be done alone. Spotted something while
working? Add a line to `docs/braindump.txt` and run `bin/import-tasks` — don't get
distracted fixing it.
