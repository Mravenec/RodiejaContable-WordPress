---
id: APP-3-1
title: Plugin · meta boxes + list columns Marca/Modelo/Gen/Vehículo
role: plugin
epic: 3
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T05:35:49.955Z"
---
## Objetivo

Plugin · meta boxes + list columns Marca/Modelo/Gen/Vehículo

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### CatalogMetaBoxes native

Path: `src/Admin/CatalogMetaBoxes.php`

```php
<?php
/**
 * Native meta boxes for Marca → Modelo → Generación → Vehículo chain.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;

final class CatalogMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_rodieja_modelo', array( $this, 'save_modelo' ), 10, 2 );
		add_action( 'save_post_rodieja_generacion', array( $this, 'save_generacion' ), 10, 2 );
		add_action( 'save_post_rodieja_vehiculo', array( $this, 'save_vehiculo' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box(
			'rodieja_modelo_links',
			__( 'Catálogo · Modelo', 'rodieja-contable' ),
			array( $this, 'render_modelo' ),
			'rodieja_modelo',
			'normal',
			'high'
		);
		add_meta_box(
			'rodieja_generacion_links',
			__( 'Catálogo · Generación', 'rodieja-contable' ),
			array( $this, 'render_generacion' ),
			'rodieja_generacion',
			'normal',
			'high'
		);
		add_meta_box(
/* … truncated 145 more lines — open the file */
```

## History

- 2026-11-10T05:40:32.967Z moved testing → done
- 2026-11-10T05:40:32.890Z checklist item 2 marked
- 2026-11-10T05:40:32.816Z moved doing → testing
- 2026-11-10T05:40:32.660Z checklist item 1 marked
- 2026-11-10T05:35:49.956Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:35:43.852Z created → backlog
