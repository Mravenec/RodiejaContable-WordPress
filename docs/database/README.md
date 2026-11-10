# Domain mirror — Spring SQL → WordPress native

Runtime persistence for the landed MVP is **WordPress CPTs + post meta** (no custom `dbDelta` tables).

| SQL (sistema_vehicular) | WordPress |
|---|---|
| marcas | CPT `rodieja_marca` |
| modelos | CPT `rodieja_modelo` + `_rodieja_marca_id` |
| generaciones | CPT `rodieja_generacion` + modelo/años meta |
| vehiculos | CPT `rodieja_vehiculo` + vehicle meta |
| inventario_repuestos | CPT `rodieja_repuesto` |
| tipos_transacciones / transacciones_financieras | taxonomy `rodieja_tipo_tx` + CPT `rodieja_transaccion` |
| users / roles | WP Users + `rodieja_contador` |

Original SQL remains in the Spring repo for reference; do not load it into this WP MariaDB as the app schema.
