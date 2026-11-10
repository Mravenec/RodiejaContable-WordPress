<?php
/**
 * Native taxonomies.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Taxonomies;

final class TaxonomyRegistrar {

	public function register(): void {
		add_action( 'init', array( $this, 'register_taxes' ) );
	}

	public function register_taxes(): void {
		register_taxonomy(
			'wl_parte',
			array( 'wl_repuesto', 'wl_vehiculo' ),
			array(
				'labels'            => array(
					'name'          => __( 'Parts', 'workshop-ledger' ),
					'singular_name' => __( 'Part', 'workshop-ledger' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => false,
			)
		);

		register_taxonomy(
			'wl_tipo_tx',
			array( 'wl_transaccion' ),
			array(
				'labels'            => array(
					'name'          => __( 'Transaction types', 'workshop-ledger' ),
					'singular_name' => __( 'Transaction type', 'workshop-ledger' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => true,
			)
		);

		register_taxonomy(
			'wl_estado_vehiculo',
			array( 'wl_vehiculo' ),
			array(
				'labels'            => array(
					'name'          => __( 'Vehicle statuses', 'workshop-ledger' ),
					'singular_name' => __( 'Status', 'workshop-ledger' ),
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
