<?php
/**
 * Featured image support for catalog CPTs (repuesto + vehículo reaffirm).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\PostTypes;

final class CatalogThumbnailSupport {

	public function register(): void {
		add_action( 'init', array( $this, 'supports' ), 20 );
	}

	public function supports(): void {
		add_post_type_support( 'rodieja_repuesto', 'thumbnail' );
		add_post_type_support( 'rodieja_vehiculo', 'thumbnail' );
	}
}
