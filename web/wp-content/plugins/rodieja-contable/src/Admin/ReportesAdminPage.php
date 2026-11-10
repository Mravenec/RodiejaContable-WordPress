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
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Reportes Rodieja', 'rodieja-contable' ) ); ?></h1>
			<?php if ( $sent ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Resumen enviado al correo del administrador.', 'rodieja-contable' ); ?></p></div>
			<?php endif; ?>

			<h2><?php echo $this->escaper->html( __( 'Exportar CSV', 'rodieja-contable' ) ); ?></h2>
			<p><?php echo $this->escaper->html( __( 'Descargas vía admin-post.php con nonce y capacidad manage_rodieja_contable.', 'rodieja-contable' ) ); ?></p>
			<ul>
				<?php foreach ( array( 'vehiculos', 'repuestos', 'transacciones' ) as $export ) : ?>
					<li>
						<a href="<?php echo esc_url( $this->export_url( $base, $export ) ); ?>">
							<?php echo $this->escaper->html( ucfirst( $export ) ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<h2><?php echo $this->escaper->html( __( 'Resumen mensual (correo)', 'rodieja-contable' ) ); ?></h2>
			<p><?php echo $this->escaper->html( __( 'También programado semanalmente vía WP-Cron.', 'rodieja-contable' ) ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::MAIL_ACTION ); ?>" />
				<?php $this->nonce->field( self::MAIL_ACTION ); ?>
				<?php submit_button( __( 'Enviar resumen ahora', 'rodieja-contable' ) ); ?>
			</form>

			<h3><?php echo $this->escaper->html( __( 'Vista previa', 'rodieja-contable' ) ); ?></h3>
			<pre style="background:#fff;padding:1em;border:1px solid #ccd0d4;max-width:640px;white-space:pre-wrap"><?php echo esc_html( $this->resumen->build_body() ); ?></pre>
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
