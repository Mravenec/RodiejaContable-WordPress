<?php
/**
 * Reportes · ventas.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\PostTypes\PostTypeRegistrar;
use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\SalesReportService;

final class SalesReportPage {

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private SalesReportService $report
	) {}

	public function register(): void {
		// Rendered via AdminMenu.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$all   = $this->report->summary();
		$month = $this->report->summary( gmdate( 'Y-m' ) );
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Ventas', 'rodieja-contable' ) ); ?></h1>
			<table class="widefat striped" style="max-width:560px">
				<thead><tr><th></th><th><?php echo $this->escaper->html( __( 'Todo', 'rodieja-contable' ) ); ?></th><th><?php echo $this->escaper->html( __( 'Mes actual', 'rodieja-contable' ) ); ?></th></tr></thead>
				<tbody>
					<tr><th><?php echo $this->escaper->html( __( 'Pedidos', 'rodieja-contable' ) ); ?></th>
						<td><?php echo $this->escaper->html( (string) $all['pedidos'] ); ?></td>
						<td><?php echo $this->escaper->html( (string) $month['pedidos'] ); ?></td></tr>
					<tr><th><?php echo $this->escaper->html( __( 'Total pedidos', 'rodieja-contable' ) ); ?></th>
						<td><?php echo $this->escaper->html( number_format_i18n( $all['total_pedidos'], 2 ) ); ?></td>
						<td><?php echo $this->escaper->html( number_format_i18n( $month['total_pedidos'], 2 ) ); ?></td></tr>
					<tr><th><?php echo $this->escaper->html( __( 'Ingresos (transacciones)', 'rodieja-contable' ) ); ?></th>
						<td><?php echo $this->escaper->html( number_format_i18n( $all['ingresos'], 2 ) ); ?></td>
						<td><?php echo $this->escaper->html( number_format_i18n( $month['ingresos'], 2 ) ); ?></td></tr>
				</tbody>
			</table>
			<h2><?php echo $this->escaper->html( __( 'Pedidos por origen (total)', 'rodieja-contable' ) ); ?></h2>
			<table class="widefat striped" style="max-width:400px">
				<tbody>
				<?php foreach ( $all['por_origen'] as $origen => $n ) : ?>
					<tr><th><?php echo $this->escaper->html( $origen ); ?></th><td><?php echo $this->escaper->html( (string) $n ); ?></td></tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
