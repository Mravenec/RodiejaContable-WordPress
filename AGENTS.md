# Agent Guide — RodiejaContable-WordPress

Product site on the **unified WordPress harness**. **Native-first** (`.ai/rules/native-first.md`).

## Layout

```
web/wp-content/plugins/rodieja-contable/   ← product plugin (DI + CPT registrars)
web/wp-content/mu-plugins/                 ← hardening
.linear/ .ai/ .claude/ .cursor/ .ddev/
```

## Domain (landed MVP)

CPTs: marca, modelo, generacion, vehiculo, repuesto, transaccion.  
Role: `rodieja_contador`. REST: core CPT routes + `rodieja/v1/health`.

## Board

```bash
node .linear/scripts/board.mjs epics
node .ai/scripts/epic-new.mjs <n> <slug> --title "…"
node .ai/scripts/worktree-add.mjs <ID>   # after claim
```

No Laravel/Doctrine. Prefer CPT/meta before `$wpdb` custom tables.
