# Epic 16 — tools_import_export

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/16-tools_import_export/` · Snapshot: `.docs/board/16-tools_import_export/`

## Title

Native Tools: WP domain export/import + CSV

## Why

Domain backup/migration must live in admin Tools (JSON/CSV export + validated import), not Spring utilities.

## What we did

- [x] Tools submenu export all CPT JSON/CSV (nonce)
- [x] JSON import with validate + wp_insert_post
- [x] Map DatabaseImportExport → native Tools

## Acceptance

- Given manage caps
- When exporting and re-importing a fixture
- Expect recreated posts; without nonce/cap → fail

## Agent notes (constraints / edge cases)

Nonce + caps required on every POST.

## Board issues (= tickets)

- APP-16-1 export
- APP-16-2 import
- APP-16-3 Spring tools map

## How to verify

- `Config/Tools export+import`
- `layer-audit`

```bash
node .linear/scripts/board.mjs progress --epic 16
```

## Outcome

Tools import/export LANDED.
