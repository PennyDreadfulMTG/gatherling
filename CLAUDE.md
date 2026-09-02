# Gatherling

Magic: the Gathering tournament software. PHP 8.2+, MariaDB, Mustache templates, no framework.

The codebase is old and is being modernised deliberately, working through a backlog of
~576 tasks in `docs/TASKS.md`. Large parts already follow the conventions below. Where you
find code that does not, it is legacy: move it towards them, do not copy it.

## Start here

```bash
bin/setup                 # once per workspace; idempotent, safe to re-run
bin/claim next --pinned   # pick and claim the highest-value task
```

`bin/claim` shows you the task, its risk, and a branch name. Work only on that task.
When you think you are finished:

```bash
bin/verify                # everything CI runs. Green or you are not done.
bin/tasks done T-XXXXXX && bin/import-tasks
```

If you were given a specific task instead, `bin/claim T-XXXXXX` it and skip the picking.
If `bin/setup` fails on the database step, it prints the exact SQL to fix it — do that
rather than working around it, because most of the suite needs a real database.

If you are running somewhere that does not share a filesystem with the other agents
(Conductor Cloud, CI, another machine), set `GATHERLING_CLAIMS=remote` first, or your
claim is invisible to them. `bin/claim --where` tells you which mode you are in.

Other entry points: `bin/tasks` to browse the backlog, `docs/WORKFLOW.md` for what "done"
means, `docs/PARALLEL.md` if several agents are running at once.

Spotted something broken that is not your task? Add a line to `docs/braindump.txt` and
carry on. Do not fix it.

## Non-negotiables

These are enforced by `bin/verify` and by CI. Breaking one fails the build.

1. `declare(strict_types=1);` at the top of every PHP file.
2. Never add to `phpstan-baseline.neon` or `psalm-baseline.xml`. The ratchet check fails any
   commit that grows either file. Fix the error instead. If you genuinely cannot, say so in
   the PR rather than suppressing it.
3. Never lower `level:` in `phpstan.neon` or `errorLevel` in `psalm.xml`.
4. No new `exit`, `die`, or bare `echo` in application code. Throw an exception; the handler
   registered in `bootstrap.php` turns it into a response.
5. No new direct `$_GET` / `$_POST` / `$_SESSION` / `$_SERVER` / `$_FILES` / `$_REQUEST`
   access. Use the helpers (below). Never write to a superglobal.
6. No new `mysqli`. Use `db()`.
7. Tests must not be deleted or marked skipped to make a change pass.

## Architecture

```
gatherling/
  *.php              Page entry points. Legacy procedural. Each has main() and is
                     reached directly by URL. This layer is the least modernised
                     and has almost no test coverage.
  bootstrap.php      Autoload, config, Sentry, session, exception handler. Every
                     entry point requires this first.
  Data/Db.php        The database layer. All new queries go through this.
  Data/Setup.php     Schema/migration bootstrapping, incl. the test database.
  Models/            Domain objects. Also contains *Dto.php (see below).
  Views/Pages/       One class per page. Extends Page extends TemplateResponse
                     extends Response.
  Views/Components/  Reusable render units. Extends Component.
  Views/Response.php Base response: headers, body(), send().
  Helpers/           db(), request(), get(), post(), session(), server(), config(),
                     files(), logger(), marshal(), datetime().
  Exceptions/        GatherlingException hierarchy.
  templates/         Mustache. Components live in templates/partials/.
  styles/            CSS.
tests/               PHPUnit. Mirrors the gatherling/ tree.
```

### Request → Response

A page's `main()` builds a `Page` object and calls `send()`, which echoes and exits.
`ErrorHandler` (wired up in `bootstrap.php`) catches uncaught throwables and renders the
`Error` page with the right HTTP status.

This layer is mid-refactor. The target is a front controller with `handle(): Response` so
pages can be tested. Until that lands, page-level code is hard to test; prefer pushing logic
down into a Model or Component where it can be.

### Database

Everything goes through `db()`, which returns `Gatherling\Data\Db`. Pick the narrowest method:

- `select($sql, DtoClass::class, $params)` → `list<Dto>`
- `selectOnly(...)` → `Dto`, throws if not exactly one row
- `selectOnlyOrNull(...)` → `?Dto`
- `int` / `optionalInt` / `string` / `optionalString` / `float` / `optionalFloat` /
  `bool` / `optionalBool` → single scalar
- `ints` / `strings` → `list<int>` / `list<string>` from one column
- `insert` → `int` id, `insertMany` → `list<int>`
- `modify` → affected row count
- `execute` → no return
- `begin` / `commit` / `rollback` take a transaction name and nest

Rules:

- **Named parameters only.** `:event`, never `?`. Params are `['event' => $name]`.
- **Never interpolate user input into SQL.** Identifiers that must be interpolated go
  through `quoteIdentifier`.
