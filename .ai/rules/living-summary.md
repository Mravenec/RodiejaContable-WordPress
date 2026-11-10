---
description: Living HTML must show compact code, commands, and results for each ticket — not a cloned glossary
alwaysApply: true
---

# Living summary evidence (mandatory)

Canonical skill: `.ai/skills/living-summary/SKILL.md`  
Spec: `.ai/specs/living-summary.md`

The living HTML (`summaries/{n}-{slug}-beginner.html`) is how a beginner **and** a senior see what shipped. Board history plus a copied glossary is not enough.

## After every checklist item

1. Do the work.
2. `checklist <ID> n`
3. **Immediately** record compact evidence:

```bash
node .linear/scripts/board.mjs evidence <ID> --title "SQL password_hash" --path "0.database/canonical_sql/NN_slug.sql" --lang sql --code-file snippet.sql --command "python 0.database/scripts/setup_db.py" --result-file describe.txt --agent <name>
```

Use `--code` / `--result` for short inline text. Lang `html-preview` (or `html`) adds a compact HTML window in the summary.

## What to record (compact, path labeled)

Record **every layer the ticket touched**, in order. Do not skip a layer that was in the checklist.

| Role | Include |
|---|---|
| database | SQL · `setup_db` / tests / DESCRIBE result · DBML |
| backend | JOOQ codegen command+result · POJO · IRepository · Repository · IService · Service · IController · Controller · unit test if run · `.http` request **and** status · fix notes if it failed then passed |
| frontend | API service · hook · page (state job / primary action / next / hidden) · compact HTML · Mermaid if needed |

Chain a reviewer should be able to read across tickets: `database → tests → JOOQ/POJOs (never DTOs) → IRepo → Repo → ISvc → Svc → ICtrl → Ctrl → .http → api service → hooks → pages`.

Never paste secrets or raw JWTs. Truncation is built into `evidence` (~48 lines).

## HTML

Ticket section **What was built** renders `## Evidence`. Empty fences → warning card, not a fake tutorial.

Rule map: `.ai/rules/tool-adapters-harmony.md`
