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
		echo '<option value="0">' . $this->escaper->html( __( 'Todos los vehicles', 'rodieja-contable' ) ) . '</option>';
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

	public function filter_by_vehiculo( \WP_Query $query ): void {
		global $pagenow;
		if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
			return;
		}
		if ( 'rodieja_repuesto' !== $query->get( 'post_type' ) ) {
			return;
		}
		$id = isset( $_GET['rodieja_vehiculo_id'] ) ? absint( wp_unslash( $_GET['rodieja_vehiculo_id'] ) ) : 0;
		if ( $id <= 0 ) {
			return;
		}
		$query->set(
			'meta_query',
			array(
				array(
					'key'   => 'rodieja_vehiculo_id',
					'value' => (string) $id,
				),
			)
		);
	}
}
