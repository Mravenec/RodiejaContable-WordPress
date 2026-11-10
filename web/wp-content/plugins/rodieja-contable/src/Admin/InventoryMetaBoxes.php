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
		$stock    = get_post_meta( $post->ID, 'rodieja_stock', true );

		echo '<p><label>' . $this->escaper->html( __( 'Vehículo origen', 'rodieja-contable' ) ) . '</label><br />';
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
		echo '<option value="0">—</option>';
		foreach ( $vehicles as $v ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				(int) $v->ID,
				selected( $vehiculo, (int) $v->ID, false ),
				$this->escaper->html( get_the_title( $v ) )
			);
		}
		echo '</select></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Precio', 'rodieja-contable' ) ) . '</label><br />';
		echo '<input type="number" step="0.01" name="rodieja_precio" value="' . $this->escaper->attr( (string) $precio ) . '" class="regular-text" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Stock', 'rodieja-contable' ) ) . '</label><br />';
		echo '<input type="number" name="rodieja_stock" value="' . $this->escaper->attr( (string) $stock ) . '" class="small-text" /></p>';
		echo '<p class="description">' . $this->escaper->html( __( 'Asigna la taxonomía Parte en el panel lateral (nativo).', 'rodieja-contable' ) ) . '</p>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || ! isset( $_POST['_rodieja_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_rodieja_nonce'] ) ), 'rodieja_inventory_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, 'rodieja_vehiculo_id', (string) ( isset( $_POST['rodieja_vehiculo_id'] ) ? absint( wp_unslash( $_POST['rodieja_vehiculo_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'rodieja_precio', isset( $_POST['rodieja_precio'] ) ? (float) wp_unslash( $_POST['rodieja_precio'] ) : 0 );
		update_post_meta( $post_id, 'rodieja_stock', isset( $_POST['rodieja_stock'] ) ? (int) wp_unslash( $_POST['rodieja_stock'] ) : 0 );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function columns( array $cols ): array {
		$cols['rodieja_vehiculo'] = __( 'Vehículo', 'rodieja-contable' );
		$cols['rodieja_precio']   = __( 'Precio', 'rodieja-contable' );
		$cols['rodieja_stock']    = __( 'Stock', 'rodieja-contable' );
		return $cols;
	}

	public function column( string $col, int $post_id ): void {
		if ( 'rodieja_vehiculo' === $col ) {
			$id = (int) get_post_meta( $post_id, 'rodieja_vehiculo_id', true );
			if ( $id ) {
				$link = get_edit_post_link( $id );
				$title = get_the_title( $id );
				echo $link
					? '<a href="' . $this->escaper->attr( (string) $link ) . '">' . $this->escaper->html( $title ) . '</a>'
					: $this->escaper->html( $title );
			} else {
				echo '—';
			}
			return;
		}
		if ( 'rodieja_precio' === $col ) {
			echo $this->escaper->html( number_format_i18n( (float) get_post_meta( $post_id, 'rodieja_precio', true ), 2 ) );
			return;
		}
		if ( 'rodieja_stock' === $col ) {
			$stock = (int) get_post_meta( $post_id, 'rodieja_stock', true );
			$style = $stock <= 1 ? ' style="color:#b32d2e;font-weight:600"' : '';
			echo '<span' . $style . '>' . $this->escaper->html( (string) $stock ) . '</span>';
		}
	}
}
