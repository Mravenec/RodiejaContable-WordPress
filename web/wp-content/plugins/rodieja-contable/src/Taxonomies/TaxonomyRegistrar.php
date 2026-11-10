<?php
/**
 * Native taxonomies.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Taxonomies;

final class TaxonomyRegistrar {

	public function register(): void {
		add_action( 'init', array( $this, 'register_taxes' ) );
	}

	public function register_taxes(): void {
		register_taxonomy(
			'rodieja_parte',
			array( 'rodieja_repuesto', 'rodieja_vehiculo' ),
			array(
				'labels'            => array(
					'name'          => __( 'Parts', 'rodieja-contable' ),
					'singular_name' => __( 'Part', 'rodieja-contable' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => false,
			)
		);

		register_taxonomy(
			'rodieja_tipo_tx',
			array( 'rodieja_transaccion' ),
			array(
				'labels'            => array(
					'name'          => __( 'Transaction types', 'rodieja-contable' ),
					'singular_name' => __( 'Transaction type', 'rodieja-contable' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
			)
		);

		register_taxonomy(
			'rodieja_estado_vehiculo',
			array( 'rodieja_vehiculo' ),
			array(
				'labels'            => array(
					'name'          => __( 'Vehicle statuses', 'rodieja-contable' ),
					'singular_name' => __( 'Status', 'rodieja-contable' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
			)
		);
	}
}
