---
id: APP-4-2
title: Plugin · vínculo repuesto↔vehículo en admin
role: plugin
epic: 4
estimate: 1
blocked_by:
  - APP-4-1
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T05:42:28.786Z"
---
## Objetivo

Plugin · vínculo repuesto↔vehículo en admin

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### filter by vehiculo meta

Path: `src/Admin/InventoryAdminFilters.php`

```php
<?php
/**
 * Filter repuestos by linked vehículo (meta query — native WP_Query).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\OutputEscaper;

final class InventoryAdminFilters {

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		add_action( 'restrict_manage_posts', array( $this, 'render_vehiculo_filter' ) );
		add_filter( 'parse_query', array( $this, 'filter_by_vehiculo' ) );
	}

	public function render_vehiculo_filter( string $post_type ): void {
		if ( 'rodieja_repuesto' !== $post_type ) {
			return;
		}
		$selected = isset( $_GET['rodieja_vehiculo_id'] ) ? absint( wp_unslash( $_GET['rodieja_vehiculo_id'] ) ) : 0;
		$vehicles = get_posts(
			array(
				'post_type'      => 'rodieja_vehiculo',
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		echo '<select name="rodieja_vehiculo_id">';
		echo '<option value="0">' . $this->escaper->html( __( 'Todos los vehículos', 'rodieja-contable' ) ) . '</option>';
		foreach ( $vehicles as $v ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				(int) $v->ID,
				selected( $selected, (int) $v->ID, false ),
				$this->escaper->html( get_the_title( $v ) )
			);
		}
		echo '</select>';
	}
/* … truncated 24 more lines — open the file */
```

## History

- 2026-11-10T05:42:52.747Z moved testing → done
- 2026-11-10T05:42:52.672Z checklist item 2 marked
- 2026-11-10T05:42:52.599Z moved doing → testing
- 2026-11-10T05:42:52.449Z checklist item 1 marked
- 2026-11-10T05:42:28.787Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:35:44.160Z created → backlog
