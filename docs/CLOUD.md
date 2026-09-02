# Running this on Conductor Cloud

Cloud agents get their own Linux sandbox and their own clone. Two things follow from
that, and both are already handled:

- There is no MariaDB. `bin/setup` installs and starts one, then creates the user and
  databases. `.conductor/settings.toml` runs `bin/setup` before the agent starts.
- Local git-ref claims are invisible between sandboxes. Cloud agents need
  `GATHERLING_CLAIMS=remote`, which puts claims on `origin` where every agent can see
  them.

## One-time

```bash
conductor auth login
```

The CLI lives at `~/Library/Application Support/com.conductor.app/bin/conductor`. Add it
to your PATH if you want to type `conductor`.

Cloud reads `.conductor/settings.toml` from **the branch the workspace is created from**,
so create workspaces from a branch that has it. Right now that is
`legacy-codebase-cleanup-plan`. Once it merges, `dev`.

## Start five agents

```bash
bin/fanout 5 --cloud --go
```

That picks five open tasks whose predicted edit files do not overlap, claims each one,
writes its prompt, and creates a cloud workspace per task with the prompt as the first
message and `GATHERLING_CLAIMS=remote` set.

To do it by hand, or to check what it will do first:

```bash
bin/fanout 5                 # dry run, prints the plan and the prompts
bin/fanout 5 --go            # claim and write prompts to .fanout/, no workspaces
```

Then one workspace per prompt file:

```bash
conductor workspace create \
  --repo-url https://github.com/PennyDreadfulMTG/gatherling \
  --branch legacy-codebase-cleanup-plan \
  --name bug-5155ad \
  --message-file .fanout/3-bug-5155ad.txt \
  --env GATHERLING_CLAIMS=remote
```

`--env GATHERLING_CLAIMS=remote` is the one you must not forget. Without it a cloud
agent claims into its own throwaway sandbox, sees no other claims, and two agents can
silently do the same task. `bin/claim --where` inside a workspace tells the agent which
mode it is in and warns when local mode cannot reach the others.

## Watching them

```bash
conductor workspace list --json
conductor workspace session <workspaceId>
conductor session message <sessionId> --after <lastMessageId>
bin/claim --list              # needs GATHERLING_CLAIMS=remote locally too, to see cloud claims
```

To see cloud claims from your Mac:

```bash
GATHERLING_CLAIMS=remote bin/claim --list
```

## Collecting the work

Each agent commits to its own branch and is told not to push. To get the work off a
cloud sandbox you will need to either tell that agent to push its branch, or use the
Conductor UI's diff view. If you would rather they push from the start, change the line
in the prompt: `bin/fanout` generates "Commit to the branch bin/claim suggests. Do not
push and do not open a PR."

Merge one branch at a time. `.ratchet.json` will conflict and the merge driver resolves
it, but only in a checkout where `bin/setup` has run and registered the driver.

## Cleaning up

```bash
GATHERLING_CLAIMS=remote bin/claim --prune 2     # drop claims from agents that died
conductor workspace archive <workspaceId>
```

Claim refs on the remote are not branches, so they never show up in the GitHub branch
list. They still accumulate if agents die mid-task, hence `--prune`.

## What has not been tested

`bin/setup`'s database provisioning path — installing mariadb-server via apt, starting
it without systemd, creating the user over the root socket — has only been reasoned
about, not run. There is no Linux or Docker on the machine it was written on. If the
first cloud workspace fails during setup, that is the most likely place, and the setup
log will say which step. The rest of `bin/setup` is exercised locally on every run.

`bin/fanout --cloud` has not been run either, because the CLI was not authenticated.
The `conductor workspace create` invocation it builds is written from the CLI's own
`--help`; check the first one lands before firing off five.
