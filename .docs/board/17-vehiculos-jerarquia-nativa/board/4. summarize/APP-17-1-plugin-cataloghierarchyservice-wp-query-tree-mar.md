---
id: APP-17-1
title: Plugin · CatalogHierarchyService (WP_Query tree marca→modelo→gen→vehículo)
role: plugin
epic: 17
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T07:14:42.991Z"
summary_html: summaries/17-vehiculos-jerarquia-nativa-beginner.html
---
## Objetivo

Plugin · CatalogHierarchyService (WP_Query tree marca→modelo→gen→vehículo)

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### CatalogHierarchyRepository

```php
public function tree(): array { $marcas = $this->posts("rodieja_marca"); }
```

## History

- 2026-11-10T07:18:32.280Z epic summary HTML → summaries/17-vehiculos-jerarquia-nativa-beginner.html
- 2026-11-10T07:18:32.269Z moved done → summarize
- 2026-11-10T07:16:10.845Z moved testing → done
- 2026-11-10T07:15:25.747Z checklist item 2 marked
- 2026-11-10T07:15:22.698Z moved doing → testing
- 2026-11-10T07:15:22.506Z checklist item 1 marked
- 2026-11-10T07:14:42.992Z claimed by plugin-agent-1 → doing
- 2026-11-10T07:14:40.202Z created → backlog
