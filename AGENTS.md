# Agent guidance

Follow the user's task and the existing repository conventions. Do not start the
modernization backlog unless the user explicitly asks you to work from it.

For a task from `docs/TASKS.md`:

1. Read `docs/MODERNIZATION.md`.
2. Claim exactly one task with `bin/claim T-XXXXXX` before editing, unless the
   launch prompt says the fanout coordinator already claimed it.
3. Keep the change single-purpose. Predicted files are collision guidance, not a
   substitute for investigation; necessary tests are always in scope.
4. For a bug, write a failing regression test first. For a behavior-preserving
   refactor, add characterization coverage first when it is missing.
5. Run `bin/verify` before reporting completion.
6. Do not mark the task done in the shared backlog until its change is merged.
7. Do not commit, push, or open a pull request unless the user asks.

If you find unrelated work, add one line to `docs/braindump.txt` and continue with
your assigned task.
