# private.board

Local multi-agent issue board. **No cloud API key. No MCP.** Neutral engine (`harness: neutral` in `config.yaml`).

## Day zero

```bash
cd .linear
node scripts/board.mjs epics
# → No epics. Create one from the host repo (or):
# node scripts/board.mjs epic create 1 my-feature --title "My feature"
```

Optional UI:

```bash
node scripts/api.mjs
npm run ui:install
npm run ui
# http://127.0.0.1:47382  (API :47381)
```

Optional demo only (not required):

```bash
npm run seed   # creates Epic 1 hello-board — wipe/re-run anytime
npm run smoke:columns   # column contract: testing before verify, summarize after whole epic in done
npm run smoke:living    # living HTML grows with moves; fail testing→doing; handoff git + note
npm run smoke:clock     # active work clock pauses on idle / approval wait; handoff freezes time
npm run smoke:hours     # monthly Hours worked (active + elapsed across epics)
npm run smoke:land      # land badge helper (land.json + git infer when vendored under host)
```

Land row badges: **Landed** / **On origin** / **Not landed** (never “No updates”). When this engine is vendored as `<host>/.linear`, the API also infers **Landed** if `origin/feature/{n}-{slug}` is already on `origin/main`.

## Docs

| Path | What |
|---|---|
| [AGENTS.md](./AGENTS.md) | How agents operate this board |
| [info/](./info/) | Cheatsheet + config schema + worktrees |
| [config.yaml](./config.yaml) | Team key, columns, harness |

## Board columns

`0. backlog` → `1. doing` → `2. testing` → `3. done` → `4. summarize`

- **doing** — implement one checklist item, mark it immediately, then the next (do not run the testing section here)
- **testing** — verify locally and set harness flags
- **done** — technical work accepted (unblocks dependents); **park here** until every epic ticket is in done; claim stays until summarize
- **summarize** — last wrap-up column (only after every ticket is in `done`). The HTML at `epics/<n>-<slug>/summaries/<n>-<slug>-beginner.html` is a **living work log** from the first claim/move. Each ticket must show compact **code / command / result** windows (`evidence`), not only board history. Wrap-up stores the git command + Supervisor / Manager note via `handoff`.

Epic progress (column + checklist): each ticket contributes
`(checklist_done + column.order) / (checklist_total + maxOrder)` toward the epic
average (`maxOrder` = `summarize`). Marking a checklist item raises % immediately;
column moves do too. Tickets with no checklist use columns only (backlog 0% ·
doing 25% · testing 50% · done 75% · summarize 100% of that ticket’s share).
Epic hits **100%** only when every issue is in `summarize` **and** every
checklist is complete.

```bash
node scripts/board.mjs summarize-epic <n>
node scripts/board.mjs fail <ID> --agent NAME --reason "HTTP gate failed"
node scripts/board.mjs move <ID> doing --agent NAME --reason "HTTP gate failed"
node scripts/board.mjs handoff <n> --git "cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}" --note-file summaries/handoff-note.txt
```

Board UI: **Open living summary** on the epic board (also `GET /api/epic/<id>/summary`). Sidebar **Hours worked** totals Active + Elapsed for every epic in a selected month (`GET /api/hours?month=YYYY-MM`).

## Install into another repo

Copy this folder as `.linear/`, then set `team_key` and `harness` in `config.yaml` to match `harness/<name>.mjs`.

Ticket worktrees belong on the **host git repo** (`node scripts/worktree-add.mjs APP-1-2 --repo <host>` — **required** after every claim). Nested under `{n}-{slug}` on `ticket/{n}-{slug}/{ISSUE_ID}` until integrate. Do not fork this board per branch. Agents `worktree-finish`, `worktree-integrate` (merge + prune ticket folders; fail if leftovers remain), `handoff`, then `worktree-push` (publishes `feature/{n}-{slug}`, drops `_epic` and leftover ticket folders).
