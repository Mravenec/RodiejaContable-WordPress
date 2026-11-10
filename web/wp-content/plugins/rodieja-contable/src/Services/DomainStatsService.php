<?php
/**
 * Aggregate native CPT publish counts for admin / REST smoke.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class DomainStatsService {

	/**
	 * @return array<string, int>
	 */
	public function counts(): array {
		$types = array(
			'rodieja_marca',
			'rodieja_modelo',
			'rodieja_generacion',
			'rodieja_vehiculo',
			'rodieja_repuesto',
			'rodieja_empleado',
			'rodieja_transaccion',
			'rodieja_pedido',
		);
		$out = array();
		foreach ( $types as $type ) {
			$out[ $type ] = (int) ( wp_count_posts( $type )->publish ?? 0 );
		}
		return $out;
	}
}
