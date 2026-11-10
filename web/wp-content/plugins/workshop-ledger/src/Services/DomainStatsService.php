<?php
/**
 * Aggregate native CPT publish counts for admin / REST smoke.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

final class DomainStatsService {

	/**
	 * @return array<string, int>
	 */
	public function counts(): array {
		$types = array(
			'wl_marca',
			'wl_modelo',
			'wl_generacion',
			'wl_vehiculo',
			'wl_repuesto',
			'wl_empleado',
			'wl_transaccion',
			'wl_pedido',
		);
		$out = array();
		foreach ( $types as $type ) {
			$out[ $type ] = (int) ( wp_count_posts( $type )->publish ?? 0 );
		}
		return $out;
	}
}
