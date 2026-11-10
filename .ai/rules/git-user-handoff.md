---
description: Agents commit, merge, record summarize HTML, then push feature/{n}-{slug} and drop _epic
alwaysApply: true
---

# Git user handoff (mandatory)

Agents **commit, merge, record the living HTML, push `feature/{n}-{slug}`, and drop `_epic`**. Ordinary people must not run `git merge`, `git restore`, or ticket commits. They read the living HTML (git line + Supervisor / Manager smoke note) after the agent has published the branch.

See also: `.ai/rules/no-git-push.md` (named epic push only) · `.ai/rules/worktrees.md` · `.ai/rules/english-only.md`.

---

## Who does what

| Actor | Does | Never |
|---|---|---|
| Agent | `worktree-commit` / `worktree-finish`; `worktree-integrate` (merge, then prune ticket folders); `handoff` (git line + Supervisor / Manager smoke note into the living HTML); `worktree-push` (publish `feature/{n}-{slug}`, then drop `_epic`) | `--force`; `HEAD`; ticket branches; asking the human to merge, restore, or push |
| Human | Reads `summaries/{n}-{slug}-beginner.html` (recorded push line + smoke walkthrough). Confirms on the remote if they want. | Ticket commits; `git merge`; nested `.worktrees` paths |

**Forbidden for agents:** `git push --force`, `git push origin HEAD`, pushing `ticket/` branches.

**Allowed for agents (required):** `git add` / `git commit` via worktree scripts; `git merge` inside `worktree-integrate`; after `handoff`, `worktree-push` (named epic branch only).

Do **not** paste merge recipes for the human. If a script fails, the agent fixes it (commit the dirty dep, resolve conflict, re-run the script).

---

## Moments

| When | Agent runs | Human sees | Supervisor / Manager |
|---|---|---|---|
| Ticket parked in **`done`** | `node .ai/scripts/worktree-finish.mjs <ISSUE_ID> --agent NAME` (commit **then** `move done`) | Optional: `git status` from that ticket folder so the trail is visible. **No** commit fence for the human. **No** push. | **No** |
| Epic in **`summarize`** | `worktree-integrate` (prune ticket folders), smoke `_epic`, `move summarize`, `handoff` (git line + smoke note in HTML), then `worktree-push` | Living HTML already has the recorded push line and the walkthrough. Origin has the branch. `_epic` is gone. | **Yes** — one note for the **whole epic**, including smoke steps |

`move done` **refuses** a dirty ticket worktree. Dependents unlock only after the parked branch has a commit.

---

## Ticket → `done` (agent runs)

After flags pass in `testing`:

```bash
node .ai/scripts/worktree-finish.mjs APP-1-2 --agent backend-agent-1 --message "APP-1-2: short description"
```

That local-commits inside `.worktrees/{n}-{slug}/{ISSUE_ID}` and then moves the card to `done`. Do not tell the human to `cd` there and commit.

If finish fails because flags are missing, fix verification, then retry finish. If a later ticket’s `worktree-add` says a parked dep is dirty, **that** agent (or the owner of the dep) runs `worktree-commit` / `worktree-finish` on the dep — never ask the human to `git restore` or merge.

---

## Epic → `summarize` (agent integrates, records HTML, then pushes)

When **every** ticket in the epic is in `done`:

```bash
node .ai/scripts/worktree-integrate.mjs <n>
```

Default is **apply**: commit leftover dirty tickets, create `feature/{n}-{slug}` + `_epic`, merge remaining ticket branches, then **remove ticket folders and merged `ticket/` branches**. On conflict, the agent resolves in `_epic` (keep both tickets’ intent), `git add -A && git commit`, then re-runs integrate.

Then smoke the **combined** tree. Then `move summarize` + `handoff`.

Show `git status` from `.worktrees/{n}-{slug}/_epic` (`On branch feature/{n}-{slug}`). Record this line in the living HTML (audit, never `HEAD`):

```bash
cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}
```

