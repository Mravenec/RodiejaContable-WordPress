---
description: Custom plugin folder name under wp-content/plugins
alwaysApply: true
---

# Module name (WordPress plugin folder)

| When | What |
|---|---|
| This harness | `module_name: harness-plugin` → `web/wp-content/plugins/harness-plugin/` |
| Rename | Human decision + update `.ai/project.yaml` `module_name` + `plugin_path` + DDEV `composer_root` |

Never use a permanent folder named `appProject`. Never put the plugin under `1.plugin/`.
