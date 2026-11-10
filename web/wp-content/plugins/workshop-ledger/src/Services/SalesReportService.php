<?php
/**
 * Ventas — pedidos + ingresos vía WP_Query/meta.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

final class SalesReportService {

	public function __construct( private FinanceTotalsService $finance ) {}

	/**
	 * @return array{
	 *   pedidos: int,
	 *   total_pedidos: float,
	 *   por_origen: array<string, int>,
	 *   ingresos: float,
	 *   transacciones_ingreso: int
	 * }
	 */
	public function summary( ?string $month_ym = null ): array {
		$args = array(
			'post_type'      => 'wl_pedido',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);
		if ( $month_ym ) {
			$args['date_query'] = array(
				array(
					'year'  => (int) substr( $month_ym, 0, 4 ),
					'month' => (int) substr( $month_ym, 5, 2 ),
				),
			);
		}
		$ids          = get_posts( $args );
		$total        = 0.0;
		$por_origen   = array();
		foreach ( $ids as $id ) {
			$total += (float) get_post_meta( (int) $id, 'wl_total_pedido', true );
			$origen = (string) get_post_meta( (int) $id, 'wl_origen', true );
			if ( '' === $origen ) {
				$origen = 'manual';
			}
			$por_origen[ $origen ] = ( $por_origen[ $origen ] ?? 0 ) + 1;
		}
		$finance_key = $month_ym ? $month_ym . '-01' : null;
		$fin         = $this->finance->totals( $finance_key );
		return array(
			'pedidos'               => count( $ids ),
			'total_pedidos'         => $total,
			'por_origen'            => $por_origen,
			'ingresos'              => $fin['ingreso'],
			'transacciones_ingreso' => $fin['count'],
		);
	}
}
