<?php
/**
 * register_post_meta — native REST-visible meta (no custom tables).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Meta;

final class MetaRegistrar {

	public function register(): void {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_meta(): void {
		$this->string_meta( 'rodieja_modelo', 'rodieja_marca_id' );
		$this->string_meta( 'rodieja_generacion', 'rodieja_modelo_id' );
		$this->number_meta( 'rodieja_generacion', 'rodieja_anio_inicio' );
		$this->number_meta( 'rodieja_generacion', 'rodieja_anio_fin' );

		$this->string_meta( 'rodieja_vehiculo', 'rodieja_codigo' );
		$this->string_meta( 'rodieja_vehiculo', 'rodieja_generacion_id' );
		$this->number_meta( 'rodieja_vehiculo', 'rodieja_anio' );
		$this->number_meta( 'rodieja_vehiculo', 'rodieja_precio_compra' );
		$this->number_meta( 'rodieja_vehiculo', 'rodieja_costo_grua' );
		$this->number_meta( 'rodieja_vehiculo', 'rodieja_comisiones' );
		$this->number_meta( 'rodieja_vehiculo', 'rodieja_costo_recuperado' );
		$this->string_meta( 'rodieja_vehiculo', 'rodieja_traccion' );
		$this->string_meta( 'rodieja_vehiculo', 'rodieja_transmision' );
		$this->string_meta( 'rodieja_vehiculo', 'rodieja_combustible' );
		$this->string_meta( 'rodieja_vehiculo', 'rodieja_fecha_ingreso' );

		$this->string_meta( 'rodieja_repuesto', 'rodieja_vehiculo_id' );
		$this->number_meta( 'rodieja_repuesto', 'rodieja_precio' );
		$this->number_meta( 'rodieja_repuesto', 'rodieja_stock' );

		$this->string_meta( 'rodieja_empleado', 'rodieja_email' );
		$this->string_meta( 'rodieja_empleado', 'rodieja_telefono' );
		$this->string_meta( 'rodieja_empleado', 'rodieja_puesto' );
		$this->string_meta( 'rodieja_empleado', 'rodieja_provincia' );
		$this->string_meta( 'rodieja_empleado', 'rodieja_canton' );
		$this->string_meta( 'rodieja_empleado', 'rodieja_lat' );
		$this->string_meta( 'rodieja_empleado', 'rodieja_lng' );

		$this->string_meta( 'rodieja_vehiculo', 'rodieja_provincia' );
		$this->string_meta( 'rodieja_vehiculo', 'rodieja_canton' );
		$this->string_meta( 'rodieja_vehiculo', 'rodieja_lat' );
		$this->string_meta( 'rodieja_vehiculo', 'rodieja_lng' );

		$this->number_meta( 'rodieja_transaccion', 'rodieja_monto' );
		$this->string_meta( 'rodieja_transaccion', 'rodieja_fecha' );
		$this->string_meta( 'rodieja_transaccion', 'rodieja_vehiculo_id' );
		$this->string_meta( 'rodieja_transaccion', 'rodieja_empleado_id' );
		$this->string_meta( 'rodieja_transaccion', 'rodieja_sentido' ); // ingreso|egreso

		$this->register_pedido_meta();
		$this->register_pedido_item_meta();

		$this->array_int_meta( 'rodieja_vehiculo', 'rodieja_gallery_ids' );
		$this->array_int_meta( 'rodieja_repuesto', 'rodieja_gallery_ids' );
		$this->number_meta( 'rodieja_empleado', 'rodieja_user_id' );
	}

	private function register_pedido_meta(): void {
		$string_keys = array(
			'rodieja_origen',
			'rodieja_estado_pedido',
			'rodieja_referencia_ext',
			'rodieja_numero_pedido',
			'rodieja_cotizacion_id',
			'rodieja_siniestro',
			'rodieja_wan',
			'rodieja_aseguradora',
			'rodieja_vehiculo_label',
			'rodieja_vin',
			'rodieja_comision_pagada',
			'rodieja_fecha_pago_comision',
			'rodieja_referencia_pago_comision',
			'rodieja_notas',
		);
		foreach ( $string_keys as $key ) {
			$this->string_meta( 'rodieja_pedido', $key );
		}
		foreach ( array( 'rodieja_total_pedido', 'rodieja_comision_calculada', 'rodieja_porcentaje_comision' ) as $key ) {
			$this->number_meta( 'rodieja_pedido', $key );
		}
	}

	private function register_pedido_item_meta(): void {
		$this->string_meta( 'rodieja_pedido_item', 'rodieja_pedido_id' );
		$this->string_meta( 'rodieja_pedido_item', 'rodieja_repuesto_id' );
		$this->string_meta( 'rodieja_pedido_item', 'rodieja_descripcion' );
		$this->string_meta( 'rodieja_pedido_item', 'rodieja_tipo_pieza' );
		$this->number_meta( 'rodieja_pedido_item', 'rodieja_precio_ofrecido' );
		$this->number_meta( 'rodieja_pedido_item', 'rodieja_cantidad' );
		$this->number_meta( 'rodieja_pedido_item', 'rodieja_dias_entrega' );
	}

	private function string_meta( string $type, string $key ): void {
		register_post_meta(
			$type,
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => array( $this, 'can_edit' ),
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}

	private function array_int_meta( string $type, string $key ): void {
		register_post_meta(
			$type,
			$key,
			array(
				'type'              => 'array',
				'single'            => true,
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
				'auth_callback'     => array( $this, 'can_edit' ),
				'sanitize_callback' => array( $this, 'sanitize_int_array' ),
			)
		);
	}

	/**
	 * @return list<int>
	 */
	public function sanitize_int_array( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}
		return array_values( array_map( 'absint', $value ) );
	}

	private function number_meta( string $type, string $key ): void {
		register_post_meta(
			$type,
			$key,
			array(
				'type'              => 'number',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => array( $this, 'can_edit' ),
				'sanitize_callback' => array( $this, 'sanitize_number' ),
			)
		);
	}

	public function sanitize_number( mixed $value ): float {
		return (float) $value;
	}

	public function can_edit(): bool {
		return current_user_can( 'manage_rodieja_contable' ) || current_user_can( 'manage_options' );
	}
}
