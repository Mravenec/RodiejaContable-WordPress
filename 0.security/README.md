# 0.security

Always-on hardening, symlinked into `web/wp-content/mu-plugins/` on `ddev start`.

| File | Purpose |
|---|---|
| `mu-plugins/harness-hardening.php` | `DISALLOW_FILE_EDIT`, `FORCE_SSL_ADMIN`, block `?author=` enumeration, restrict Application Passwords to admins |

Plugin-level security (caps, nonces, sanitize/escape, REST permissions, headers, XML-RPC off) lives in `1.plugin/harness-plugin/src/Security/`.
