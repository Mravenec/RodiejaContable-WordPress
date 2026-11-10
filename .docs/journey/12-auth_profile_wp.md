# Epic 12 — auth_profile_wp

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/12-auth_profile_wp/` · Snapshot: `.docs/board/12-auth_profile_wp/`

## Title

Auth/Profile WP nativo: users, caps, profile meta

## Why

Identidad = usuarios WP. Empleado de dominio debe enlazarse al user y la matriz contador vs admin debe verificarse.

## What we did

- [x] Profile fields + link empleado↔user_id
- [x] Matriz roles/caps contador vs admin verificada

## Acceptance

- Given usuario contador
- Expect acceso acotado vs administrador Rodieja

## Agent notes (constraints / edge cases)

No auth framework externo; users + caps WP.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin profile | sí |  |
| Security roles | sí |  |

## Board issues (= tickets)

- APP-12-1 profile + link
- APP-12-2 caps matrix

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `perfil WP`
- `prueba caps contador vs admin`

```bash
node .linear/scripts/board.mjs progress --epic 12
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Auth/profile nativo LANDED.
