<?php
/**
 * Native post meta (register_post_meta + show_in_rest).
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
		$this->int_meta( 'rodieja_modelo', '_rodieja_marca_id', 'Marca (post ID)' );
		$this->int_meta( 'rodieja_generacion', '_rodieja_modelo_id', 'Modelo (post ID)' );
		$this->int_meta( 'rodieja_generacion', '_rodieja_anio_inicio', 'Año inicio' );
		$this->int_meta( 'rodieja_generacion', '_rodieja_anio_fin', 'Año fin' );

		$this->int_meta( 'rodieja_vehiculo', '_rodieja_generacion_id', 'Generación (post ID)' );
		$this->string_meta( 'rodieja_vehiculo', '_rodieja_codigo', 'Código vehículo' );
		$this->int_meta( 'rodieja_vehiculo', '_rodieja_anio', 'Año' );
		$this->number_meta( 'rodieja_vehiculo', '_rodieja_precio_compra', 'Precio compra' );
		$this->number_meta( 'rodieja_vehiculo', '_rodieja_costo_grua', 'Costo grúa' );
		$this->number_meta( 'rodieja_vehiculo', '_rodieja_comisiones', 'Comisiones' );
		$this->number_meta( 'rodieja_vehiculo', '_rodieja_costo_recuperado', 'Costo recuperado' );
		$this->string_meta( 'rodieja_vehiculo', '_rodieja_traccion', 'Tracción' );
		$this->string_meta( 'rodieja_vehiculo', '_rodieja_transmision', 'Transmisión' );
		$this->string_meta( 'rodieja_vehiculo', '_rodieja_combustible', 'Combustible' );
		$this->string_meta( 'rodieja_vehiculo', '_rodieja_estado', 'Estado' );
		$this->string_meta( 'rodieja_vehiculo', '_rodieja_fecha_ingreso', 'Fecha ingreso' );
		$this->number_meta( 'rodieja_vehiculo', '_rodieja_precio_venta', 'Precio venta' );

		$this->string_meta( 'rodieja_repuesto', '_rodieja_codigo', 'Código repuesto' );
		$this->number_meta( 'rodieja_repuesto', '_rodieja_stock', 'Stock' );
		$this->number_meta( 'rodieja_repuesto', '_rodieja_precio', 'Precio' );
		$this->int_meta( 'rodieja_repuesto', '_rodieja_vehiculo_id', 'Vehículo origen (post ID)' );

		$this->string_meta( 'rodieja_transaccion', '_rodieja_codigo', 'Código transacción' );
		$this->string_meta( 'rodieja_transaccion', '_rodieja_fecha', 'Fecha' );
		$this->string_meta( 'rodieja_transaccion', '_rodieja_categoria', 'Categoría INGRESO|EGRESO' );
		$this->number_meta( 'rodieja_transaccion', '_rodieja_monto', 'Monto' );
		$this->int_meta( 'rodieja_transaccion', '_rodieja_vehiculo_id', 'Vehículo (post ID)' );
		$this->int_meta( 'rodieja_transaccion', '_rodieja_repuesto_id', 'Repuesto (post ID)' );
		$this->string_meta( 'rodieja_transaccion', '_rodieja_estado', 'Estado' );
	}

	private function string_meta( string $type, string $key, string $description ): void {
		register_post_meta(
			$type,
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'description'       => $description,
				'auth_callback'     => array( $this, 'can_edit' ),
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}

	private function int_meta( string $type, string $key, string $description ): void {
		register_post_meta(
			$type,
			$key,
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'description'       => $description,
				'auth_callback'     => array( $this, 'can_edit' ),
				'sanitize_callback' => 'absint',
			)
		);
	}

	private function number_meta( string $type, string $key, string $description ): void {
		register_post_meta(
			$type,
			$key,
			array(
				'type'              => 'number',
				'single'            => true,
				'show_in_rest'      => true,
				'description'       => $description,
				'auth_callback'     => array( $this, 'can_edit' ),
				'sanitize_callback' => static function ( $value ) {
					return round( (float) $value, 2 );
				},
			)
		);
	}

	public function can_edit(): bool {
		return current_user_can( 'manage_rodieja_contable' ) || current_user_can( 'manage_options' );
	}
}
