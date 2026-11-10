<?php
/**
 * Seeds demo content via native wp_insert_post / meta / terms only.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class DemoSeedService {

	/**
	 * Seed when empty, or fill missing domain posts if a partial seed exists.
	 *
	 * @return array<string, mixed>
	 */
	public function seed_if_empty(): array {
		$vehicle_count = (int) ( wp_count_posts( 'rodieja_vehiculo' )->publish ?? 0 );
		if ( 0 === $vehicle_count ) {
			return $this->seed_full();
		}
		return $this->ensure_missing();
	}

	/**
	 * @return array<string, mixed>
	 */
	private function seed_full(): array {
		$marca_id  = $this->post( 'rodieja_marca', 'Toyota' );
		$modelo_id = $this->post( 'rodieja_modelo', 'Corolla', array( 'rodieja_marca_id' => (string) $marca_id ) );
		$gen_id    = $this->post(
			'rodieja_generacion',
			'12th Gen',
			array(
				'rodieja_modelo_id'   => (string) $modelo_id,
				'rodieja_anio_inicio' => 2019,
				'rodieja_anio_fin'    => 2024,
			)
		);

		$this->ensure_terms();

		$vehiculo_id = $this->post(
			'rodieja_vehiculo',
			'Toyota Corolla 2020',
			array(
				'rodieja_codigo'           => 'VEH-DEMO-001',
				'rodieja_generacion_id'    => (string) $gen_id,
				'rodieja_anio'             => 2020,
				'rodieja_precio_compra'    => 3500000,
				'rodieja_costo_grua'       => 50000,
				'rodieja_comisiones'       => 100000,
				'rodieja_costo_recuperado' => 0,
				'rodieja_traccion'         => '4x2',
				'rodieja_transmision'      => 'Automatico',
				'rodieja_combustible'      => 'Gasolina',
				'rodieja_fecha_ingreso'    => gmdate( 'Y-m-d' ),
			)
		);
		wp_set_object_terms( $vehiculo_id, 'DISPONIBLE', 'rodieja_estado_vehiculo' );

		$emp_id = $this->post(
			'rodieja_empleado',
			'Ana Contadora',
			array(
				'rodieja_email'    => 'ana@rodieja.local',
				'rodieja_telefono' => '8888-0000',
				'rodieja_puesto'   => 'Contador',
			)
		);

		$rep_id = $this->post(
			'rodieja_repuesto',
			'Puerta delantera izq',
			array(
				'rodieja_vehiculo_id' => (string) $vehiculo_id,
				'rodieja_precio'      => 150000,
				'rodieja_stock'       => 1,
			)
		);
		wp_set_object_terms( $rep_id, 'Puerta', 'rodieja_parte' );

		$tx_id = $this->post(
			'rodieja_transaccion',
			'Compra vehículo demo',
			array(
				'rodieja_monto'       => 3650000,
				'rodieja_fecha'       => gmdate( 'Y-m-d' ),
				'rodieja_vehiculo_id' => (string) $vehiculo_id,
				'rodieja_empleado_id' => (string) $emp_id,
				'rodieja_sentido'     => 'egreso',
			)
		);
		wp_set_object_terms( $tx_id, 'Egreso', 'rodieja_tipo_tx' );

		$pedido_id = $this->post(
			'rodieja_pedido',
			'Pedido demo manual',
			array(
				'rodieja_origen'         => 'manual',
				'rodieja_estado_pedido'  => 'abierto',
				'rodieja_referencia_ext' => 'DEMO-1',
			)
		);

		return array(
			'seeded'   => true,
			'mode'     => 'full',
			'marca'    => $marca_id,
			'vehiculo' => $vehiculo_id,
			'pedido'   => $pedido_id,
		);
	}

	/**
	 * Fill pedidos / finance / parts when an older partial seed left gaps.
	 *
	 * @return array<string, mixed>
	 */
	private function ensure_missing(): array {
		$this->ensure_terms();
		$created = array();

		$vehicles = get_posts(
			array(
				'post_type'      => 'rodieja_vehiculo',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		$vehiculo_id = (int) ( $vehicles[0] ?? 0 );

		if ( 0 === (int) ( wp_count_posts( 'rodieja_empleado' )->publish ?? 0 ) ) {
			$created['empleado'] = $this->post(
				'rodieja_empleado',
				'Ana Contadora',
				array(
					'rodieja_email'    => 'ana@rodieja.local',
					'rodieja_telefono' => '8888-0000',
					'rodieja_puesto'   => 'Contador',
				)
			);
		}

		$empleados = get_posts(
			array(
				'post_type'      => 'rodieja_empleado',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		$emp_id = (int) ( $empleados[0] ?? 0 );

		if ( $vehiculo_id && 0 === (int) ( wp_count_posts( 'rodieja_repuesto' )->publish ?? 0 ) ) {
			$rep_id = $this->post(
				'rodieja_repuesto',
				'Puerta delantera izq',
				array(
					'rodieja_vehiculo_id' => (string) $vehiculo_id,
					'rodieja_precio'      => 150000,
					'rodieja_stock'       => 1,
				)
			);
			wp_set_object_terms( $rep_id, 'Puerta', 'rodieja_parte' );
			$created['repuesto'] = $rep_id;
		}

		if ( $vehiculo_id && 0 === (int) ( wp_count_posts( 'rodieja_transaccion' )->publish ?? 0 ) ) {
			$tx_id = $this->post(
				'rodieja_transaccion',
				'Compra vehículo demo',
				array(
					'rodieja_monto'       => 3650000,
					'rodieja_fecha'       => gmdate( 'Y-m-d' ),
					'rodieja_vehiculo_id' => (string) $vehiculo_id,
					'rodieja_empleado_id' => (string) $emp_id,
					'rodieja_sentido'     => 'egreso',
				)
			);
			wp_set_object_terms( $tx_id, 'Egreso', 'rodieja_tipo_tx' );
			$created['transaccion'] = $tx_id;
		}

		if ( 0 === (int) ( wp_count_posts( 'rodieja_pedido' )->publish ?? 0 ) ) {
			$created['pedido'] = $this->post(
				'rodieja_pedido',
				'Pedido demo manual',
				array(
					'rodieja_origen'         => 'manual',
					'rodieja_estado_pedido'  => 'abierto',
					'rodieja_referencia_ext' => 'DEMO-1',
				)
			);
		}

		return array(
			'seeded'  => ! empty( $created ),
			'mode'    => 'ensure',
			'created' => $created,
			'reason'  => empty( $created ) ? 'already_complete' : 'filled_missing',
		);
	}

	private function ensure_terms(): void {
		foreach ( array( 'DISPONIBLE', 'VENDIDO', 'DESARMADO', 'REPARACION' ) as $estado ) {
			if ( ! term_exists( $estado, 'rodieja_estado_vehiculo' ) ) {
				wp_insert_term( $estado, 'rodieja_estado_vehiculo' );
			}
		}
		foreach ( array( 'Motor', 'Puerta', 'Parachoques' ) as $parte ) {
			if ( ! term_exists( $parte, 'rodieja_parte' ) ) {
				wp_insert_term( $parte, 'rodieja_parte' );
			}
		}
		foreach ( array( 'Ingreso', 'Egreso' ) as $tipo ) {
			if ( ! term_exists( $tipo, 'rodieja_tipo_tx' ) ) {
				wp_insert_term( $tipo, 'rodieja_tipo_tx' );
			}
		}
	}

	/**
	 * @param array<string, mixed> $meta
	 */
	private function post( string $type, string $title, array $meta = array() ): int {
		$id = wp_insert_post(
			array(
				'post_type'   => $type,
				'post_title'  => $title,
				'post_status' => 'publish',
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return 0;
		}
		foreach ( $meta as $key => $value ) {
			update_post_meta( (int) $id, $key, $value );
		}
		return (int) $id;
	}
}
