---
id: APP-5-2
title: Plugin · dashboard montos WP_Query/meta
role: plugin
epic: 5
estimate: 1
blocked_by:
  - APP-5-1
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T05:44:54.004Z"
---
## Objetivo

Plugin · dashboard montos WP_Query/meta

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### FinanceDashboardPage

Path: `src/Admin/FinanceDashboardPage.php`

```php
<?php
/**
 * Finanzas dashboard — montos via FinanceTotalsService (WP_Query).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\FinanceTotalsService;

final class FinanceDashboardPage {

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private FinanceTotalsService $totals
	) {}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
	}

	public function add_page(): void {
		add_submenu_page(
			'rodieja-contable',
			__( 'Finanzas', 'rodieja-contable' ),
			__( 'Finanzas', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-finanzas',
			array( $this, 'render' )
		);
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$all   = $this->totals->totals();
		$month = $this->totals->totals( gmdate( 'Y-m' ) );
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Dashboard financiero', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Totales calculados con WP_Query + post meta (sin tablas custom).', 'rodieja-contable' ) ); ?></p>
/* … truncated 21 more lines — open the file */
```

## History

- 2026-11-10T05:44:54.765Z moved testing → done
- 2026-11-10T05:44:54.681Z checklist item 2 marked
- 2026-11-10T05:44:54.604Z moved doing → testing
- 2026-11-10T05:44:54.452Z checklist item 1 marked
- 2026-11-10T05:44:54.005Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:35:44.395Z created → backlog
