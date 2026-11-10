<?php
/**
 * Top-level native admin menu for Rodieja CPTs.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Roles\RoleRegistrar;

final class AdminMenu {

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	public function add_menu(): void {
		add_menu_page(
			__( 'Rodieja Contable', 'rodieja-contable' ),
			__( 'Rodieja', 'rodieja-contable' ),
			RoleRegistrar::CAP_MANAGE,
			'rodieja-contable',
			array( $this, 'render_dashboard' ),
			'dashicons-car',
			26
		);
	}

	public function render_dashboard(): void {
		if ( ! current_user_can( RoleRegistrar::CAP_MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}

		$counts = array(
			'vehiculos'      => wp_count_posts( 'rodieja_vehiculo' ),
			'transacciones'  => wp_count_posts( 'rodieja_transaccion' ),
			'repuestos'      => wp_count_posts( 'rodieja_repuesto' ),
			'marcas'         => wp_count_posts( 'rodieja_marca' ),
		);
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Rodieja Contable', 'rodieja-contable' ); ?></h1>
			<p><?php echo esc_html__( 'Native WordPress module: vehicles, parts inventory, and finance (CPT + meta + REST).', 'rodieja-contable' ); ?></p>
			<table class="widefat striped" style="max-width:480px;">
				<tbody>
					<tr><th><?php echo esc_html__( 'Vehículos', 'rodieja-contable' ); ?></th><td><?php echo esc_html( (string) ( $counts['vehiculos']->publish ?? 0 ) ); ?></td></tr>
					<tr><th><?php echo esc_html__( 'Transacciones', 'rodieja-contable' ); ?></th><td><?php echo esc_html( (string) ( $counts['transacciones']->publish ?? 0 ) ); ?></td></tr>
					<tr><th><?php echo esc_html__( 'Repuestos', 'rodieja-contable' ); ?></th><td><?php echo esc_html( (string) ( $counts['repuestos']->publish ?? 0 ) ); ?></td></tr>
					<tr><th><?php echo esc_html__( 'Marcas', 'rodieja-contable' ); ?></th><td><?php echo esc_html( (string) ( $counts['marcas']->publish ?? 0 ) ); ?></td></tr>
				</tbody>
			</table>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=rodieja_vehiculo' ) ); ?>"><?php echo esc_html__( 'Vehículos', 'rodieja-contable' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=rodieja_transaccion' ) ); ?>"><?php echo esc_html__( 'Transacciones', 'rodieja-contable' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=rodieja_repuesto' ) ); ?>"><?php echo esc_html__( 'Repuestos', 'rodieja-contable' ); ?></a>
			</p>
		</div>
		<?php
	}
}
