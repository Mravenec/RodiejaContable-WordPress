# Agent cheatsheet

```bash
node scripts/board.mjs epic create <n> <slug> --title "…"
node scripts/board.mjs issue create <epic> --title "…" --role database
node scripts/board.mjs next --role backend --epic <n>
node scripts/board.mjs claim <ID> --agent <name>
node scripts/board.mjs checklist <ID> <n> --agent <name>
node scripts/board.mjs evidence <ID> --title SQL --path file --lang sql --code-file snippet.sql --agent <name>
node scripts/board.mjs move <ID> testing --agent <name>
# if verification fails (claim kept):
node scripts/board.mjs fail <ID> --agent <name> --reason "HTTP gate failed"
# commit then park (host):
node scripts/worktree-finish.mjs <ID> --agent <name> --repo C:\path\to\host-harness
node scripts/board.mjs move <ID> summarize --agent <name>
node scripts/board.mjs summarize-epic <n>
node scripts/board.mjs handoff <n> --git "cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}" --note-file summaries/handoff-note.txt
node scripts/board.mjs ping <ID> --agent <name>
node scripts/board.mjs clock pause <ID> --reason "waiting for approval"
node scripts/board.mjs clock resume <ID> --agent <name>
node scripts/board.mjs clock status <n>
node scripts/board.mjs hours [--month YYYY-MM]
node scripts/board.mjs progress --epic <n>
```

Flow: `backlog` → `doing` (do item N, then `checklist <ID> N`, repeat) → `testing` (verify) → `done` (park) → **`summarize`** (only after every ticket in the epic is in done)

Checklist: one item per command, in order, immediately after that step. CLI refuses skip/batch.

Living HTML per epic: `epics/<n>-<slug>/summaries/<n>-<slug>-beginner.html`. Ticket sections include **What was built** (compact code / command / result via `evidence <ID>`). Refreshes on every move and evidence pulse. Wrap-up: `handoff` writes the git command + Supervisor / Manager note. UI: **Open living summary**.

Epic % is **column + checklist** (backlog→doing→testing→done→summarize, plus each marked checklist item). Dependents unlock at `done` (or later). Claim stays through `done` so summarize does not need `--force`. Do not summarize while siblings are still open.

`blocked_by` resolves **across epics** (board-wide), not only within one epic.

Large briefs: create all epics + tickets before claims (repo root `.ai/rules/orchestrate-first.md`).

Host worktrees (shared board — not this folder). **Required** after every claim:

```bash
node scripts/worktree-add.mjs APP-1-2 --repo C:\path\to\host-harness
node scripts/worktree-finish.mjs APP-1-2 --agent backend-agent-1 --repo C:\path\to\host-harness
node scripts/worktree-integrate.mjs 1 --repo C:\path\to\host-harness
node scripts/worktree-push.mjs 1 --repo C:\path\to\host-harness
node scripts/worktree-rm.mjs APP-1-2 --repo C:\path\to\host-harness
```

Ticket folder: `.worktrees/{n}-{slug}/APP-1-2` on `ticket/{n}-{slug}/APP-1-2` until integrate. Agents `worktree-finish` (local commit, no ticket push). Integrate after every ticket is in `done` (merge, prune APP-* folders; **fail** if leftovers remain). `handoff` records the git line + smoke note. Then `worktree-push` publishes `feature/{n}-{slug}` and drops `_epic` plus leftover ticket folders.
