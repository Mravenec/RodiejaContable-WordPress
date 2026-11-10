<?php
/**
 * Finanzas dashboard — montos via FinanceTotalsService (WP_Query).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\FinanceTotalsService;

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
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$all   = $this->totals->totals();
		$month = $this->totals->totals( gmdate( 'Y-m' ) );
		?>
		<div class="wrap rodieja-admin-wrap">
			<h1><?php echo $this->escaper->html( __( 'Dashboard montos', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Totals computed with WP_Query + post meta (no custom tables).', 'rodieja-contable' ) ); ?></p>
			<table class="widefat striped" style="max-width:520px">
				<thead><tr><th></th><th><?php echo $this->escaper->html( __( 'Todo', 'rodieja-contable' ) ); ?></th><th><?php echo $this->escaper->html( __( 'Mes actual', 'rodieja-contable' ) ); ?></th></tr></thead>
				<tbody>
					<tr><th><?php echo $this->escaper->html( __( 'Incomes', 'rodieja-contable' ) ); ?></th>
						<td><?php echo $this->escaper->html( number_format_i18n( $all['ingreso'], 2 ) ); ?></td>
						<td><?php echo $this->escaper->html( number_format_i18n( $month['ingreso'], 2 ) ); ?></td></tr>
					<tr><th><?php echo $this->escaper->html( __( 'Expenses', 'rodieja-contable' ) ); ?></th>
						<td><?php echo $this->escaper->html( number_format_i18n( $all['egreso'], 2 ) ); ?></td>
						<td><?php echo $this->escaper->html( number_format_i18n( $month['egreso'], 2 ) ); ?></td></tr>
					<tr><th><?php echo $this->escaper->html( __( 'Balance', 'rodieja-contable' ) ); ?></th>
						<td><?php echo $this->escaper->html( number_format_i18n( $all['balance'], 2 ) ); ?></td>
						<td><?php echo $this->escaper->html( number_format_i18n( $month['balance'], 2 ) ); ?></td></tr>
					<tr><th><?php echo $this->escaper->html( __( 'Transactions', 'rodieja-contable' ) ); ?></th>
						<td><?php echo $this->escaper->html( (string) $all['count'] ); ?></td>
						<td><?php echo $this->escaper->html( (string) $month['count'] ); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
