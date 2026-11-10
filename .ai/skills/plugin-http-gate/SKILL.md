# Skill — plugin HTTP gate (GATE_HTTP)

Prove the WordPress REST / admin contract before dependents proceed.

## In `testing` (not in `doing`)

1. Plugin active: `ddev wp plugin activate <module_name>`
2. Public allowlisted routes only (today: `harness/v1/health`) return 200 and no secrets.
3. Protected mutating/list routes return **401/403** without auth.
4. Authenticated admin (cookie + `X-WP-Nonce`, Application Password, or `ddev wp eval` + `rest_do_request`) gets 2xx.
5. Set `http_ok: true` (and `tests_ok: true` if PHPUnit/WPCS ran).

## Evidence

Record compact `board.mjs evidence` for: route registration, `permission_callback`, curl/WP-CLI status codes.

Never mark GATE_HTTP on Network Error / 500 from missing tables — fix schema/activation first.
