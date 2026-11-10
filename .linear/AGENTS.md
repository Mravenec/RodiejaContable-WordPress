# Agent guide — private.board (neutral engine)

Filesystem board. **No** cloud issue-tracker API/MCP.

**Day zero:** 0 epics. Default harness: `neutral` (edit `config.yaml` when a host project adds a stack harness). Host Spring/MariaDB/React folders are **not** in this engine — see the host `.ai/rules/spring-initializr.md` when the host uses this board.

Columns: `backlog` → `doing` (implement **one checklist item, then mark it**) → `testing` (verify) → `done` (park) → **`summarize`** (last; only after every ticket in the epic is in `done`).

The beginner HTML is a **living work log**: `epics/<n>-<slug>/summaries/<n>-<slug>-beginner.html` refreshes on claim, checklist, **evidence**, fail, and every column move. Each ticket section must show **What was built**: compact code, the command that ran, and the result (`evidence <ID>`). Without those windows every epic summary looks the same. Column `summarize` is still the wrap-up gate. Record the git command + Supervisor / Manager note with `handoff` so they appear in that HTML.

If verification fails: `fail <ID> --agent NAME --reason "…"` (from **testing** → **doing**, claim kept) or `move <ID> doing --reason "…"`. Fix in doing, then `move testing` again.

**Active time:** the board cannot see Cursor / Claude Run / Approve dialogs. It counts working hours/minutes from first `claim` until `handoff`. A gap longer than `time.idle_ms` (default 3 min) between claim / ping / checklist / move is not counted. `ping` during long commands; `clock pause` / `resume` when waiting on purpose. Parked-in-done time is not counted. Totals appear on the living HTML. UI **Hours worked** (sidebar) sums active + elapsed for every epic in a selected month; CLI `hours [--month YYYY-MM]`.

Epic % is **column + checklist**: each ticket’s share is `(checklist_done + column.order) / (checklist_total + maxOrder)`. Marking a checklist item and moving columns both raise %. **100%** only when every ticket is in `summarize` with its checklist complete.

The CLI refuses skip/batch/`all`. Do not implement the next item while the previous is still `[ ]`. Mark this board when the work is in this folder.

Commands: `summarize <id>` · `summarize-epic <n|slug>` · `handoff <n|slug> --git "…" --note-file path` · `evidence <id> --title "…" --code-file …` · `clock pulse|pause|resume <id>` · `clock status <n|id>` · `hours [--month YYYY-MM]` · `ping <id>`. Claim stays through `done` so summarize does not need `--force`. Do not summarize while siblings are still in backlog/doing/testing.

`blocked_by` is **board-wide** (cross-epic): `next` / `claim` wait until each dependency reaches `unlock_column` (default: `done`).

Optional UI ports (unusual, avoid clashes): API `47381` · UI `47382`.

**Worktrees** are created on the **host git repo**, never inside this engine:

```bash
node scripts/worktree-add.mjs APP-1-2 --repo C:\path\to\host-harness
```

That creates `.worktrees/{n}-{slug}/APP-1-2` on `ticket/{n}-{slug}/APP-1-2` (**required** after every claim) and junctions the host `.linear/epics` + `.linear/agents` (unlink those junctions **before** prune — never recurse-delete them). UI rows show `{n}-{slug}`. Epic rows (including several at 0%) are one backlog — not one tree per epic. `worktree-add` starts from committed deps (including other epics). When every ticket is in `done`:

```bash
node scripts/worktree-integrate.mjs 1 --repo C:\path\to\host-harness
```

That merge unlinks board junctions, stops java/vite holding ticket folders, then **removes** ticket folders (**fails** if leftovers remain). The host `.linear/epics` stays. After `handoff`:

```bash
node scripts/worktree-push.mjs 1 --repo C:\path\to\host-harness
```

Do not parallelize SQL + JOOQ. Cap 2–3 agents.

Ticket → `done`: **agent** `worktree-finish` (local commit, no ticket push). Epic → `summarize`: agents merge, prune APP-* folders (fail if leftovers remain), smoke, write the git line + Supervisor / Manager smoke note, then `worktree-push` (publishes `feature/{n}-{slug}`, drops `_epic` and leftover ticket folders). When every epic is on origin: `worktree-origin-ready` — do not merge `main` yet. Details: [info/worktrees.md](./info/worktrees.md).
