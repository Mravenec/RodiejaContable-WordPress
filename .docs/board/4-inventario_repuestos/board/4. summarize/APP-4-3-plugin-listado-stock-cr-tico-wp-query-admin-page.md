---
id: APP-4-3
title: Plugin · listado stock crítico WP_Query admin page
role: plugin
epic: 4
estimate: 1
blocked_by:
  - APP-4-2
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T05:42:53.158Z"
summary_html: summaries/4-inventario-repuestos-beginner.html
---
## Objetivo

Plugin · listado stock crítico WP_Query admin page

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### CriticalStockPage WP_Query

Path: `src/Admin/CriticalStockPage.php`

```php
<?php
/**
 * Admin page: critical stock (native WP_Query meta_query).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;

final class CriticalStockPage {

	public const THRESHOLD = 1;

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
	}

	public function add_page(): void {
		add_submenu_page(
			'rodieja-contable',
			__( 'Stock crítico', 'rodieja-contable' ),
			__( 'Stock crítico', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-stock-critico',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$q = new \WP_Query(
			array(
				'post_type'      => 'rodieja_repuesto',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'meta_query'     => array(
/* … truncated 52 more lines — open the file */
```

## History

- 2026-11-10T07:21:58.110Z epic summary HTML → summaries/4-inventario-repuestos-beginner.html
- 2026-11-10T07:21:58.101Z moved done → summarize
- 2026-11-10T05:43:23.279Z moved testing → done
- 2026-11-10T05:43:23.205Z checklist item 2 marked
- 2026-11-10T05:43:23.116Z moved doing → testing
- 2026-11-10T05:43:22.958Z checklist item 1 marked
- 2026-11-10T05:42:53.159Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:35:44.239Z created → backlog