- **River style.** Right-align keywords so the whitespace forms a river:

  ```php
  $sql = '
      SELECT name, start, format
        FROM events
       WHERE series = :series AND private = 0
    ORDER BY start DESC
       LIMIT 20';
  ```

- **A query inside a loop is a bug.** Write one query with a join or an `IN`. This is one of
  the most common real performance problems in this codebase.
- Multi-step writes belong in a transaction. Partially-applied writes have corrupted
  production standings before.

### Dtos

`Models/*Dto.php` are populated by `PDO::FETCH_CLASS`, so they have no constructor and their
properties are set directly. That is why psalm's `MissingConstructor` and
`PossiblyUnusedProperty` are suppressed for them. Keep them dumb: public typed properties,
no logic.

### Views

A `Component` renders `templates/partials/{lcfirst(ClassName)}.mustache` by default. A `Page`
renders `templates/{lcfirst(ClassName)}.mustache` inside `templates/page.mustache`.

- Public properties on the component are the template context. Mustache reads them directly.
- A property holding pre-escaped HTML is suffixed `Safe` and used with `{{{triple}}}`.
  Everything else uses `{{double}}` and is escaped. Getting this wrong is an XSS bug.
- A property holding a URL is suffixed `Link`; an image source is suffixed `Src`.
- Components must not query the database. Pass data in.
- Models must not know about Components.

### Input

Never touch superglobals. Use the helper matching the source:

```php
use function Gatherling\Helpers\get;
use function Gatherling\Helpers\post;

$season  = get()->optionalInt('season');
$name    = get()->string('name', '');        // second arg is the default
$players = post()->listString('players');
```

`request()` merges GET and POST. `Request` marshals and type-checks; a bad value throws
`MarshalException`. Prefer `optionalX()` over a default when absent genuinely means absent.

### Exceptions

```
Exception
└── GatherlingException (abstract, carries httpStatusCode)
    ├── BadRequestException (abstract)  → 4xx, message is shown to the user
    │   ├── NotFoundException
    │   ├── RequestException
    │   └── ValidationException
    ├── ConfigurationException
    ├── DatabaseException → NotFoundInDatabaseException
    ├── InvalidStateException
    ├── MarshalException
    └── SetMissingException
```

Bad user input is a 4xx, not a 500. If you find code that 500s on a malformed querystring,
that is a bug worth fixing. Do not use built-in `InvalidArgumentException` in new code.

`getUserMessage()` on a `BadRequestException` is shown to the public. Never put a stack trace
or anything internal in it.

## Style

- Single quotes unless the string contains a `$` you want interpolated or a `'`.
- `===` and `!==`. Never `==`. Never truthiness checks on a nullable — compare to `null`.
- PSR-12, enforced by phpcs. `composer autofix` fixes most violations.
- Prefer `DateTimeImmutable` (the `Safe\` one) over strings and timestamps for dates.
- Prefer a backed enum over magic ints and stringly-typed states.
- Prefer non-nullable properties. If a property is nullable only because construction is
  two-phase, fix the construction.
- Class names and files are PascalCase; the psr-4 root is `Gatherling\` → `gatherling/`.

## Tests

```bash
composer test                       # whole suite
vendor/bin/phpunit tests/Models     # one directory
vendor/bin/phpunit --filter testFoo # one test
DEBUG=1 composer test               # with debug log output
```

- Anything touching the database extends `Gatherling\Tests\Support\TestCases\DatabaseCase`,
  which wraps each test in a transaction and rolls it back. Do not commit test data.
- Build fixtures with `createTestEvent()` and `insertDeck()` from `DatabaseCase`, not by
  hand-writing inserts.
- Use `assertSame`, not `assertEquals`. Test classes are `final`.
- The test file mirrors the source path: `gatherling/Models/Event.php` →
  `tests/Models/EventTest.php`.
- A test that needs a distinct series/event must use a distinct name. Fixtures are shared
  within a run.

### What a good test looks like here

Most of this codebase's bugs are "this page 500s" or "this write left the database in a bad
state". Prefer tests that exercise a real path against the real database over tests that
mock. Mocking is rare here and should stay rare.

When fixing a bug, write the failing test first, in the same PR. When doing a pure refactor,
add characterization tests that pin the current behaviour before you change anything.

## Working on a task

Keep changes small and single-purpose. One task per branch. A branch that fixes a bug and
also reformats three files is much harder to review and much more likely to be reverted.

If a task turns out to be wrong, obsolete, or already done, say so and mark it rather than
inventing work to fill it. That is a useful result.

Other agents may be working in this repo at the same time. Before touching anything
outside your task's area, check `docs/PARALLEL.md` — some work (renames, schema
migrations, the front controller) has to happen alone.

Full detail in `docs/WORKFLOW.md`.
