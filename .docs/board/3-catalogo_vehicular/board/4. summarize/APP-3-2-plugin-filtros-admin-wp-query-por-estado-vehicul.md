---
id: APP-3-2
title: Plugin · filtros admin WP_Query por estado_vehiculo
role: plugin
epic: 3
estimate: 1
blocked_by:
  - APP-3-1
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T05:40:35.666Z"
summary_html: summaries/3-catalogo-vehicular-beginner.html
---
## Objetivo

Plugin · filtros admin WP_Query por estado_vehiculo

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### estado filter WP_Query

Path: `src/Admin/CatalogAdminFilters.php`

```php
<?php
/**
 * Native admin list filters for vehículos (estado taxonomy).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\OutputEscaper;

final class CatalogAdminFilters {

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		add_action( 'restrict_manage_posts', array( $this, 'render_estado_filter' ) );
		add_filter( 'parse_query', array( $this, 'filter_by_estado' ) );
	}

	public function render_estado_filter( string $post_type ): void {
		if ( 'rodieja_vehiculo' !== $post_type ) {
			return;
		}
		$taxonomy = 'rodieja_estado_vehiculo';
		$selected = isset( $_GET[ $taxonomy ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ $taxonomy ] ) ) : '';
		$terms    = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}
		echo '<select name="' . $this->escaper->attr( $taxonomy ) . '" id="' . $this->escaper->attr( $taxonomy ) . '">';
		echo '<option value="">' . $this->escaper->html( __( 'Todos los estados', 'rodieja-contable' ) ) . '</option>';
		foreach ( $terms as $term ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				$this->escaper->attr( $term->slug ),
				selected( $selected, $term->slug, false ),
				$this->escaper->html( $term->name )
			);
		}
		echo '</select>';
/* … truncated 31 more lines — open the file */
```

## History

- 2026-11-10T07:21:57.822Z epic summary HTML → summaries/3-catalogo-vehicular-beginner.html
- 2026-11-10T07:21:57.814Z moved done → summarize
- 2026-11-10T05:40:59.139Z moved testing → done
- 2026-11-10T05:40:59.054Z checklist item 2 marked
- 2026-11-10T05:40:58.974Z moved doing → testing
- 2026-11-10T05:40:58.811Z checklist item 1 marked
- 2026-11-10T05:40:35.667Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:35:43.926Z created → backlog
