<?php
/**
 * Aggregate ingresos/egresos via native WP_Query + post meta.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

final class FinanceTotalsService {

	/**
	 * @return array{ingreso: float, egreso: float, balance: float, count: int}
	 */
	public function totals( ?string $month_ymd = null ): array {
		$args = array(
			'post_type'      => 'wl_transaccion',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);
		if ( $month_ymd ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'wl_fecha',
					'value'   => substr( $month_ymd, 0, 7 ),
					'compare' => 'LIKE',
				),
			);
		}
		$ids     = get_posts( $args );
		$ingreso = 0.0;
		$egreso  = 0.0;
		foreach ( $ids as $id ) {
			$monto   = (float) get_post_meta( (int) $id, 'wl_monto', true );
			$sentido = (string) get_post_meta( (int) $id, 'wl_sentido', true );
			if ( 'ingreso' === $sentido ) {
				$ingreso += $monto;
			} else {
				$egreso += $monto;
			}
		}
		return array(
			'ingreso' => $ingreso,
			'egreso'  => $egreso,
			'balance' => $ingreso - $egreso,
			'count'   => count( $ids ),
		);
	}
}
