# Running agents in parallel

576 open tasks is more than one agent should chew through serially. This is how to run
several without them tripping over each other.

## Just start N agents

```bash
bin/fanout            # plan 5 agents, change nothing
bin/fanout 3 --go     # claim 3 non-colliding tasks and write their prompts
```

Picks the highest-value open tasks whose predicted edit files do not overlap, claims
them, and writes a ready-to-paste prompt per agent into `.fanout/`. Make one Conductor
workspace per file and paste it in. Re-running skips anything already claimed, so a
second fanout gives you a fresh, still-disjoint set.

`--cloud --go` also creates Conductor cloud workspaces and starts them, passing
`GATHERLING_CLAIMS=remote` so their claims are visible to each other. Needs
`conductor auth login` first.

Which files a task touches is predicted from its text by `bin/_predict.py`, matching
class, function and file names against an index of the codebase. It is a heuristic and
gets maybe three quarters of them right. That is fine for separating agents, because
being wrong makes it over-cautious. It is not authoritative, so every prompt tells the
agent to flag before editing anything outside its list rather than assume the list is
complete.

Perfect file-level separation is not achievable here anyway: `Player` is referenced by
89 files and `Event` by 54, so expanding a task's footprint to everything it couples to
would make every task collide with every other. Reading a hub class is not a conflict;
only editing it is, and most fixes edit one or two files.

## Stop two agents doing the same task

`bin/claim` handles this. Which mode you want depends on whether the agents share a
filesystem.

```bash
bin/claim --where                # tells you which mode is active and who can see it
bin/claim next --pinned          # atomically pick and claim the highest-value task
bin/claim next --theme perf      # or from a lane
bin/claim T-5155AD               # a specific one
bin/claim --list                 # who holds what
bin/claim --mine                 # what this agent holds
bin/claim --release T-5155AD     # give it back
bin/claim --prune 2              # drop claims older than 2 days, for agents that died
```

Both modes are atomic. Two agents racing for one task: exactly one wins, the loser gets
exit 1 and the holder's name. Verified by racing them, in both modes.

### local (the default) — Conductor workspaces on one machine

Claims are refs under `refs/claims/` in the local `.git`. Conductor workspaces are
worktrees sharing one `.git`, so a claim made in one workspace is visible in all the
others instantly, with no network and no config. Atomicity comes from `git update-ref`
create, which fails if the ref already exists.

Nothing is pushed. `git push`, `git push --all`, `git clone` and `git fetch` all ignore
`refs/claims/` — verified, not assumed. The single exception is `git push --mirror`,
which pushes every ref; nobody should run that against a shared repo anyway, since it
also deletes remote branches. `git gc` leaves claims alone and they survive it.

**This mode does not work across machines.** Agents that do not share the `.git` see
nothing.

### remote — Conductor Cloud, CI, or separate machines

```bash
export GATHERLING_CLAIMS=remote
```

Claims become refs under `refs/claims/` on `origin`, so every agent that can reach the
remote sees them. Atomicity comes from git rejecting a push to an existing ref as
non-fast-forward: claim refs point at blobs, and blobs have no ancestry, so a second
claimant is always refused.

The cost is a network round trip per claim and writes to the shared remote. Set
`GATHERLING_CLAIMS_REMOTE` to use something other than `origin`, for instance a scratch
repo if you would rather not put claim refs on the main one.

Claim refs on the remote are invisible to normal work — they are not branches, so they
do not appear in the GitHub branch list or in `git branch -a`. Clean up abandoned ones
with `bin/claim --prune`.

### shard — no coordination at all

```bash
bin/tasks list --shard 1/4       # agent 1
bin/tasks list --shard 2/4       # agent 2, guaranteed disjoint
```

Deterministic partition by ID hash. No network, no shared filesystem, no possibility of
collision, nothing to clean up. Verified disjoint and covering every open task.

