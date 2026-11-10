---
id: APP-1-1
title: Plugin · scaffold DI + health REST
role: plugin
epic: 1
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
http_ok: true
tests_ok: true
claimed_at: "2026-11-10T05:16:09.510Z"
summary_html: summaries/1-foundation-beginner.html
---
## Objetivo

Plugin · scaffold DI + health REST

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### Plugin bootstrap DI

Path: `web/wp-content/plugins/rodieja-contable/src/Core/Plugin.php`

```php
<?php
/**
 * Plugin bootstrap — DI + native registrars.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Core;

use Rodieja\Contable\Admin\AdminMenu;
use Rodieja\Contable\Controllers\Rest\HealthController;
use Rodieja\Contable\Meta\MetaRegistrar;
use Rodieja\Contable\PostTypes\PostTypeRegistrar;
use Rodieja\Contable\Providers\ServiceProvider;
use Rodieja\Contable\Roles\RoleRegistrar;
use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\SecurityHeaders;
use Rodieja\Contable\Taxonomies\TaxonomyRegistrar;

final class Plugin {

	private static ?self $instance = null;

	private Container $container;

	private function __construct() {
		$this->container = new Container();
		( new ServiceProvider() )->register( $this->container );
	}

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	public function container(): Container {
		return $this->container;
	}
```

### REST health

Path: `wp-json/rodieja/v1/health`

```json
{"status":"ok","plugin":"rodieja-contable","version":"0.1.0","php":"8.3.30","wordpress":"7.1.4","native":true}
```

Ran:

```bash
curl -sk https://rodieja-wp.ddev.site/wp-json/rodieja/v1/health
```

Result:

```text
200 ok
```

## History

- 2026-11-10T05:19:55.252Z epic summary HTML → summaries/1-foundation-beginner.html
- 2026-11-10T05:19:55.245Z moved done → summarize
- 2026-11-10T05:18:00.969Z moved testing → done
- 2026-11-10T05:17:43.376Z checklist item 2 marked
- 2026-11-10T05:17:43.269Z moved doing → testing
- 2026-11-10T05:17:42.778Z checklist item 1 marked
- 2026-11-10T05:16:09.512Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:12:45.696Z created → backlog
