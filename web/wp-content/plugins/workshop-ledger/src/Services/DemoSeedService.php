<?php
/**
 * Seeds demo content via native wp_insert_post / meta / terms only.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

final class DemoSeedService {

	public function seed_if_empty(): array {
		if ( wp_count_posts( 'wl_vehiculo' )->publish > 0 ) {
			return array( 'seeded' => false, 'reason' => 'already_has_vehicles' );
		}

		$marca_id = $this->post( 'wl_marca', 'Toyota' );
		$modelo_id = $this->post( 'wl_modelo', 'Corolla', array( 'wl_marca_id' => (string) $marca_id ) );
		$gen_id = $this->post(
			'wl_generacion',
			'12th Gen',
			array(
				'wl_modelo_id'  => (string) $modelo_id,
				'wl_anio_inicio' => 2019,
				'wl_anio_fin'    => 2024,
			)
		);

		foreach ( array( 'DISPONIBLE', 'VENDIDO', 'DESARMADO', 'REPARACION' ) as $estado ) {
			if ( ! term_exists( $estado, 'wl_estado_vehiculo' ) ) {
				wp_insert_term( $estado, 'wl_estado_vehiculo' );
			}
		}
		foreach ( array( 'Motor', 'Puerta', 'Parachoques' ) as $parte ) {
			if ( ! term_exists( $parte, 'wl_parte' ) ) {
				wp_insert_term( $parte, 'wl_parte' );
			}
		}
		foreach ( array( 'Ingreso', 'Egreso' ) as $tipo ) {
			if ( ! term_exists( $tipo, 'wl_tipo_tx' ) ) {
				wp_insert_term( $tipo, 'wl_tipo_tx' );
			}
		}

		$vehiculo_id = $this->post(
			'wl_vehiculo',
			'Toyota Corolla 2020',
			array(
				'wl_codigo'          => 'VEH-DEMO-001',
				'wl_generacion_id'   => (string) $gen_id,
				'wl_anio'            => 2020,
				'wl_precio_compra'   => 3500000,
				'wl_costo_grua'      => 50000,
				'wl_comisiones'      => 100000,
				'wl_costo_recuperado'=> 0,
				'wl_traccion'        => '4x2',
				'wl_transmision'     => 'Automatico',
				'wl_combustible'     => 'Gasolina',
				'wl_fecha_ingreso'   => gmdate( 'Y-m-d' ),
			)
		);
		wp_set_object_terms( $vehiculo_id, 'DISPONIBLE', 'wl_estado_vehiculo' );

		$emp_id = $this->post(
			'wl_empleado',
			'Ana Contadora',
			array(
				'wl_email'    => 'ana@workshop.local',
				'wl_telefono' => '8888-0000',
				'wl_puesto'   => 'Contador',
			)
		);

		$rep_id = $this->post(
			'wl_repuesto',
			'Puerta delantera izq',
			array(
				'wl_vehiculo_id' => (string) $vehiculo_id,
				'wl_precio'      => 150000,
				'wl_stock'       => 1,
			)
		);
		wp_set_object_terms( $rep_id, 'Puerta', 'wl_parte' );

		$tx_id = $this->post(
			'wl_transaccion',
			'Compra vehículo demo',
			array(
				'wl_monto'        => 3650000,
				'wl_fecha'        => gmdate( 'Y-m-d' ),
				'wl_vehiculo_id'  => (string) $vehiculo_id,
				'wl_empleado_id'  => (string) $emp_id,
				'wl_sentido'      => 'egreso',
			)
		);
		wp_set_object_terms( $tx_id, 'Egreso', 'wl_tipo_tx' );

		$pedido_id = $this->post(
			'wl_pedido',
			'Pedido demo manual',
			array(
				'wl_origen'         => 'manual',
				'wl_estado_pedido'  => 'abierto',
				'wl_referencia_ext' => 'DEMO-1',
			)
		);

		return array(
			'seeded'   => true,
			'marca'    => $marca_id,
			'vehiculo' => $vehiculo_id,
			'pedido'   => $pedido_id,
		);
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
