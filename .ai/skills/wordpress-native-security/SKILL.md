# Skill — WordPress native security

Use on every plugin/security ticket.

## Checklist

- Sanitize early / escape late / always validate
- Capabilities before state changes (`current_user_can` / `CapabilityGate`)
- Nonces on admin forms and AJAX (`NonceGuard`)
- REST: real `permission_callback` on mutations (never `__return_true`)
- SQL: `$wpdb->prepare` / insert / update / delete only; `$wpdb->prefix`
- `ABSPATH` / `WP_UNINSTALL_PLUGIN` guards
- Keep MU-plugin symlink + nginx deny PHP in uploads
- Prefer `wp_safe_redirect`

Canonical: `.ai/rules/wordpress-native-security.md` · plugin `src/Security/`
