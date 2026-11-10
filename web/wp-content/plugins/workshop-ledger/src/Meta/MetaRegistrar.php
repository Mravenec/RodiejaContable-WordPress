<?php
/**
 * register_post_meta — native REST-visible meta (no custom tables).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Meta;

final class MetaRegistrar {

	public function register(): void {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_meta(): void {
		$this->string_meta( 'wl_modelo', 'wl_marca_id' );
		$this->string_meta( 'wl_generacion', 'wl_modelo_id' );
		$this->number_meta( 'wl_generacion', 'wl_anio_inicio' );
		$this->number_meta( 'wl_generacion', 'wl_anio_fin' );

		$this->string_meta( 'wl_vehiculo', 'wl_codigo' );
		$this->string_meta( 'wl_vehiculo', 'wl_generacion_id' );
		$this->number_meta( 'wl_vehiculo', 'wl_anio' );
		$this->number_meta( 'wl_vehiculo', 'wl_precio_compra' );
		$this->number_meta( 'wl_vehiculo', 'wl_costo_grua' );
		$this->number_meta( 'wl_vehiculo', 'wl_comisiones' );
		$this->number_meta( 'wl_vehiculo', 'wl_costo_recuperado' );
		$this->string_meta( 'wl_vehiculo', 'wl_traccion' );
		$this->string_meta( 'wl_vehiculo', 'wl_transmision' );
		$this->string_meta( 'wl_vehiculo', 'wl_combustible' );
		$this->string_meta( 'wl_vehiculo', 'wl_cilindraje' );
		$this->string_meta( 'wl_vehiculo', 'wl_fecha_ingreso' );

		$this->string_meta( 'wl_repuesto', 'wl_vehiculo_id' );
		$this->string_meta( 'wl_repuesto', 'wl_modo' ); // con_vehiculo|sin_vehiculo
		$this->string_meta( 'wl_repuesto', 'wl_generacion_id' );
		$this->number_meta( 'wl_repuesto', 'wl_precio' ); // legacy alias of sale
		$this->number_meta( 'wl_repuesto', 'wl_precio_costo' );
		$this->number_meta( 'wl_repuesto', 'wl_precio_venta' );
		$this->number_meta( 'wl_repuesto', 'wl_stock' );
		$this->string_meta( 'wl_repuesto', 'wl_condicion' );
		$this->string_meta( 'wl_repuesto', 'wl_originalidad' );
		$this->string_meta( 'wl_repuesto', 'wl_bodega' );
		$this->string_meta( 'wl_repuesto', 'wl_zona' );
		$this->string_meta( 'wl_repuesto', 'wl_estante' );
		$this->string_meta( 'wl_repuesto', 'wl_nivel' );
		$this->string_meta( 'wl_repuesto', 'wl_posicion' );

		$this->string_meta( 'wl_empleado', 'wl_email' );
		$this->string_meta( 'wl_empleado', 'wl_telefono' );
		$this->string_meta( 'wl_empleado', 'wl_puesto' );
		$this->string_meta( 'wl_empleado', 'wl_provincia' );
		$this->string_meta( 'wl_empleado', 'wl_canton' );
		$this->string_meta( 'wl_empleado', 'wl_lat' );
		$this->string_meta( 'wl_empleado', 'wl_lng' );

		$this->string_meta( 'wl_vehiculo', 'wl_provincia' );
		$this->string_meta( 'wl_vehiculo', 'wl_canton' );
		$this->string_meta( 'wl_vehiculo', 'wl_lat' );
		$this->string_meta( 'wl_vehiculo', 'wl_lng' );

		$this->number_meta( 'wl_transaccion', 'wl_monto' );
		$this->string_meta( 'wl_transaccion', 'wl_fecha' );
		$this->string_meta( 'wl_transaccion', 'wl_vehiculo_id' );
		$this->string_meta( 'wl_transaccion', 'wl_empleado_id' );
		$this->string_meta( 'wl_transaccion', 'wl_repuesto_id' );
		$this->number_meta( 'wl_transaccion', 'wl_cantidad_repuesto' );
		$this->string_meta( 'wl_transaccion', 'wl_sentido' ); // ingreso|egreso
		$this->string_meta( 'wl_transaccion', 'wl_metodo_pago' );
		$this->string_meta( 'wl_transaccion', 'wl_tipo_transferencia' );
		$this->string_meta( 'wl_transaccion', 'wl_proveedor' );
		$this->string_meta( 'wl_transaccion', 'wl_descripcion' );

		$this->register_pedido_meta();
		$this->register_pedido_item_meta();

		$this->array_int_meta( 'wl_vehiculo', 'wl_gallery_ids' );
		$this->array_int_meta( 'wl_repuesto', 'wl_gallery_ids' );
		$this->number_meta( 'wl_empleado', 'wl_user_id' );
	}

	private function register_pedido_meta(): void {
		$string_keys = array(
			'wl_origen',
			'wl_estado_pedido',
			'wl_referencia_ext',
			'wl_numero_pedido',
			'wl_cotizacion_id',
			'wl_siniestro',
			'wl_wan',
			'wl_aseguradora',
			'wl_vehiculo_label',
			'wl_vin',
			'wl_comision_pagada',
			'wl_fecha_pago_comision',
			'wl_referencia_pago_comision',
			'wl_notas',
		);
		foreach ( $string_keys as $key ) {
			$this->string_meta( 'wl_pedido', $key );
		}
		foreach ( array( 'wl_total_pedido', 'wl_comision_calculada', 'wl_porcentaje_comision' ) as $key ) {
			$this->number_meta( 'wl_pedido', $key );
		}
	}

	private function register_pedido_item_meta(): void {
		$this->string_meta( 'wl_pedido_item', 'wl_pedido_id' );
		$this->string_meta( 'wl_pedido_item', 'wl_repuesto_id' );
		$this->string_meta( 'wl_pedido_item', 'wl_descripcion' );
		$this->string_meta( 'wl_pedido_item', 'wl_tipo_pieza' );
		$this->number_meta( 'wl_pedido_item', 'wl_precio_ofrecido' );
		$this->number_meta( 'wl_pedido_item', 'wl_cantidad' );
		$this->number_meta( 'wl_pedido_item', 'wl_dias_entrega' );
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
		return current_user_can( 'manage_workshop_ledger' ) || current_user_can( 'manage_options' );
	}
}
