<?php
/**
 * Reportes — CSV export links + manual resumen email.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Admin\CsvExportHandler;
use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\MonthlyResumenService;

final class ReportesAdminPage {

	public const SLUG = 'wl-reportes';
	public const MAIL_ACTION = 'wl_send_resumen';

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
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}

		$sent = isset( $_GET['wl_resumen_sent'] ) && '1' === $_GET['wl_resumen_sent'];
		$base = admin_url( 'admin-post.php' );
		$export_labels = array(
			'vehiculos'     => __( 'Vehicles', 'workshop-ledger' ),
			'repuestos'     => __( 'Parts', 'workshop-ledger' ),
			'transacciones' => __( 'Transactions', 'workshop-ledger' ),
		);
		?>
		<div class="wrap wl-admin-wrap">
			<div class="wl-page-header">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=workshop-ledger' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'workshop-ledger' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Reports', 'workshop-ledger' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Reports', 'workshop-ledger' ) ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( __( 'Executive views, CSV, and email — native WP_Query / admin-post / wp_mail.', 'workshop-ledger' ) ); ?></p>
				</div>
			</div>
			<?php if ( $sent ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo $this->escaper->html( __( 'Digest sent to the administrator email.', 'workshop-ledger' ) ); ?></p></div>
			<?php endif; ?>

			<h2 class="wl-section-title"><?php echo $this->escaper->html( __( 'Executive views', 'workshop-ledger' ) ); ?></h2>
			<div class="wl-nav-grid" style="margin-bottom:20px">
				<a class="wl-nav-tile" href="<?php echo esc_url( admin_url( 'admin.php?page=wl-reporte-ventas' ) ); ?>">
					<span class="dashicons dashicons-groups" aria-hidden="true"></span>
					<span class="wl-nav-tile__title"><?php echo $this->escaper->html( __( 'Sales', 'workshop-ledger' ) ); ?></span>
					<span class="wl-nav-tile__hint"><?php echo $this->escaper->html( __( 'Orders and amounts', 'workshop-ledger' ) ); ?></span>
				</a>
				<a class="wl-nav-tile" href="<?php echo esc_url( admin_url( 'admin.php?page=wl-reporte-inventario' ) ); ?>">
					<span class="dashicons dashicons-products" aria-hidden="true"></span>
					<span class="wl-nav-tile__title"><?php echo $this->escaper->html( __( 'Inventory', 'workshop-ledger' ) ); ?></span>
					<span class="wl-nav-tile__hint"><?php echo $this->escaper->html( __( 'Stock and value', 'workshop-ledger' ) ); ?></span>
				</a>
				<a class="wl-nav-tile" href="<?php echo esc_url( admin_url( 'admin.php?page=wl-reporte-finanzas' ) ); ?>">
					<span class="dashicons dashicons-chart-area" aria-hidden="true"></span>
					<span class="wl-nav-tile__title"><?php echo $this->escaper->html( __( 'Profitability', 'workshop-ledger' ) ); ?></span>
					<span class="wl-nav-tile__hint"><?php echo $this->escaper->html( __( 'Estimated margin', 'workshop-ledger' ) ); ?></span>
				</a>
			</div>

			<div class="wl-shell-grid">
				<div class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'Export CSV', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Nonce + manage_workshop_ledger on admin-post.php.', 'workshop-ledger' ) ); ?></p>
					<ul class="wl-link-list">
						<?php foreach ( $export_labels as $export => $label ) : ?>
							<li>
								<a href="<?php echo esc_url( $this->export_url( $base, $export ) ); ?>">
									<?php echo $this->escaper->html( $label ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'Monthly digest (email)', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Also scheduled weekly via WP-Cron.', 'workshop-ledger' ) ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="<?php echo esc_attr( self::MAIL_ACTION ); ?>" />
						<?php $this->nonce->field( self::MAIL_ACTION ); ?>
						<?php submit_button( __( 'Send digest now', 'workshop-ledger' ) ); ?>
					</form>
					<h3 class="wl-shell-panel__title" style="margin-top:16px"><?php echo $this->escaper->html( __( 'Preview', 'workshop-ledger' ) ); ?></h3>
					<pre style="background:#fafafa;padding:1em;border:1px solid #e8e8e8;border-radius:8px;max-width:100%;white-space:pre-wrap;font-size:12px"><?php echo esc_html( $this->resumen->build_body() ); ?></pre>
				</div>
			</div>
		</div>
		<?php
	}

	public function send_resumen(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		$this->nonce->verify_admin( self::MAIL_ACTION );
		$this->resumen->send_to_admin();
		wp_safe_redirect( add_query_arg( 'wl_resumen_sent', '1', admin_url( 'admin.php?page=' . self::SLUG ) ) );
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
			'_wl_nonce'
		);
	}
}
