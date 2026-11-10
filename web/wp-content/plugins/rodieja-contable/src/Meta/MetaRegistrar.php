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

		$this->number_meta( 'rodieja_transaccion', 'rodieja_monto' );
		$this->string_meta( 'rodieja_transaccion', 'rodieja_fecha' );
		$this->string_meta( 'rodieja_transaccion', 'rodieja_vehiculo_id' );
		$this->string_meta( 'rodieja_transaccion', 'rodieja_empleado_id' );
		$this->string_meta( 'rodieja_transaccion', 'rodieja_sentido' ); // ingreso|egreso

		$this->string_meta( 'rodieja_pedido', 'rodieja_origen' ); // audatex|ins|manual
		$this->string_meta( 'rodieja_pedido', 'rodieja_estado_pedido' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_referencia_ext' );
		// Audatex-shape (native meta; no live sync).
		$this->string_meta( 'rodieja_pedido', 'rodieja_numero_pedido' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_cotizacion_id' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_siniestro' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_wan' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_aseguradora' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_vehiculo_label' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_vin' );
		$this->number_meta( 'rodieja_pedido', 'rodieja_total_pedido' );
		$this->number_meta( 'rodieja_pedido', 'rodieja_comision_calculada' );
		$this->number_meta( 'rodieja_pedido', 'rodieja_porcentaje_comision' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_comision_pagada' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_fecha_pago_comision' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_referencia_pago_comision' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_notas' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_detalle_json' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_user_id' );
		$this->string_meta( 'rodieja_pedido', 'rodieja_transaccion_id' );

		$this->string_meta( 'rodieja_pedido_item', 'rodieja_pedido_id' );
		$this->string_meta( 'rodieja_pedido_item', 'rodieja_repuesto_id' );
		$this->number_meta( 'rodieja_pedido_item', 'rodieja_precio_ofrecido' );
		$this->number_meta( 'rodieja_pedido_item', 'rodieja_cantidad' );
		$this->string_meta( 'rodieja_pedido_item', 'rodieja_descripcion' );
		$this->string_meta( 'rodieja_pedido_item', 'rodieja_tipo_pieza' );
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
