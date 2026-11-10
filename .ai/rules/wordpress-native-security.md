# WordPress native + security

- Use only WordPress/PHP APIs listed in AGENTS.md.
- Every mutating REST route needs a real capability check (not `__return_true`).
- Every admin form needs `wp_nonce_field` + `check_admin_referer` + `current_user_can`.
- Sanitize on input, escape on output, validate always.
- `$wpdb->prepare` for all dynamic SQL values; never concatenate user input.
- Prefix options, tables, capabilities, REST namespaces, text domain.
- Do not commit `web/wp-config.php`, uploads, caches, or `.env` secrets.
