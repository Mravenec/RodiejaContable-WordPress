<?php
/**
 * Reportes — CSV export links + manual resumen email.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Admin\CsvExportHandler;
use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\MonthlyResumenService;

final class ReportesAdminPage {

	public const SLUG = 'rodieja-reportes';
	public const MAIL_ACTION = 'rodieja_send_resumen';

	public function __construct(
		private OutputEscaper $escaper,
		private NonceGuard $nonce,
		private MonthlyResumenService $resumen
	) {}

	public function register(): void {
		add_action( 'admin_post_' . self::MAIL_ACTION, array( $this, 'send_resumen' ) );
	}

	public function render(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}

		$sent = isset( $_GET['rodieja_resumen_sent'] ) && '1' === $_GET['rodieja_resumen_sent'];
		$base = admin_url( 'admin-post.php' );
		?>
		$export_labels = array(
			'vehiculos'      => __( 'Vehicles', 'rodieja-contable' ),
			'repuestos'      => __( 'Parts', 'rodieja-contable' ),
			'transacciones'  => __( 'Transactions', 'rodieja-contable' ),
		);
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-page-header">
				<div class="rodieja-page-header__main">
					<p class="rodieja-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-contable' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'rodieja-contable' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Reports', 'rodieja-contable' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Reports', 'rodieja-contable' ) ); ?></h1>
					<p class="rodieja-page-header__lede"><?php echo $this->escaper->html( __( 'Executive views, CSV, and email — native WP_Query / admin-post / wp_mail.', 'rodieja-contable' ) ); ?></p>
				</div>
			</div>
			<?php if ( $sent ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo $this->escaper->html( __( 'Digest sent to the administrator email.', 'rodieja-contable' ) ); ?></p></div>
			<?php endif; ?>

			<h2 class="rodieja-section-title"><?php echo $this->escaper->html( __( 'Executive views', 'rodieja-contable' ) ); ?></h2>
			<div class="rodieja-nav-grid" style="margin-bottom:20px">
				<a class="rodieja-nav-tile" href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-reporte-ventas' ) ); ?>">
					<span class="dashicons dashicons-groups" aria-hidden="true"></span>
					<span class="rodieja-nav-tile__title"><?php echo $this->escaper->html( __( 'Sales', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-nav-tile__hint"><?php echo $this->escaper->html( __( 'Orders and amounts', 'rodieja-contable' ) ); ?></span>
				</a>
				<a class="rodieja-nav-tile" href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-reporte-inventario' ) ); ?>">
					<span class="dashicons dashicons-products" aria-hidden="true"></span>
					<span class="rodieja-nav-tile__title"><?php echo $this->escaper->html( __( 'Inventory', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-nav-tile__hint"><?php echo $this->escaper->html( __( 'Stock and value', 'rodieja-contable' ) ); ?></span>
				</a>
				<a class="rodieja-nav-tile" href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-reporte-finanzas' ) ); ?>">
					<span class="dashicons dashicons-chart-area" aria-hidden="true"></span>
					<span class="rodieja-nav-tile__title"><?php echo $this->escaper->html( __( 'Profitability', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-nav-tile__hint"><?php echo $this->escaper->html( __( 'Estimated margin', 'rodieja-contable' ) ); ?></span>
				</a>
			</div>

			<div class="rodieja-shell-grid">
				<div class="rodieja-shell-panel">
					<h2 class="rodieja-shell-panel__title"><?php echo $this->escaper->html( __( 'Export CSV', 'rodieja-contable' ) ); ?></h2>
					<p class="rodieja-shell-panel__hint"><?php echo $this->escaper->html( __( 'Nonce + manage_rodieja_contable on admin-post.php.', 'rodieja-contable' ) ); ?></p>
					<ul class="rodieja-link-list">
						<?php foreach ( $export_labels as $export => $label ) : ?>
							<li>
								<a href="<?php echo esc_url( $this->export_url( $base, $export ) ); ?>">
									<?php echo $this->escaper->html( $label ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="rodieja-shell-panel">
					<h2 class="rodieja-shell-panel__title"><?php echo $this->escaper->html( __( 'Monthly digest (email)', 'rodieja-contable' ) ); ?></h2>
					<p class="rodieja-shell-panel__hint"><?php echo $this->escaper->html( __( 'Also scheduled weekly via WP-Cron.', 'rodieja-contable' ) ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="<?php echo esc_attr( self::MAIL_ACTION ); ?>" />
						<?php $this->nonce->field( self::MAIL_ACTION ); ?>
						<?php submit_button( __( 'Send digest now', 'rodieja-contable' ) ); ?>
					</form>
					<h3 class="rodieja-shell-panel__title" style="margin-top:16px"><?php echo $this->escaper->html( __( 'Preview', 'rodieja-contable' ) ); ?></h3>
					<pre style="background:#fafafa;padding:1em;border:1px solid #e8e8e8;border-radius:8px;max-width:100%;white-space:pre-wrap;font-size:12px"><?php echo esc_html( $this->resumen->build_body() ); ?></pre>
				</div>
			</div>
		</div>
		<?php
	}

	public function send_resumen(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$this->nonce->verify_admin( self::MAIL_ACTION );
		$this->resumen->send_to_admin();
		wp_safe_redirect( add_query_arg( 'rodieja_resumen_sent', '1', admin_url( 'admin.php?page=' . self::SLUG ) ) );
		exit;
	}

	private function export_url( string $base, string $export ): string {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action' => CsvExportHandler::ACTION,
					'export' => $export,
				),
				$base
			),
			CsvExportHandler::ACTION,
			'_rodieja_nonce'
		);
	}
}
