# Epic 16 — tools_import_export

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/16-tools_import_export/` · Snapshot: `.docs/board/16-tools_import_export/`

## Title

Tools nativos: export/import WP + CSV dominio

## Why

Operaciones de backup/migración de dominio deben vivir en Tools admin (export JSON/CSV + import validado), no en utilidades Spring.

## What we did

- [x] Tools submenu export all CPT JSON/CSV (nonce)
- [x] Import JSON con validate + wp_insert_post
- [x] Mapa DatabaseImportExport → Tools nativos

## Acceptance

- Given caps manage
- When exporta e reimporta fixture
- Expect posts recreados; sin nonce/cap → fail

## Agent notes (constraints / edge cases)

Nonce + caps obligatorios en cada POST.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin ToolsAdminPage | sí |  |

## Board issues (= tickets)

- APP-16-1 export
- APP-16-2 import
- APP-16-3 map Spring tools

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `Config/Tools export+import`
- `layer-audit`

```bash
node .linear/scripts/board.mjs progress --epic 16
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Tools import/export LANDED.
