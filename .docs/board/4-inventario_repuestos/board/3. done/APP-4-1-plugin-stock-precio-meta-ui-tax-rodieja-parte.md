---
id: APP-4-1
title: Plugin · stock/precio meta UI + tax rodieja_parte
role: plugin
epic: 4
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T05:41:39.535Z"
---
## Objetivo

Plugin · stock/precio meta UI + tax rodieja_parte

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### InventoryMetaBoxes

Path: `src/Admin/InventoryMetaBoxes.php`

```php
<?php
/**
 * Native meta boxes for repuestos (stock, precio, vehículo, parte tax UI helper).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;

final class InventoryMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_rodieja_repuesto', array( $this, 'save' ), 10, 2 );
		add_filter( 'manage_rodieja_repuesto_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_rodieja_repuesto_posts_custom_column', array( $this, 'column' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box(
			'rodieja_repuesto_inv',
			__( 'Inventario · Repuesto', 'rodieja-contable' ),
			array( $this, 'render' ),
			'rodieja_repuesto',
			'normal',
			'high'
		);
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_inventory_meta' );
		$vehiculo = (int) get_post_meta( $post->ID, 'rodieja_vehiculo_id', true );
		$precio   = get_post_meta( $post->ID, 'rodieja_precio', true );
/* … truncated 82 more lines — open the file */
```

## History

- 2026-11-10T05:42:28.436Z moved testing → done
- 2026-11-10T05:42:28.363Z checklist item 2 marked
- 2026-11-10T05:42:28.291Z moved doing → testing
- 2026-11-10T05:42:28.133Z checklist item 1 marked
- 2026-11-10T05:41:39.535Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:35:44.080Z created → backlog
