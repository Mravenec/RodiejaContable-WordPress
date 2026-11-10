<?php
/**
 * Dashboard ejecutivo — counts + montos (native services).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class ExecutiveDashboardService {

	public function __construct(
		private DomainStatsService $stats,
		private FinanceTotalsService $finance,
		private InventoryReportService $inventory,
		private SalesReportService $sales,
		private ProfitabilityReportService $profit
	) {}

	/**
	 * @return array{
	 *   counts: array<string, int>,
	 *   finance_all: array{ingreso: float, egreso: float, balance: float, count: int},
	 *   finance_month: array{ingreso: float, egreso: float, balance: float, count: int},
	 *   inventory: array<string, int|float>,
	 *   sales_month: array<string, mixed>,
	 *   profitability: array<string, float|int>
	 * }
	 */
	public function snapshot(): array {
		$month = gmdate( 'Y-m' );
		return array(
			'counts'          => $this->stats->counts(),
			'finance_all'     => $this->finance->totals(),
			'finance_month'   => $this->finance->totals( $month . '-01' ),
			'inventory'       => $this->inventory->summary(),
			'sales_month'     => $this->sales->summary( $month ),
			'profitability'   => $this->profit->summary(),
		);
	}
}
