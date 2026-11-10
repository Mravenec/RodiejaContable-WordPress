# Skill — WordPress plugin scaffold

Path: `web/wp-content/plugins/<module_name>/` (unified project — not `1.plugin/`).

1. Bootstrap file with headers + `ABSPATH` + Composer autoload
2. PSR-4 in `composer.json` — no app frameworks
3. `src/`: Contracts · Repositories · Services · Controllers · Security · Providers · Core
4. Constructor DI via `Core\Container` + `Providers\ServiceProvider`
5. Activation `dbDelta` + caps; `uninstall.php` cleans data
6. `ddev composer install` · `node .ai/scripts/layer-audit.mjs`
