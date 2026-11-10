# Epic 14 — pedidos_ins_import

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/14-pedidos_ins_import/` · Snapshot: `.docs/board/14-pedidos_ins_import/`

## Title

Pedidos INS/Audatex: import JSON/CSV local + Options (sin API live)

## Why

Traer pedidos Audatex/INS sin sync live: Options de configuración + import local JSON/CSV a CPT/ítems.

## What we did

- [x] Options Audatex/INS (sin API live)
- [x] Import JSON/CSV → pedidos + items
- [x] Mapa journey Spring sync → import nativo

## Acceptance

- Given un JSON/CSV válido
- When se importa en admin
- Expect CPT pedidos/ítems creados; settings en Options

## Agent notes (constraints / edge cases)

Explicit non-goal: sync live Audatex. Solo import local.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin Options + import | sí |  |
| Orchestrator map | sí |  |

## Board issues (= tickets)

- APP-14-1 Options settings
- APP-14-2 import JSON/CSV
- APP-14-3 map Spring→native

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `Settings Audatex/INS`
- `import page con fixture`

```bash
node .linear/scripts/board.mjs progress --epic 14
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Import local INS/Audatex LANDED (sin live API).
