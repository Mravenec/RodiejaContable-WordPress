---
id: APP-30-1
title: "NonceGuard: unique field name per meta-box action; fix all save handlers"
role: security
epic: 30
http_ok: true
tests_ok: true
estimate: 1
blocked_by: []
blocks: []
agent: security-agent-1
claimed_at: "2026-11-10T16:08:36.034Z"
summary_html: summaries/30-meta-nonce-unique-beginner.html
---
## Objetivo

NonceGuard: unique field name per meta-box action; fix all save handlers

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### unique-nonces

```php
NonceGuard::name_for(action) + verify_post; meta boxes no longer share _wl_nonce
```

## History

- 2026-11-10T16:40:03.974Z epic summary HTML → summaries/30-meta-nonce-unique-beginner.html
- 2026-11-10T16:40:03.966Z moved done → summarize
- 2026-11-10T16:09:43.362Z moved testing → done
- 2026-11-10T16:09:42.862Z checklist item 2 marked
- 2026-11-10T16:09:42.769Z moved doing → testing
- 2026-11-10T16:09:42.523Z checklist item 1 marked
- 2026-11-10T16:08:36.036Z claimed by security-agent-1 → doing
- 2026-11-10T16:08:26.145Z created → backlog
