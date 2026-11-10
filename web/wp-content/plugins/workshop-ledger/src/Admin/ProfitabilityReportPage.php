<?php
/**
 * Reportes · rentabilidad.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\PostTypes\PostTypeRegistrar;
use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\ProfitabilityReportService;

final class ProfitabilityReportPage {

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private ProfitabilityReportService $report
	) {}

	public function register(): void {
		// Rendered via AdminMenu (Finanzas).
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		$p = $this->report->summary();
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Profitability', 'workshop-ledger' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Vehicle costs vs recovered + parts stock value.', 'workshop-ledger' ) ); ?></p>
			<table class="widefat striped" style="max-width:520px">
				<tbody>
				<?php
				$rows = array(
					__( 'Vehicles', 'workshop-ledger' )              => (string) $p['vehiculos'],
					__( 'Cumulative investment', 'workshop-ledger' )    => number_format_i18n( $p['inversion'], 2 ),
					__( 'Costo recuperado', 'workshop-ledger' )       => number_format_i18n( $p['recuperado'], 2 ),
					__( 'Parts value (stock)', 'workshop-ledger' ) => number_format_i18n( $p['valor_repuestos_stock'], 2 ),
					__( 'Margen bruto estimado', 'workshop-ledger' )  => number_format_i18n( $p['margen_bruto'], 2 ),
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
