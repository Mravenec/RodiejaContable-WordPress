---
id: APP-1-2
title: Security · roles caps + MU hardening verify
role: security
epic: 1
estimate: 1
blocked_by:
  - APP-1-1
blocks: []
agent: security-agent-1
tests_ok: true
claimed_at: "2026-11-10T05:18:11.613Z"
---
## Objetivo

Security · roles caps + MU hardening verify

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### Native roles+MU

Path: `web/wp-content/mu-plugins/harness-hardening.php`

```text
role rodieja_contador + manage_rodieja_contable + MU harness-hardening + DISALLOW_FILE_EDIT
```

Ran:

```bash
ddev wp eval role/cap checks
```

Result:

```text
role_ok cap_ok file_edit_disallowed
```

## History

- 2026-11-10T05:18:30.767Z checklist item 2 marked
- 2026-11-10T05:18:30.638Z moved doing → testing
- 2026-11-10T05:18:30.442Z checklist item 1 marked
- 2026-11-10T05:18:11.617Z claimed by security-agent-1 → doing
- 2026-11-10T05:12:45.800Z created → backlog
