<?php
/**
 * Reportes · rentabilidad.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\PostTypes\PostTypeRegistrar;
use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\ProfitabilityReportService;

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
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$p = $this->report->summary();
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Rentabilidad', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Costos vehiculares vs recuperado + valor de repuestos en stock.', 'rodieja-contable' ) ); ?></p>
			<table class="widefat striped" style="max-width:520px">
				<tbody>
				<?php
				$rows = array(
					__( 'Vehículos', 'rodieja-contable' )              => (string) $p['vehiculos'],
					__( 'Inversión acumulada', 'rodieja-contable' )    => number_format_i18n( $p['inversion'], 2 ),
					__( 'Costo recuperado', 'rodieja-contable' )       => number_format_i18n( $p['recuperado'], 2 ),
					__( 'Valor repuestos (stock)', 'rodieja-contable' ) => number_format_i18n( $p['valor_repuestos_stock'], 2 ),
					__( 'Margen bruto estimado', 'rodieja-contable' )  => number_format_i18n( $p['margen_bruto'], 2 ),
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
