<?php
/**
 * Reportes · inventario resumen.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\PostTypes\PostTypeRegistrar;
use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\InventoryReportService;

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
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		$s = $this->report->summary();
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Inventory summary', 'workshop-ledger' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Aggregated with WP_Query + parts meta.', 'workshop-ledger' ) ); ?></p>
			<table class="widefat striped" style="max-width:520px">
				<tbody>
				<?php
				$rows = array(
					__( 'Vehicles in catalog', 'workshop-ledger' ) => (string) $s['vehiculos'],
					__( 'SKUs (parts)', 'workshop-ledger' )      => (string) $s['repuestos'],
					__( 'Units in stock', 'workshop-ledger' )     => (string) $s['unidades'],
					__( 'Valor inventario', 'workshop-ledger' )     => number_format_i18n( $s['valor_inventario'], 2 ),
					__( 'Critical stock SKUs', 'workshop-ledger' )    => (string) $s['criticos'],
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
