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
		echo '<option value="">' . $this->escaper->html( __( 'All statuses', 'rodieja-contable' ) ) . '</option>';
		foreach ( $terms as $term ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				$this->escaper->attr( $term->slug ),
				selected( $selected, $term->slug, false ),
				$this->escaper->html( $term->name )
			);
		}
		echo '</select>';
	}

	public function filter_by_estado( \WP_Query $query ): void {
		global $pagenow;
		if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
			return;
		}
		$post_type = $query->get( 'post_type' );
		if ( 'rodieja_vehiculo' !== $post_type ) {
			return;
		}
		$taxonomy = 'rodieja_estado_vehiculo';
		if ( empty( $_GET[ $taxonomy ] ) ) {
			return;
		}
		$slug = sanitize_text_field( wp_unslash( (string) $_GET[ $taxonomy ] ) );
		if ( '' === $slug ) {
			return;
		}
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $slug,
				),
			)
		);
	}
}
