# Epic 35 — orphan-meta-english-cleanup

**Status:** Landed on main · Board: `.linear/epics/35-orphan-meta-english-cleanup/`

## Title

Cleanup orphan wl_estado + leftover Spanish admin strings

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
node .linear/scripts/board.mjs issue create 35 --title "DB · …" --role database
node .linear/scripts/board.mjs issue create 35 --title "Plugin · …" --role plugin --blocked-by APP-35-1
node .linear/scripts/board.mjs issue create 35 --title "Security · …" --role security --blocked-by APP-35-2
```

Each issue Markdown holds its own sequential checklist. Do not copy checklists into this journey.

## How to verify

```bash
node .linear/scripts/board.mjs progress --epic 35
# column + checklist %; 100% when all issues are in summarize with checklists complete
```

## Outcome

(Filled when epic reaches 100%.)


## Land on main

- Merge commit: `(unknown)`
- Branch: `feature/35-orphan-meta-english-cleanup` → `main`

### Supervisor / Manager (land)

Supervisor land note — Epic 35 orphan-meta-english-cleanup

Migrated orphan wl_estado meta to taxonomy wl_estado_vehiculo (then deleted), blocked reintroduction via domain import, and English-only gallery/pedido leftovers. Read-Only untouched.

You can confirm it doing the following:
1. ddev exec wp --path=web db query "SELECT COUNT(*) FROM wp_postmeta WHERE meta_key='wl_estado';" → 0 after boot.
2. Vehicle status still appears via taxonomy wl_estado_vehiculo on edit screen.
3. Gallery help text and Order meta labels are English; layer audit green.

