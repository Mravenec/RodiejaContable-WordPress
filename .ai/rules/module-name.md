# Module name (backend / frontend folders)

Never use a permanent folder named `appProject`.

| When | What |
|---|---|
| Day zero | `module_name: null` in `.ai/project.yaml`. `1.backend/` and `2.frontend/` hold READMEs only. |
| First scaffold | Human (or orchestrator with human OK) sets `module_name` to the **project module name**. Create `1.backend/<module_name>/` as a [start.spring.io](https://start.spring.io/) Maven unzip (`.ai/rules/spring-initializr.md`) and `2.frontend/<module_name>/`. |
| Commands | `mvn` / `npm` run inside those named folders (`pom.xml` is at `1.backend/<module_name>/pom.xml`). |

Same name for both layers keeps the stack obvious. Stack remains MariaDB → Spring/JOOQ → React; only the folder label is the project name.

`3.diagrams/` uses the **layer** names (`0.database`, `1.backend`, `2.frontend`) — not `<module_name>/`.
