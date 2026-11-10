# Skill — living summary evidence

1. Finish one checklist item → mark it → record evidence the same turn.

```bash
node .linear/scripts/board.mjs evidence <ID> --title "ItemRepository" --path "web/wp-content/plugins/<module>/src/Repositories/ItemRepository.php" --lang php --code-file snippet.php --agent NAME
```

HTTP:

```bash
node .linear/scripts/board.mjs evidence <ID> --title "REST items" --lang http --command "curl …" --result "401 anonymous / 201 auth" --agent NAME
```

Order when touched:

`dbDelta/SQL → Contract → Repository → Service → Controller → Security → HTTP/WP-CLI`

Do not dump `vendor/` or WordPress core; never paste passwords/tokens.
