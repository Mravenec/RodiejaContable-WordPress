<?php
/**
 * Seeds a minimal native CPT demo once (WP-CLI / admin tool).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class DemoSeedService {

	public const OPTION = 'rodieja_contable_demo_seeded';

	public function seed_if_needed(): bool {
		if ( get_option( self::OPTION ) ) {
			return false;
		}
		return $this->seed();
	}

	public function seed(): bool {
		$marca_id = wp_insert_post(
			array(
				'post_type'   => 'rodieja_marca',
				'post_title'  => 'Toyota',
				'post_status' => 'publish',
			),
			true
		);
		if ( is_wp_error( $marca_id ) ) {
			return false;
		}

		$modelo_id = wp_insert_post(
			array(
				'post_type'   => 'rodieja_modelo',
				'post_title'  => 'Corolla',
				'post_status' => 'publish',
				'meta_input'  => array( '_rodieja_marca_id' => (int) $marca_id ),
			),
			true
		);
		if ( is_wp_error( $modelo_id ) ) {
			return false;
		}

		$gen_id = wp_insert_post(
			array(
				'post_type'   => 'rodieja_generacion',
				'post_title'  => '12th Gen',
				'post_status' => 'publish',
				'meta_input'  => array(
					'_rodieja_modelo_id'  => (int) $modelo_id,
					'_rodieja_anio_inicio'=> 2019,
					'_rodieja_anio_fin'   => 2024,
				),
			),
			true
		);
		if ( is_wp_error( $gen_id ) ) {
			return false;
		}

		$veh_id = wp_insert_post(
			array(
				'post_type'   => 'rodieja_vehiculo',
				'post_title'  => 'VEH-DEMO-001',
				'post_status' => 'publish',
				'meta_input'  => array(
					'_rodieja_codigo'        => 'VEH-DEMO-001',
					'_rodieja_generacion_id' => (int) $gen_id,
					'_rodieja_anio'          => 2021,
					'_rodieja_precio_compra' => 3500000,
					'_rodieja_estado'        => 'DISPONIBLE',
					'_rodieja_fecha_ingreso' => gmdate( 'Y-m-d' ),
					'_rodieja_combustible'   => 'Gasolina',
					'_rodieja_transmision'   => 'Automatico',
					'_rodieja_traccion'      => '4x2',
				),
			),
			true
		);
		if ( is_wp_error( $veh_id ) ) {
			return false;
		}

		if ( ! term_exists( 'venta-repuesto', 'rodieja_tipo_tx' ) ) {
			wp_insert_term( 'Venta repuesto', 'rodieja_tipo_tx', array( 'slug' => 'venta-repuesto' ) );
		}

		$tx_id = wp_insert_post(
			array(
				'post_type'   => 'rodieja_transaccion',
				'post_title'  => 'TX-DEMO-001',
				'post_status' => 'publish',
				'meta_input'  => array(
					'_rodieja_codigo'      => 'TX-DEMO-001',
					'_rodieja_fecha'       => gmdate( 'Y-m-d' ),
					'_rodieja_categoria'   => 'INGRESO',
					'_rodieja_monto'       => 125000,
					'_rodieja_vehiculo_id' => (int) $veh_id,
					'_rodieja_estado'      => 'COMPLETADA',
				),
			),
			true
		);
		if ( is_wp_error( $tx_id ) ) {
			return false;
		}
		wp_set_object_terms( (int) $tx_id, 'venta-repuesto', 'rodieja_tipo_tx' );

		update_option( self::OPTION, 1, false );
		return true;
	}
}
