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
			__( 'Finance · Transaction', 'rodieja-contable' ),
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
		$sentido   = (string) get_post_meta( $post->ID, 'rodieja_sentido', true );
		$vehiculo  = (int) get_post_meta( $post->ID, 'rodieja_vehiculo_id', true );
		$empleado  = (int) get_post_meta( $post->ID, 'rodieja_empleado_id', true );

		echo '<p><label>' . $this->escaper->html( __( 'Amount', 'rodieja-contable' ) ) . '</label><br />';
		echo '<input type="number" step="0.01" name="rodieja_monto" value="' . $this->escaper->attr( (string) $monto ) . '" class="regular-text" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Date', 'rodieja-contable' ) ) . '</label><br />';
		echo '<input type="date" name="rodieja_fecha" value="' . $this->escaper->attr( $fecha ) . '" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Sentido', 'rodieja-contable' ) ) . '</label><br />';
		echo '<select name="rodieja_sentido">';
		foreach ( array( 'ingreso' => __( 'Income', 'rodieja-contable' ), 'egreso' => __( 'Expense', 'rodieja-contable' ) ) as $k => $lab ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', $this->escaper->attr( $k ), selected( $sentido, $k, false ), $this->escaper->html( $lab ) );
		}
		echo '</select></p>';
		$this->select_posts( 'rodieja_vehiculo', 'rodieja_vehiculo_id', $vehiculo, __( 'Vehicle', 'rodieja-contable' ) );
		$this->select_posts( 'rodieja_empleado', 'rodieja_empleado_id', $empleado, __( 'Employee', 'rodieja-contable' ) );
		echo '<p class="description">' . $this->escaper->html( __( 'Tipo (Income/Expense tax) en el panel lateral native.', 'rodieja-contable' ) ) . '</p>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || empty( $_POST['_rodieja_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_rodieja_nonce'] ) ), 'rodieja_finance_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, 'rodieja_monto', isset( $_POST['rodieja_monto'] ) ? (float) wp_unslash( $_POST['rodieja_monto'] ) : 0 );
		update_post_meta( $post_id, 'rodieja_fecha', isset( $_POST['rodieja_fecha'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['rodieja_fecha'] ) ) : '' );
		$sentido = isset( $_POST['rodieja_sentido'] ) ? sanitize_key( wp_unslash( (string) $_POST['rodieja_sentido'] ) ) : 'egreso';
		update_post_meta( $post_id, 'rodieja_sentido', in_array( $sentido, array( 'ingreso', 'egreso' ), true ) ? $sentido : 'egreso' );
		update_post_meta( $post_id, 'rodieja_vehiculo_id', (string) ( isset( $_POST['rodieja_vehiculo_id'] ) ? absint( wp_unslash( $_POST['rodieja_vehiculo_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'rodieja_empleado_id', (string) ( isset( $_POST['rodieja_empleado_id'] ) ? absint( wp_unslash( $_POST['rodieja_empleado_id'] ) ) : 0 ) );

		// Keep taxonomy in sync with sentido.
		$term = 'ingreso' === $sentido ? 'Ingreso' : 'Egreso';
		if ( ! term_exists( $term, 'rodieja_tipo_tx' ) ) {
			wp_insert_term( $term, 'rodieja_tipo_tx' );
		}
		wp_set_object_terms( $post_id, $term, 'rodieja_tipo_tx' );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function columns( array $cols ): array {
		$cols['rodieja_monto']    = __( 'Amount', 'rodieja-contable' );
		$cols['rodieja_sentido']  = __( 'Sentido', 'rodieja-contable' );
		$cols['rodieja_fecha']    = __( 'Date', 'rodieja-contable' );
		$cols['rodieja_vehiculo'] = __( 'Vehicle', 'rodieja-contable' );
		return $cols;
	}

	public function column( string $col, int $post_id ): void {
		switch ( $col ) {
			case 'rodieja_monto':
				echo $this->escaper->html( number_format_i18n( (float) get_post_meta( $post_id, 'rodieja_monto', true ), 2 ) );
				break;
			case 'rodieja_sentido':
				echo $this->escaper->html( (string) get_post_meta( $post_id, 'rodieja_sentido', true ) );
				break;
			case 'rodieja_fecha':
				echo $this->escaper->html( (string) get_post_meta( $post_id, 'rodieja_fecha', true ) );
				break;
			case 'rodieja_vehiculo':
				$id = (int) get_post_meta( $post_id, 'rodieja_vehiculo_id', true );
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