The trade-off is that it is static: an agent that finishes its shard early cannot take
work from another's. Good for a fixed fan-out of cloud agents, worse for a long-running
pool. Use remote claims when you want work-stealing, shards when you want simplicity.

## Stop two agents conflicting in git

Three files would otherwise conflict on nearly every merge. All three are handled:

| file | why it conflicts | handling |
| --- | --- | --- |
| `.ratchet.json` | every branch that improves a metric edits it | custom merge driver, keeps the better side of each metric. Registered by `bin/setup`. |
| `docs/TASKS.md` | generated; regenerated whenever anything is ticked off | `merge=union` in `.gitattributes`; if it still conflicts, delete it and run `bin/import-tasks` |
| `phpstan-baseline.neon`, `psalm-baseline.xml` | branches remove different entries | usually merges fine since removals are in different hunks. If it conflicts, take both removals. **Never** resolve by regenerating the baseline. |

The merge driver is real, not theoretical: branch A removing 21 `exit` calls and branch B
adding 75 tests merge with no conflict and both gains intact.

## Lanes

The themes map onto different parts of the tree, so running one agent per theme keeps
file overlap low. It is not zero — the overlaps are called out.

| lane | mostly touches | overlaps with |
| --- | --- | --- |
| `ui` (50) | `styles/`, `templates/` | `views` |
| `views` (78) | `Views/Components/` (155 files), `templates/` (174) | `ui` |
| `db` (81) | `Data/`, `Models/` | `perf` |
| `perf` (14) | `Models/`, mostly the query sites | `db` |
| `errors` (45) | `Exceptions/`, top-level pages | `arch` |
| `infra` (38) | `.github/`, `bin/`, config, logging | rarely anything |
| `test` (22) | `tests/` | rarely anything |
| `types` (44) | everywhere | everything |
| `arch` (174) | everywhere | everything |

Practical reading of that table:

- `ui`, `infra` and `test` are the safest lanes to run concurrently with anything.
- Do not run `db` and `perf` at the same time. Both live in `Models/`, and
  `Event.php` (1746 lines), `Format.php` (1583) and `Player.php` (1254) are where
  they will meet.
- `types` and `arch` are cross-cutting. Run at most one of them at a time, and
  prefer to run it when few other agents are active.
- Four to six concurrent agents is a sensible ceiling. The limit is not the tooling,
  it is how many PRs you can actually review.

## Do these one at a time, alone

Some work touches so much that parallelising it guarantees pain:

- **The front controller** (`T-2828A7`). It rewrites `main(): never` in all 31 page
  entry points. Nothing else should be in flight in `gatherling/*.php` while it lands.
- **Any rename or file move.** Renames turn every other agent's diff into a conflict.
  Batch renames into one dedicated task and run it alone.
- **Schema migrations.** Two migrations racing gives you two conflicting version
  numbers and a broken `db-upgrade`.

## Order of work

**Wave 1 — now, in parallel.** The eight pinned production bugs. They are independent
of each other and independent of the refactoring. Every one gets a failing test first.
That is also how the test suite starts growing.

**Wave 2 — the seam, alone.** The front controller and killing the 33 `exit` calls, so
pages can be called from a test and return a `Response`. Watch `legacy_page_main` in the
ratchet go from 31 to 0. Nothing else large in flight.

**Wave 3 — wide parallel.** Page smoke tests and model characterization tests, one agent
per area. This is where `covered_lines` moves off 2236 and where the remaining ~550 tasks
become safe to attempt.

**Wave 4 — the long tail.** Lanes as above, indefinitely, until `bin/tasks` runs out.

Do not start wave 3 before wave 2. Writing page tests against `main(): never` means
testing through the superglobals and `exit`, and all of it gets thrown away.

## Reviewing the output

Every branch must have `bin/verify` green before you look at it. Beyond that, the two
things worth checking by hand:

- Did the ratchet actually move? A refactor branch that changes 400 lines and moves no
  metric is usually churn.
- For a refactor, were any existing tests modified? They should not have been. If they
  were, it was not a refactor.
