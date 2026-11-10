# 8-harden_land

Harden + DDEV smoke + land hygiene

| Field | Value |
|---|---|
| Id | 8 |
| Slug | harden_land |
| Status | in_progress |
| Progress | **100%** (3/3 in summarize · column + checklist) |
| Color | #b45309 |

## Board

Issues under `board/` **are the tickets**. Agents move `.md` files:

`0. backlog` → `1. doing` → `2. testing` → `3. done` → `4. summarize`

## Narrative (host project)

Full Epic story lives only in `.docs/journey/{id}-{slug}.md` (why / acceptance / agent notes / verify).  
This folder is the **operational** board (claims, checklist, %). There is no `.ai/tickets/` pack.

## Issues

### 0. backlog

_empty_

### 1. doing

_empty_

### 2. testing

_empty_

### 3. done

_empty_

### 4. summarize

- `APP-8-1` Security · MU audit + DISALLOW_FILE_EDIT + layer-audit _(role:security)_
- `APP-8-2` Plugin · smoke DDEV health/stats/REST 401 anon _(role:plugin)_
- `APP-8-3` Orchestrator · summarize handoff land.json all epics _(role:orchestrator)_

