<?php
/**
 * Finanzas dashboard — montos via FinanceTotalsService (WP_Query).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\FinanceTotalsService;

final class FinanceDashboardPage {

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private FinanceTotalsService $totals
	) {}

	public function register(): void {
		// Rendered via AdminMenu submenu.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		$all   = $this->totals->totals();
		$month = $this->totals->totals( gmdate( 'Y-m' ) );
		?>
		<div class="wrap wl-admin-wrap">
			<h1><?php echo $this->escaper->html( __( 'Dashboard montos', 'workshop-ledger' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Totals computed with WP_Query + post meta (no custom tables).', 'workshop-ledger' ) ); ?></p>
			<table class="widefat striped" style="max-width:520px">
				<thead><tr><th></th><th><?php echo $this->escaper->html( __( 'Todo', 'workshop-ledger' ) ); ?></th><th><?php echo $this->escaper->html( __( 'Mes actual', 'workshop-ledger' ) ); ?></th></tr></thead>
				<tbody>
					<tr><th><?php echo $this->escaper->html( __( 'Incomes', 'workshop-ledger' ) ); ?></th>
						<td><?php echo $this->escaper->html( number_format_i18n( $all['ingreso'], 2 ) ); ?></td>
						<td><?php echo $this->escaper->html( number_format_i18n( $month['ingreso'], 2 ) ); ?></td></tr>
					<tr><th><?php echo $this->escaper->html( __( 'Expenses', 'workshop-ledger' ) ); ?></th>
						<td><?php echo $this->escaper->html( number_format_i18n( $all['egreso'], 2 ) ); ?></td>
						<td><?php echo $this->escaper->html( number_format_i18n( $month['egreso'], 2 ) ); ?></td></tr>
					<tr><th><?php echo $this->escaper->html( __( 'Balance', 'workshop-ledger' ) ); ?></th>
						<td><?php echo $this->escaper->html( number_format_i18n( $all['balance'], 2 ) ); ?></td>
						<td><?php echo $this->escaper->html( number_format_i18n( $month['balance'], 2 ) ); ?></td></tr>
					<tr><th><?php echo $this->escaper->html( __( 'Transactions', 'workshop-ledger' ) ); ?></th>
						<td><?php echo $this->escaper->html( (string) $all['count'] ); ?></td>
						<td><?php echo $this->escaper->html( (string) $month['count'] ); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
