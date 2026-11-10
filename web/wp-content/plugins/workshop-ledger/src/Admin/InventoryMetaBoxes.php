<?php
/**
 * Native meta boxes for repuestos (stock, precio, vehículo, parte tax UI helper).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;

final class InventoryMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_wl_repuesto', array( $this, 'save' ), 10, 2 );
		add_filter( 'manage_wl_repuesto_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_wl_repuesto_posts_custom_column', array( $this, 'column' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box(
			'wl_repuesto_inv',
			__( 'Inventory · Part', 'workshop-ledger' ),
			array( $this, 'render' ),
			'wl_repuesto',
			'normal',
			'high'
		);
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_inventory_meta' );
		$vehiculo = (int) get_post_meta( $post->ID, 'wl_vehiculo_id', true );
		$precio   = get_post_meta( $post->ID, 'wl_precio', true );
		$stock    = get_post_meta( $post->ID, 'wl_stock', true );

		echo '<p><label>' . $this->escaper->html( __( 'Vehicle origen', 'workshop-ledger' ) ) . '</label><br />';
		$vehicles = get_posts(
			array(
				'post_type'      => 'wl_vehiculo',
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		echo '<select name="wl_vehiculo_id">';
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
		echo '<p><label>' . $this->escaper->html( __( 'Price', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="number" step="0.01" name="wl_precio" value="' . $this->escaper->attr( (string) $precio ) . '" class="regular-text" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Stock', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="number" name="wl_stock" value="' . $this->escaper->attr( (string) $stock ) . '" class="small-text" /></p>';
		echo '<p class="description">' . $this->escaper->html( __( 'Assign the Part taxonomy in the side panel (native).', 'workshop-ledger' ) ) . '</p>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || ! isset( $_POST['_wl_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_wl_nonce'] ) ), 'wl_inventory_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, 'wl_vehiculo_id', (string) ( isset( $_POST['wl_vehiculo_id'] ) ? absint( wp_unslash( $_POST['wl_vehiculo_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'wl_precio', isset( $_POST['wl_precio'] ) ? (float) wp_unslash( $_POST['wl_precio'] ) : 0 );
		update_post_meta( $post_id, 'wl_stock', isset( $_POST['wl_stock'] ) ? (int) wp_unslash( $_POST['wl_stock'] ) : 0 );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function columns( array $cols ): array {
		$cols['wl_vehiculo'] = __( 'Vehicle', 'workshop-ledger' );
		$cols['wl_precio']   = __( 'Price', 'workshop-ledger' );
		$cols['wl_stock']    = __( 'Stock', 'workshop-ledger' );
		return $cols;
	}

	public function column( string $col, int $post_id ): void {
		if ( 'wl_vehiculo' === $col ) {
			$id = (int) get_post_meta( $post_id, 'wl_vehiculo_id', true );
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
		if ( 'wl_precio' === $col ) {
			echo $this->escaper->html( number_format_i18n( (float) get_post_meta( $post_id, 'wl_precio', true ), 2 ) );
			return;
		}
		if ( 'wl_stock' === $col ) {
			$stock = (int) get_post_meta( $post_id, 'wl_stock', true );
			$style = $stock <= 1 ? ' style="color:#b32d2e;font-weight:600"' : '';
			echo '<span' . $style . '>' . $this->escaper->html( (string) $stock ) . '</span>';
		}
	}
}
