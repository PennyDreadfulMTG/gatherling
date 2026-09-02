# Working on a task

For agents and humans. Read `CLAUDE.md` first for the conventions.

## Pick something

```bash
bin/tasks                            # what's left, by theme
bin/tasks list --pinned              # the ones that matter most
bin/tasks list --theme perf          # a whole batch to work through
bin/tasks list --risk low            # mechanical, easy to verify
bin/tasks show T-5155AD              # one task and its suggested branch name
bin/tasks next --theme bug           # just give me one
```

Take one task. Not three. A branch that fixes one thing gets reviewed and merged;
a branch that fixes four gets argued about and abandoned.

## Before you write anything

Work out whether the task is a bug fix, a refactor, or a new behaviour, because the
three have different rules.

**Bug fix.** Write the failing test first, in the same branch. If you cannot make a
test fail, you have not understood the bug yet — keep digging. A bug fix without a
regression test will be asked for one.

**Refactor.** The behaviour must not change. If the area has no tests, add
characterization tests *first*, in their own commit, pinning what the code does today
including anything that looks wrong. Then refactor. Then show the tests still pass
untouched. If you had to change a test to make a refactor pass, it was not a refactor —
say so explicitly.

**New behaviour.** Test the new path and the case it replaces.

## While you work

```bash
composer tests -- --filter testThing    # fast loop
bin/verify --fast                       # tests, phpstan, phpcs, ratchet
```

Stay inside the task. If you spot something else wrong — and you will, constantly —
add a line to `docs/braindump.txt` and keep going. Do not fix it. The braindump is
the right place for "while I was in there I noticed".

If the task turns out to be already done, wrong, or impossible, stop and say so.
That is a useful result. Do not invent adjacent work to fill the space.

## Before you say you are done

```bash
bin/verify
```

Everything green. Not "green apart from". If a check fails for a reason unrelated to
your change, that is still a failure and still needs reporting — do not quietly skip it.

Then:

```bash
bin/tasks done T-5155AD && bin/import-tasks
```

## The ratchet

`bin/ratchet` tracks the debt metrics in `.ratchet.json`. They may go down, never up.
CI enforces it. If your change increases one, the build fails.

```
phpstan errors suppressed by baseline
psalm errors suppressed by baseline
exit/die calls
direct superglobal accesses
files still using mysqli
== and != comparisons
echo statements outside templates
page entry points with an untestable main(): never
test methods            (floor - may only go up)
lines covered by tests  (floor - may only go up)
```

When you improve one, lock the gain in:

```bash
bin/ratchet --update
```

and commit `.ratchet.json` with the rest of your change. That is what stops the next
person undoing your work by accident.

Do not update a mark to make a regression go away. If a metric genuinely had to rise —
it happens, for instance adding a page before the routing work lands — say so plainly in
the PR description.

## Commits and PRs

One task per PR. Reference the task ID in the title:

```
T-5155AD Wrap standings calculation in a transaction
```

Say in the description what you changed, what you tested, and what you did not check.
If you made a judgement call, name it. If part of the task was out of reach, say which
part and why, rather than narrowing the task silently.

Do not commit, push, or open a PR unless you were asked to.

## Working in parallel

Several agents on this repo at once is fine and is the point. Rules that keep it fine:

- One task per branch, branch named after the task ID.
- Do not touch `phpstan-baseline.neon`, `psalm-baseline.xml` or `.ratchet.json` beyond
  what your own change requires. These are the files most likely to conflict.
- Do not regenerate `docs/TASKS.md` unless you marked something done.
- Prefer editing a file over moving it. Renames turn every other agent's diff into a
  conflict. Batch renames into their own dedicated task.
