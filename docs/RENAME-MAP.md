# Workshop Ledger rename map (epic 26)

Canonical mapping. Implementation tickets must follow this table.

| Surface | From (legacy) | To |
|---|---|---|
| Product UI | Rodieja Contable | Workshop Ledger |
| Plugin folder | `rodieja-contable` | `workshop-ledger` |
| Bootstrap file | `rodieja-contable.php` | `workshop-ledger.php` |
| PHP namespace | `Rodieja\Contable` | `WorkshopLedger` |
| Text domain | `rodieja-contable` | `workshop-ledger` |
| Constants | `RODIEJA_PLUGIN_*` | `WL_PLUGIN_*` |
| CPT / tax / meta / options | `rodieja_*` | `wl_*` |
| Capability | `manage_rodieja_contable` | `manage_workshop_ledger` |
| Role | `rodieja_contador` | `workshop_accountant` |
| REST | `rodieja/v1` | `workshop-ledger/v1` |
| CSS / body classes | `rodieja-*` | `wl-*` |
| Admin page slugs | `rodieja-*` | `wl-*` / `workshop-ledger` home |
| DDEV project | `rodieja-wp` | `workshop-ledger` |
| Login | legacy admin | `admin@workshop.com` / `WorkshopLedger1!` |
| Repo directory | `RodiejaContable-WordPress` | `WorkshopLedger-WordPress` (after land) |

## CPT key map

| Old | New |
|---|---|
| `rodieja_marca` | `wl_marca` |
| `rodieja_modelo` | `wl_modelo` |
| `rodieja_generacion` | `wl_generacion` |
| `rodieja_vehiculo` | `wl_vehiculo` |
| `rodieja_repuesto` | `wl_repuesto` |
| `rodieja_empleado` | `wl_empleado` |
| `rodieja_transaccion` | `wl_transaccion` |
| `rodieja_pedido` | `wl_pedido` |
| `rodieja_pedido_item` | `wl_pedido_item` |
| `rodieja_evento` | `wl_evento` |

## Out of scope

- Read-Only React/Java repos (never edit)
- Historical journey titles for epics 1–25 (history)
- Third-party brands: Audatex, INS
