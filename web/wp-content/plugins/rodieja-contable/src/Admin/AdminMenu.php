<?php
/**
 * Top-level Rodieja admin hub (native menus only).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\DemoSeedService;
use Rodieja\Contable\Services\DomainStatsService;

final class AdminMenu {

	public function __construct(
		private OutputEscaper $escaper,
		private NonceGuard $nonce,
		private DemoSeedService $seed,
		private DomainStatsService $stats,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_post_rodieja_seed_demo', array( $this, 'handle_seed' ) );
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

	public function handle_seed(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$this->nonce->verify_admin( 'rodieja_seed_demo' );
		$result = $this->seed->seed_if_empty();
		$msg    = ! empty( $result['seeded'] ) ? 'seeded' : 'noop';
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'          => 'rodieja-contable',
					'rodieja_seed'  => $msg,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public function render_dashboard(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}

		$counts = $this->stats->counts();
		$labels = array(
			'rodieja_vehiculo'    => __( 'Vehículos', 'rodieja-contable' ),
			'rodieja_repuesto'    => __( 'Repuestos', 'rodieja-contable' ),
			'rodieja_transaccion' => __( 'Transacciones', 'rodieja-contable' ),
			'rodieja_empleado'    => __( 'Empleados', 'rodieja-contable' ),
			'rodieja_pedido'      => __( 'Pedidos', 'rodieja-contable' ),
			'rodieja_marca'       => __( 'Marcas', 'rodieja-contable' ),
		);
		$flash = isset( $_GET['rodieja_seed'] ) ? sanitize_key( (string) wp_unslash( $_GET['rodieja_seed'] ) ) : '';
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Rodieja Contable', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Native WordPress admin — CPTs, taxonomies, and meta. No custom tables.', 'rodieja-contable' ) ); ?></p>
			<?php if ( 'seeded' === $flash ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo $this->escaper->html( __( 'Demo content seeded / completed.', 'rodieja-contable' ) ); ?></p></div>
			<?php elseif ( 'noop' === $flash ) : ?>
				<div class="notice notice-info is-dismissible"><p><?php echo $this->escaper->html( __( 'Demo already complete — nothing to add.', 'rodieja-contable' ) ); ?></p></div>
			<?php endif; ?>
			<table class="widefat striped" style="max-width:480px">
				<tbody>
				<?php foreach ( $labels as $type => $label ) : ?>
					<tr>
						<th><?php echo $this->escaper->html( (string) $label ); ?></th>
						<td><?php echo $this->escaper->html( (string) ( $counts[ $type ] ?? 0 ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1rem">
				<input type="hidden" name="action" value="rodieja_seed_demo" />
				<?php $this->nonce->field( 'rodieja_seed_demo' ); ?>
				<?php submit_button( __( 'Seed / complete demo (native)', 'rodieja-contable' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}
}
