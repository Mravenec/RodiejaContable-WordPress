# Skill — native-first WordPress

Use on every feature ticket before coding.

## Decision gate

```
Can CPT / taxonomies / post meta / Options / Settings / WP_Query / core REST do this?
  YES → implement with native APIs (hooks, caps, nonces, sanitize/escape).
  NO  → write a short proposal (native gap + custom plan) → wait for human OK → then code.
```

## Proposal template (when custom seems needed)

1. **Goal**
2. **Native attempt** — which APIs; why insufficient
3. **Custom plan** — tables? plugin classes? packages?
4. **Security** — caps, nonces, `$wpdb->prepare`, REST `permission_callback`
5. **Ask** — “¿Seguimos nativo, custom, o híbrido?”

Do not start custom schema or new Composer runtime deps until the human answers.

## Day zero

Harness is neutral. Default bias: **native**. The plugin scaffold is ready when a plugin is justified — not a license to customize everything.
