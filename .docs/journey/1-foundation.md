# Epic 1 — foundation

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/1-foundation/` · Snapshot: `.docs/board/1-foundation/`

## Title

Foundation: WP + plugin scaffold + roles

## Why

Rodieja Contable necesita un plugin WordPress unificado con DI, health REST y roles/caps, no un monolito Spring portado. Sin esta base no hay dominio nativo seguro.

## What we did

- [x] Scaffold `rodieja-contable` con Composer PSR-4, Container y ServiceProvider
- [x] Health REST + capa Security (CapabilityGate, OutputEscaper)
- [x] Roles/caps + MU-plugin hardening baseline
- [x] Mapa native-first documentado en AGENTS / journey

## Acceptance

- Given DDEV/WP con el plugin activo
- When se llama el endpoint de health
- Expect 200 y plugin cableado con DI
- Given un usuario sin caps Rodieja
- Expect mutaciones REST/admin denegadas

## Agent notes (constraints / edge cases)

Native-first desde el día 1. Sin Laravel/Doctrine. Layer-audit verde. LANDED = origin/main.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin schema / dbDelta | — | Aún no; dominio CPT/meta |
| web/wp-content/plugins/rodieja-contable/ | sí | Core DI + health + Security |
| web/wp-content/mu-plugins/ | sí | Hardening baseline |
| docs/diagrams/plugin/ | opcional | Twins PlantUML si aplica |

## Board issues (= tickets)

- APP-1-1 scaffold DI + health REST
- APP-1-2 roles/caps + MU verify
- APP-1-3 docs journey + native map

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `ddev wp plugin list`
- `curl -sI $(ddev describe -j | …)/wp-json/…/health`
- `node .ai/scripts/layer-audit.mjs`

```bash
node .linear/scripts/board.mjs progress --epic 1
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Plugin base activo en origin/main; board 100%/LANDED. Siguiente: dominio CPT (epic 2).
