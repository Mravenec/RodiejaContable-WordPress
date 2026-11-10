<?php
/**
 * Reportes · inventario resumen.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\PostTypes\PostTypeRegistrar;
use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\InventoryReportService;

final class InventorySummaryReportPage {

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private InventoryReportService $report
	) {}

	public function register(): void {
		// Rendered via AdminMenu.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$s = $this->report->summary();
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Inventory summary', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Aggregated with WP_Query + parts meta.', 'rodieja-contable' ) ); ?></p>
			<table class="widefat striped" style="max-width:520px">
				<tbody>
				<?php
				$rows = array(
					__( 'Vehicles in catalog', 'rodieja-contable' ) => (string) $s['vehiculos'],
					__( 'SKUs (parts)', 'rodieja-contable' )      => (string) $s['repuestos'],
					__( 'Units in stock', 'rodieja-contable' )     => (string) $s['unidades'],
					__( 'Valor inventario', 'rodieja-contable' )     => number_format_i18n( $s['valor_inventario'], 2 ),
					__( 'Critical stock SKUs', 'rodieja-contable' )    => (string) $s['criticos'],
				);
				foreach ( $rows as $label => $val ) :
					?>
					<tr><th><?php echo $this->escaper->html( (string) $label ); ?></th><td><?php echo $this->escaper->html( $val ); ?></td></tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
