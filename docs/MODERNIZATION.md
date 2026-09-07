# Modernization backlog

This is a dormant, repository-owned starting point for a future Gatherling cleanup
and redesign project. Ordinary feature and bug work should ignore it unless the user
explicitly asks to work from the modernization backlog.

## What is preserved

- `docs/braindump.txt` is the canonical, verbatim source of ideas.
- `docs/TASKS.md` is the generated index with stable IDs, themes, risk, and pins.
- `bin/import-tasks` regenerates the index after the braindump changes.
- `bin/tasks` queries and updates the backlog.
- `bin/claim` prevents two agents from taking the same task.
- `bin/fanout` plans non-overlapping work and can launch Conductor workspaces.

Task IDs are hashes of their text. Reordering the braindump does not change them.
Do not hand-edit task lines in `docs/TASKS.md`.

## Start the project later

Before launching a wave:

1. Confirm `bin/setup` succeeds in a new workspace.
2. Confirm `bin/verify` is green on the trunk branch.
3. Review and reprioritize `bin/tasks list --pinned`.
4. Dry-run `bin/fanout 2`; inspect both prompts and predicted files.
5. Run a two-agent pilot, review and integrate it, then increase the wave size.

Cloud agents must use remote claims so isolated sandboxes can see each other:

```bash
GATHERLING_CLAIMS=remote bin/fanout 2 --cloud --go --commit
```

Use local claims for worktrees sharing one Git repository. `bin/claim --where`
reports which mode is active.

`--commit` is explicit authorization for the launched agents to commit their work.
Without it, generated prompts prohibit commits, pushes, and pull requests.

## Rules for a cleanup wave

- One task and one concern per workspace.
- Bugs require a failing regression test before the fix.
- Refactors preserve behavior and begin with characterization tests where needed.
- Necessary test files are always allowed; coordinate before expanding into another
  task's production files.
- `bin/verify` must pass before review.
- The coordinator marks tasks done only after their changes merge, then regenerates
  `docs/TASKS.md`.
- Integrate and rebase between waves. Do not launch months of work against an old
  trunk snapshot.

## Improving the design

Broad architectural work needs one coherent direction. Use a lead design workspace
to document target boundaries, invariants, and migration order. Parallel agents can
then implement bounded slices of that design; they should not independently invent
competing architectures.

Prefer changes that create seams for later work: characterization tests, explicit
domain boundaries, transactions around invariants, typed inputs, and removal of
hidden global state. Review each slice for whether it advances the target design,
not merely whether it reduces a metric.

## Known Cloud lessons

The first five-agent pilot found several assumptions worth rechecking before the
next wave:

- the Cloud image and Composer dependencies must agree on a PHP version;
- MariaDB must start without systemd and include timezone data;
- task prediction must include likely regression-test files;
- the Conductor CLI must be discovered through `PATH`, not a Mac-only path;
- completed branches need an explicit review and integration step.

The initial pilot produced isolated fixes for `T-BA46BE`, `T-F52FA4`, `T-5155AD`,
`T-9789CF`, and `T-573C98`, but they were not pushed or merged. Those tasks therefore
remain open in the trunk backlog.
