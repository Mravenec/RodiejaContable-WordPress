<?php
/**
 * Featured image support for catalog CPTs (repuesto + vehículo reaffirm).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\PostTypes;

final class CatalogThumbnailSupport {

	public function register(): void {
		add_action( 'init', array( $this, 'supports' ), 20 );
	}

	public function supports(): void {
		add_post_type_support( 'wl_repuesto', 'thumbnail' );
		add_post_type_support( 'wl_vehiculo', 'thumbnail' );
	}
}
