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
					'name'          => __( 'Partes', 'rodieja-contable' ),
					'singular_name' => __( 'Parte', 'rodieja-contable' ),
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
					'name'          => __( 'Tipos de transacción', 'rodieja-contable' ),
					'singular_name' => __( 'Tipo de transacción', 'rodieja-contable' ),
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
					'name'          => __( 'Estados vehículo', 'rodieja-contable' ),
					'singular_name' => __( 'Estado', 'rodieja-contable' ),
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
