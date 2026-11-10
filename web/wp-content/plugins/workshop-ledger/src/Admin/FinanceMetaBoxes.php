<?php
/**
 * Native meta boxes for transacciones financieras.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;

final class FinanceMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_wl_transaccion', array( $this, 'save' ), 10, 2 );
		add_filter( 'manage_wl_transaccion_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_wl_transaccion_posts_custom_column', array( $this, 'column' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box(
			'wl_tx_meta',
			__( 'Finance · Transaction', 'workshop-ledger' ),
			array( $this, 'render' ),
			'wl_transaccion',
			'normal',
			'high'
		);
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_finance_meta' );
		$monto     = get_post_meta( $post->ID, 'wl_monto', true );
		$fecha     = (string) get_post_meta( $post->ID, 'wl_fecha', true );
		$sentido   = (string) get_post_meta( $post->ID, 'wl_sentido', true );
		$vehiculo  = (int) get_post_meta( $post->ID, 'wl_vehiculo_id', true );
		$empleado  = (int) get_post_meta( $post->ID, 'wl_empleado_id', true );

		echo '<p><label>' . $this->escaper->html( __( 'Amount', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="number" step="0.01" name="wl_monto" value="' . $this->escaper->attr( (string) $monto ) . '" class="regular-text" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Date', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="date" name="wl_fecha" value="' . $this->escaper->attr( $fecha ) . '" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Direction', 'workshop-ledger' ) ) . '</label><br />';
		echo '<select name="wl_sentido">';
		foreach ( array( 'ingreso' => __( 'Income', 'workshop-ledger' ), 'egreso' => __( 'Expense', 'workshop-ledger' ) ) as $k => $lab ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', $this->escaper->attr( $k ), selected( $sentido, $k, false ), $this->escaper->html( $lab ) );
		}
		echo '</select></p>';
		$this->select_posts( 'wl_vehiculo', 'wl_vehiculo_id', $vehiculo, __( 'Vehicle', 'workshop-ledger' ) );
		$this->select_posts( 'wl_empleado', 'wl_empleado_id', $empleado, __( 'Employee', 'workshop-ledger' ) );
		echo '<p class="description">' . $this->escaper->html( __( 'Income/Expense type also syncs to the native taxonomy in the side panel.', 'workshop-ledger' ) ) . '</p>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || ! $this->nonce->verify_post( 'wl_finance_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, 'wl_monto', isset( $_POST['wl_monto'] ) ? (float) wp_unslash( $_POST['wl_monto'] ) : 0 );
		update_post_meta( $post_id, 'wl_fecha', isset( $_POST['wl_fecha'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_fecha'] ) ) : '' );
		$sentido = isset( $_POST['wl_sentido'] ) ? sanitize_key( wp_unslash( (string) $_POST['wl_sentido'] ) ) : 'egreso';
		update_post_meta( $post_id, 'wl_sentido', in_array( $sentido, array( 'ingreso', 'egreso' ), true ) ? $sentido : 'egreso' );
		update_post_meta( $post_id, 'wl_vehiculo_id', (string) ( isset( $_POST['wl_vehiculo_id'] ) ? absint( wp_unslash( $_POST['wl_vehiculo_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'wl_empleado_id', (string) ( isset( $_POST['wl_empleado_id'] ) ? absint( wp_unslash( $_POST['wl_empleado_id'] ) ) : 0 ) );

		// Keep taxonomy in sync with sentido.
		$term = 'ingreso' === $sentido ? 'Ingreso' : 'Egreso';
		if ( ! term_exists( $term, 'wl_tipo_tx' ) ) {
			wp_insert_term( $term, 'wl_tipo_tx' );
		}
		wp_set_object_terms( $post_id, $term, 'wl_tipo_tx' );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function columns( array $cols ): array {
		// Keep one Date (transaction meta); drop WP publish date duplicate.
		unset( $cols['date'] );
		$cols['wl_monto']    = __( 'Amount', 'workshop-ledger' );
		$cols['wl_sentido']  = __( 'Direction', 'workshop-ledger' );
		$cols['wl_fecha']    = __( 'Date', 'workshop-ledger' );
		$cols['wl_vehiculo'] = __( 'Vehicle', 'workshop-ledger' );
		return $cols;
	}

	public function column( string $col, int $post_id ): void {
		switch ( $col ) {
			case 'wl_monto':
				echo $this->escaper->html( number_format_i18n( (float) get_post_meta( $post_id, 'wl_monto', true ), 2 ) );
				break;
			case 'wl_sentido':
				$sentido = (string) get_post_meta( $post_id, 'wl_sentido', true );
				$label   = 'ingreso' === $sentido
					? __( 'Income', 'workshop-ledger' )
					: ( 'egreso' === $sentido ? __( 'Expense', 'workshop-ledger' ) : $sentido );
				echo $this->escaper->html( $label );
				break;
			case 'wl_fecha':
				echo $this->escaper->html( (string) get_post_meta( $post_id, 'wl_fecha', true ) );
				break;
			case 'wl_vehiculo':
				$id = (int) get_post_meta( $post_id, 'wl_vehiculo_id', true );
				echo $id ? $this->escaper->html( get_the_title( $id ) ) : '—';
				break;
		}
	}

	private function select_posts( string $type, string $name, int $selected, string $label ): void {
		echo '<p><label>' . $this->escaper->html( $label ) . '</label><br />';
		$posts = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		echo '<select name="' . $this->escaper->attr( $name ) . '"><option value="0">—</option>';
		foreach ( $posts as $p ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				(int) $p->ID,
				selected( $selected, (int) $p->ID, false ),
				$this->escaper->html( get_the_title( $p ) )
			);
		}
		echo '</select></p>';
	}
}