Print `Supervisor / Manager:` **outside** the fence, note body in ` ```text ` (must include the smoke walkthrough). Record the same push line + note:

```bash
node .linear/scripts/board.mjs handoff <n> --git "cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}" --note-file path/to/note.txt
```

Then **run** (from the primary checkout):

```bash
node .ai/scripts/worktree-push.mjs <n>
```

That publishes `feature/{n}-{slug}` and removes `_epic`. Persist snapshots + journeys are committed on that branch first. The branch stays on origin. The living HTML already holds the git line and the smoke steps.

Then `node .ai/scripts/worktree-origin-ready.mjs`. When every epic is on origin, **stop** — do not merge into `main` yet (`.ai/rules/epic-on-origin.md`).

`worktree-after-push.mjs` remains an escape hatch if the branch is already on origin and `_epic` was left behind.

---

## Absolute paths (do not trap the human)

Ticket folders live under the **primary** repo root (the directory that contains `.worktrees/`). Commands assume that root. Never nest `.worktrees/.../.worktrees/...`. If a shell is already inside a worktree, still invoke scripts from the primary root (or pass `--repo <primary>` on the engine copy).

---

## Smoke-test checklist (mandatory quality bar)

When the epic changes UI, API, schema, or board process, the Supervisor / Manager checklist is a **real walkthrough**.

| Do | Do not |
|---|---|
| Full local URLs (Vite UI, Spring API, board UI if started) | Route names / class names only |
| Visible UI labels | DOM paths, CSS selectors |
| Expected outcome after each step | “Verify the service works” |
| Prerequisites (MariaDB up, Spring restarted, Vite running) | File paths as the only steps |

**Frontend:** the Health (or equivalent) page must **not** show `Network Error` / `unavailable` against `http://localhost:8080` from the Vite origin. CORS and/or a Vite proxy are part of GATE_HTTP / `build_ok` (`.ai/rules/frontend-api-reach.md`).

### Example epic package (record in HTML, then agent runs `worktree-push`)

Show `git status` from `_epic` in the agent reply before push.

```bash
cd .worktrees/1-my-feature/_epic && git push -u origin feature/1-my-feature
```

Supervisor / Manager:
```text
I finished epic 1-my-feature after every ticket reached done. Agents committed each ticket branch and merged them into feature/1-my-feature. The living summary holds the work log, this note, and the push command.

You can confirm it doing the following:

Prerequisites:
- MariaDB container up; Spring Boot freshly restarted if backend changed; Vite UI running if frontend changed

1. Open the board UI at http://127.0.0.1:47382
2. Click the epic row labelled 1-my-feature
3. Click Open living summary. Expect the push command, this Supervisor / Manager note, every ticket section, and any testing → doing returns
4. Expect every ticket in SUMMARIZE
5. If API work: hit the named .http request and expect 2xx
6. If UI work: open the local Vite URL, use the visible control, and expect the visible outcome (not Network Error)
```

---

## Keep `git add` safe

- Disposable clones belong under gitignored `_scratch/` (or outside the repo).
- Ignore `__pycache__/`, `*.pyc`, Maven `target/`, frontend `dist/` / `node_modules/`.
- Never stage `.worktrees/` (gitignored).

---

## Avoid merge conflicts with `main`

Parallel tickets that touch the same files will conflict at integrate. Prevent this with `blocked_by` and by starting later trees from the **committed** dep branch.

### Hotspot files

| File / area | Typical work |
|---|---|
| `.ai/project.yaml` | `module_name` |
| `0.database/canonical_sql/` + `3.diagrams/0.database/dbdiagram/schema.dbml` | Schema |
| `3.diagrams/1.backend/` · `3.diagrams/2.frontend/` | PlantUML / Mermaid |
| `.ai/rules/` · adapters · `AGENTS.md` | Process |
| `.docs/journey/{n}-{slug}.md` | One journey per epic |
| Backend / frontend module folders | API / UI |

When conflicts happen in `_epic`: keep **both** tickets’ intent. Do not take only HEAD or only the incoming branch without comparing scope. Agent commits the merge, then still runs `worktree-push` after `handoff`.

---

## Rationale

Asking a non-git user to `cd` into a worktree, restore a dirty file, and merge a ticket branch is how trees collapse. Scripts encode that dance. The living HTML records the push line and the smoke walkthrough; the agent then publishes the named epic branch and drops `_epic`.
