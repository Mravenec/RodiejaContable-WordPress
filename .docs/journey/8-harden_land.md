# Epic 8 — harden_land

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/8-harden_land/` · Snapshot: `.docs/board/8-harden_land/`

## Title

Harden + DDEV smoke + land hygiene

## Why

Security and smoke closeout: MU audit, DISALLOW_FILE_EDIT, layer-audit, health/REST 401, coherent land.json.

## What we did

- [x] MU audit + DISALLOW_FILE_EDIT + green layer-audit
- [x] DDEV smoke health/stats/REST 401 anon
- [x] Summarize/handoff/land status

## Acceptance

- Given DDEV
- When smoke runs
- Expect healthy health and anonymous mutation 401/403

## Agent notes (constraints / edge cases)

Runs after domain block; never hand-fake LANDED.

## Board issues (= tickets)

- APP-8-1 MU + layer-audit
- APP-8-2 DDEV smoke
- APP-8-3 summarize/land

## How to verify

- `layer-audit`
- `curl health`
- `REST anon`

```bash
node .linear/scripts/board.mjs progress --epic 8
```

## Outcome

Harden + smoke + land hygiene LANDED.
