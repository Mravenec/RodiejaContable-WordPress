# Epic 33 — geo-employee-cascade

**Status:** Landed on main · Board: `.linear/epics/33-geo-employee-cascade/`

## Title

Geo canton cascade + Employee contact meta box

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
node .linear/scripts/board.mjs issue create 33 --title "DB · …" --role database
node .linear/scripts/board.mjs issue create 33 --title "Plugin · …" --role plugin --blocked-by APP-33-1
node .linear/scripts/board.mjs issue create 33 --title "Security · …" --role security --blocked-by APP-33-2
```

Each issue Markdown holds its own sequential checklist. Do not copy checklists into this journey.

## How to verify

```bash
node .linear/scripts/board.mjs progress --epic 33
# column + checklist %; 100% when all issues are in summarize with checklists complete
```

## Outcome

(Filled when epic reaches 100%.)


## Land on main

- Merge commit: `bffe9829ec37a37eadfa5a43503e359e6cb5202f`
- Branch: `feature/33-geo-employee-cascade` → `main`

### Supervisor / Manager (land)

Supervisor land note — Epic 33 geo-employee-cascade

Merged province→canton JS cascade and Employee contact meta onto main. Read-Only untouched.

You can confirm it doing the following:
1. Open a Location CPT edit screen — changing Province must refresh Canton options.
2. Open an Employee CPT — email, phone, and position fields save with the WP user link.
3. Layer audit on main is green; anonymous protected REST stays 401/403.

