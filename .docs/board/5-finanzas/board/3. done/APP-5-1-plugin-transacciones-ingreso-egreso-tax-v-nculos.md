---
id: APP-5-1
title: Plugin · transacciones ingreso/egreso + tax + vínculos
role: plugin
epic: 5
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T05:43:27.924Z"
---
## Objetivo

Plugin · transacciones ingreso/egreso + tax + vínculos

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### FinanceMetaBoxes

Path: `src/Admin/FinanceMetaBoxes.php`

```php
<?php
/**
 * Native meta boxes for transacciones financieras.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;

final class FinanceMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_rodieja_transaccion', array( $this, 'save' ), 10, 2 );
		add_filter( 'manage_rodieja_transaccion_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_rodieja_transaccion_posts_custom_column', array( $this, 'column' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box(
			'rodieja_tx_meta',
			__( 'Finanzas · Transacción', 'rodieja-contable' ),
			array( $this, 'render' ),
			'rodieja_transaccion',
			'normal',
			'high'
		);
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_finance_meta' );
		$monto     = get_post_meta( $post->ID, 'rodieja_monto', true );
		$fecha     = (string) get_post_meta( $post->ID, 'rodieja_fecha', true );
/* … truncated 98 more lines — open the file */
```

## History

- 2026-11-10T05:44:53.618Z moved testing → done
- 2026-11-10T05:44:53.527Z checklist item 2 marked
- 2026-11-10T05:44:53.443Z moved doing → testing
- 2026-11-10T05:44:53.283Z checklist item 1 marked
- 2026-11-10T05:43:27.925Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:35:44.318Z created → backlog
