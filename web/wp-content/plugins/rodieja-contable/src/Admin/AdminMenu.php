<?php
/**
 * Top-level Rodieja admin hub (native menus only).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;

final class AdminMenu {

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	public function add_menu(): void {
		add_menu_page(
			__( 'Rodieja', 'rodieja-contable' ),
			__( 'Rodieja', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-contable',
			array( $this, 'render_dashboard' ),
			'dashicons-car',
			24
		);
	}

	public function render_dashboard(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}

		$counts = array(
			__( 'Vehículos', 'rodieja-contable' )     => wp_count_posts( 'rodieja_vehiculo' )->publish ?? 0,
			__( 'Repuestos', 'rodieja-contable' )     => wp_count_posts( 'rodieja_repuesto' )->publish ?? 0,
			__( 'Transacciones', 'rodieja-contable' ) => wp_count_posts( 'rodieja_transaccion' )->publish ?? 0,
			__( 'Empleados', 'rodieja-contable' )     => wp_count_posts( 'rodieja_empleado' )->publish ?? 0,
			__( 'Pedidos', 'rodieja-contable' )       => wp_count_posts( 'rodieja_pedido' )->publish ?? 0,
		);
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Rodieja Contable', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Native WordPress admin — CPTs, taxonomies, and meta. No custom tables.', 'rodieja-contable' ) ); ?></p>
			<table class="widefat striped" style="max-width:480px">
				<tbody>
				<?php foreach ( $counts as $label => $n ) : ?>
					<tr>
						<th><?php echo $this->escaper->html( (string) $label ); ?></th>
						<td><?php echo $this->escaper->html( (string) $n ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
