# {{TITLE}}

| Field | Value |
|---|---|
| Id | {{ID}} |
| Slug | {{SLUG}} |
| Status | {{STATUS}} |
| Progress | **{{PROGRESS}}%** ({{DONE}}/{{TOTAL}} in summarize · column + checklist) |
| Color | {{COLOR}} |

## Board

Issues under `board/` **are the tickets**. Agents move `.md` files:

`0. backlog` → `1. doing` → `2. testing` → `3. done` → `4. summarize`

## Narrative (host project)

Full Epic story lives only in `.docs/journey/{id}-{slug}.md` (why / acceptance / agent notes / verify).  
This folder is the **operational** board (claims, checklist, %). There is no `.ai/tickets/` pack.

## Issues

{{ISSUE_LIST}}
