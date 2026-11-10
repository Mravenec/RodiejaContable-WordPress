<?php
/**
 * Classic meta boxes (native admin UI) for key fields.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Roles\RoleRegistrar;

final class MetaBoxRegistrar {

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box( 'rodieja_vehiculo_datos', __( 'Datos del vehículo', 'rodieja-contable' ), array( $this, 'render_vehiculo' ), 'rodieja_vehiculo', 'normal', 'high' );
		add_meta_box( 'rodieja_tx_datos', __( 'Datos de la transacción', 'rodieja-contable' ), array( $this, 'render_transaccion' ), 'rodieja_transaccion', 'normal', 'high' );
		add_meta_box( 'rodieja_modelo_datos', __( 'Relación', 'rodieja-contable' ), array( $this, 'render_modelo' ), 'rodieja_modelo', 'side', 'default' );
		add_meta_box( 'rodieja_gen_datos', __( 'Relación / años', 'rodieja-contable' ), array( $this, 'render_generacion' ), 'rodieja_generacion', 'side', 'default' );
	}

	public function render_vehiculo( \WP_Post $post ): void {
		wp_nonce_field( 'rodieja_save_meta', '_rodieja_meta_nonce' );
		$fields = array(
			'_rodieja_codigo'           => 'Código',
			'_rodieja_generacion_id'    => 'Generación ID',
			'_rodieja_anio'             => 'Año',
			'_rodieja_precio_compra'    => 'Precio compra',
			'_rodieja_costo_grua'       => 'Costo grúa',
			'_rodieja_comisiones'       => 'Comisiones',
			'_rodieja_costo_recuperado' => 'Costo recuperado',
			'_rodieja_traccion'         => 'Tracción (4x2|4x4)',
			'_rodieja_transmision'      => 'Transmisión',
			'_rodieja_combustible'      => 'Combustible',
			'_rodieja_estado'           => 'Estado',
			'_rodieja_fecha_ingreso'    => 'Fecha ingreso (YYYY-MM-DD)',
			'_rodieja_precio_venta'     => 'Precio venta',
		);
		echo '<table class="form-table">';
		foreach ( $fields as $key => $label ) {
			$val = get_post_meta( $post->ID, $key, true );
			printf(
				'<tr><th><label for="%1$s">%2$s</label></th><td><input class="regular-text" type="text" id="%1$s" name="%1$s" value="%3$s" /></td></tr>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( (string) $val )
			);
		}
		echo '</table>';
	}

	public function render_transaccion( \WP_Post $post ): void {
		wp_nonce_field( 'rodieja_save_meta', '_rodieja_meta_nonce' );
		$fields = array(
			'_rodieja_codigo'      => 'Código',
			'_rodieja_fecha'       => 'Fecha (YYYY-MM-DD)',
			'_rodieja_categoria'   => 'Categoría (INGRESO|EGRESO)',
			'_rodieja_monto'       => 'Monto',
			'_rodieja_vehiculo_id' => 'Vehículo ID',
			'_rodieja_repuesto_id' => 'Repuesto ID',
			'_rodieja_estado'      => 'Estado',
		);
		echo '<table class="form-table">';
		foreach ( $fields as $key => $label ) {
			$val = get_post_meta( $post->ID, $key, true );
			printf(
				'<tr><th><label for="%1$s">%2$s</label></th><td><input class="regular-text" type="text" id="%1$s" name="%1$s" value="%3$s" /></td></tr>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( (string) $val )
			);
		}
		echo '</table>';
	}

	public function render_modelo( \WP_Post $post ): void {
		wp_nonce_field( 'rodieja_save_meta', '_rodieja_meta_nonce' );
		$val = get_post_meta( $post->ID, '_rodieja_marca_id', true );
		printf(
			'<p><label for="_rodieja_marca_id">%s</label><br /><input type="number" id="_rodieja_marca_id" name="_rodieja_marca_id" value="%s" class="widefat" /></p>',
			esc_html__( 'Marca post ID', 'rodieja-contable' ),
			esc_attr( (string) $val )
		);
	}

	public function render_generacion( \WP_Post $post ): void {
		wp_nonce_field( 'rodieja_save_meta', '_rodieja_meta_nonce' );
		foreach ( array( '_rodieja_modelo_id' => 'Modelo ID', '_rodieja_anio_inicio' => 'Año inicio', '_rodieja_anio_fin' => 'Año fin' ) as $key => $label ) {
			$val = get_post_meta( $post->ID, $key, true );
			printf(
				'<p><label for="%1$s">%2$s</label><br /><input type="number" id="%1$s" name="%1$s" value="%3$s" class="widefat" /></p>',
				esc_attr( $key ),
				esc_html( $label ),
				esc_attr( (string) $val )
			);
		}
	}

	public function save( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['_rodieja_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_rodieja_meta_nonce'] ) ), 'rodieja_save_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( RoleRegistrar::CAP_MANAGE ) && ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$keys = array(
			'_rodieja_codigo',
			'_rodieja_generacion_id',
			'_rodieja_anio',
			'_rodieja_precio_compra',
			'_rodieja_costo_grua',
			'_rodieja_comisiones',
			'_rodieja_costo_recuperado',
			'_rodieja_traccion',
			'_rodieja_transmision',
			'_rodieja_combustible',
			'_rodieja_estado',
			'_rodieja_fecha_ingreso',
			'_rodieja_precio_venta',
			'_rodieja_fecha',
			'_rodieja_categoria',
			'_rodieja_monto',
			'_rodieja_vehiculo_id',
			'_rodieja_repuesto_id',
			'_rodieja_marca_id',
			'_rodieja_modelo_id',
			'_rodieja_anio_inicio',
			'_rodieja_anio_fin',
			'_rodieja_stock',
			'_rodieja_precio',
		);

		foreach ( $keys as $key ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$is_money = str_contains( $key, 'precio' ) || str_contains( $key, 'monto' ) || str_contains( $key, 'costo' ) || str_contains( $key, 'comision' );
			if ( $is_money ) {
				update_post_meta( $post_id, $key, round( (float) $raw, 2 ) );
			} elseif ( str_ends_with( $key, '_id' ) || str_contains( $key, 'anio' ) || str_contains( $key, 'stock' ) ) {
				update_post_meta( $post_id, $key, absint( $raw ) );
			} else {
				update_post_meta( $post_id, $key, sanitize_text_field( (string) $raw ) );
			}
		}
	}
}
