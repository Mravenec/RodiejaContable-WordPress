# Epic 34 — admin-color-token-fidelity

**Status:** Landed on main · Board: `.linear/epics/34-admin-color-token-fidelity/`

## Title

Admin color/token fidelity closer to Read-Only Ant shell

## Why

(Describe the problem / opportunity in plain language. Put **all** narrative depth here.)

## What we will do

- [ ] Clarify acceptance criteria
- [ ] **Native-first:** prefer CPT/meta/Options/REST/hooks; if custom tables/packages seem needed, propose + get human OK before coding (`.ai/rules/native-first.md`)
- [ ] Split into **board issues** under `.linear` (those are the only tickets)
- [ ] Implement via multi-agent board (`doing` only)
- [ ] Move tickets to `testing`, then verify (harness flags)
- [ ] Park tickets in `done` until every issue in the epic is done
- [ ] Then `summarize` (wrap-up column) and `handoff` (git command + Supervisor / Manager into the living HTML)

## Acceptance

- Given …
- When …
- Expect …

## Agent notes (constraints / edge cases)

(Anything agents need that is not a checklist step: constraints, links, non-goals, risks. Keep it here — there is no `.ai/tickets` folder.)

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin schema / dbDelta | | Optional mirror `docs/database/canonical_sql/` + `docs/diagrams/database/` |
| `web/wp-content/plugins/<module>/` | | Contracts→Repos→Services→Controllers |
| `web/wp-content/mu-plugins/` | | Hardening |
| `docs/diagrams/plugin/` | | PlantUML twin of plugin `src/` |

## Board issues (= tickets)

Work lives only on the board. Create issues:

```bash
node .linear/scripts/board.mjs issue create 34 --title "DB · …" --role database
node .linear/scripts/board.mjs issue create 34 --title "Plugin · …" --role plugin --blocked-by APP-34-1
node .linear/scripts/board.mjs issue create 34 --title "Security · …" --role security --blocked-by APP-34-2
```

Each issue Markdown holds its own sequential checklist. Do not copy checklists into this journey.

## How to verify

```bash
node .linear/scripts/board.mjs progress --epic 34
# column + checklist %; 100% when all issues are in summarize with checklists complete
```

## Outcome

(Filled when epic reaches 100%.)


## Land on main

- Merge commit: `(unknown)`
- Branch: `feature/34-admin-color-token-fidelity` → `main`

### Supervisor / Manager (land)

Supervisor land note — Epic 34 admin-color-token-fidelity

Aligned Workshop Ledger admin/list/login CSS variables with Read-Only Ant theme tokens (#1890ff / #001529 / #f0f2f5). Native CSS only; Read-Only untouched.

You can confirm it doing the following:
1. Load any Workshop Ledger CPT list — surface #f0f2f5, primary actions #1890ff, table header #fafafa via --wl-* tokens.
2. Check wp-login branding still uses the navy→accent gradient from the same tokens.
3. Layer audit on main stays green.

