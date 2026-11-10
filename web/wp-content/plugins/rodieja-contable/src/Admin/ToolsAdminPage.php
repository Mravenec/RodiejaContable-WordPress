<?php
/**
 * Settings · Tools — domain export/import (admin-post + multipart).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\DomainExportService;
use Rodieja\Contable\Services\DomainImportService;

final class ToolsAdminPage {

	private const ACTION_EXPORT_JSON = 'rodieja_export_json';
	private const ACTION_EXPORT_CSV  = 'rodieja_export_csv';
	private const ACTION_IMPORT      = 'rodieja_domain_import';

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private NonceGuard $nonce,
		private DomainExportService $export,
		private DomainImportService $import
	) {}

	public function register(): void {
		add_action( 'admin_post_' . self::ACTION_EXPORT_JSON, array( $this, 'download_json' ) );
		add_action( 'admin_post_' . self::ACTION_EXPORT_CSV, array( $this, 'download_csv' ) );
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$notice = '';
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['rodieja_tools_action'] ) && self::ACTION_IMPORT === sanitize_key( (string) wp_unslash( $_POST['rodieja_tools_action'] ) ) ) {
			$this->nonce->verify_admin( 'rodieja_tools_import' );
			$notice = $this->handle_import();
		}
		$export_json_url = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::ACTION_EXPORT_JSON ), 'rodieja_export_json', '_rodieja_nonce' );
		$export_csv_url  = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::ACTION_EXPORT_CSV ), 'rodieja_export_csv', '_rodieja_nonce' );
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-page-header">
				<div class="rodieja-page-header__main">
					<p class="rodieja-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-contable' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'rodieja-contable' ) ); ?></a>
						· <a href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-settings' ) ); ?>"><?php echo $this->escaper->html( __( 'Settings', 'rodieja-contable' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Tools', 'rodieja-contable' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Tools', 'rodieja-contable' ) ); ?></h1>
					<p class="rodieja-page-header__lede"><?php echo $this->escaper->html( __( 'Export or import domain CPT data — Read-Only Config → Database parity on native admin-post.', 'rodieja-contable' ) ); ?></p>
				</div>
			</div>

			<?php if ( '' !== $notice ) : ?>
				<div class="notice notice-info is-dismissible"><p><?php echo $this->escaper->html( $notice ); ?></p></div>
			<?php endif; ?>

			<div class="rodieja-shell-grid">
				<div class="rodieja-shell-panel">
					<h2 class="rodieja-shell-panel__title"><?php echo $this->escaper->html( __( 'Export domain', 'rodieja-contable' ) ); ?></h2>
					<p class="rodieja-shell-panel__hint"><?php echo $this->escaper->html( __( 'Download all Rodieja CPT rows as JSON or CSV.', 'rodieja-contable' ) ); ?></p>
					<p>
						<a class="button button-primary" href="<?php echo $this->escaper->attr( $export_json_url ); ?>"><?php echo $this->escaper->html( __( 'Download JSON', 'rodieja-contable' ) ); ?></a>
						<a class="button" href="<?php echo $this->escaper->attr( $export_csv_url ); ?>"><?php echo $this->escaper->html( __( 'Download CSV', 'rodieja-contable' ) ); ?></a>
					</p>
				</div>
				<div class="rodieja-shell-panel">
					<h2 class="rodieja-shell-panel__title"><?php echo $this->escaper->html( __( 'Import JSON', 'rodieja-contable' ) ); ?></h2>
					<p class="rodieja-shell-panel__hint"><?php echo $this->escaper->html( __( 'Restore from a previous JSON export. Nonce required.', 'rodieja-contable' ) ); ?></p>
					<form method="post" enctype="multipart/form-data">
						<input type="hidden" name="rodieja_tools_action" value="<?php echo $this->escaper->attr( self::ACTION_IMPORT ); ?>" />
						<?php $this->nonce->field( 'rodieja_tools_import' ); ?>
						<p><input type="file" name="rodieja_domain_json" accept=".json,application/json" required /></p>
						<?php submit_button( __( 'Import domain', 'rodieja-contable' ), 'secondary', 'submit', false ); ?>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	public function download_json(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		check_admin_referer( 'rodieja_export_json', '_rodieja_nonce' );
		$body = $this->export->to_json_string();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=rodieja-domain-' . gmdate( 'Y-m-d' ) . '.json' );
		header( 'Content-Length: ' . (string) strlen( $body ) );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	public function download_csv(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		check_admin_referer( 'rodieja_export_csv', '_rodieja_nonce' );
		$body = $this->export->to_csv_string();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=rodieja-domain-' . gmdate( 'Y-m-d' ) . '.csv' );
		header( 'Content-Length: ' . (string) strlen( $body ) );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	private function handle_import(): string {
		if ( empty( $_FILES['rodieja_domain_json']['tmp_name'] ) ) {
			return __( 'JSON file required.', 'rodieja-contable' );
		}
		$file = $_FILES['rodieja_domain_json'];
		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			return __( 'Upload failed.', 'rodieja-contable' );
		}
		$body = file_get_contents( (string) $file['tmp_name'] );
		if ( ! is_string( $body ) || '' === $body ) {
			return __( 'Empty file.', 'rodieja-contable' );
		}
		$result = $this->import->from_json_string( $body );
		$msg    = sprintf(
			/* translators: 1: created count 2: updated count */
			__( 'Created: %1$d · Updated: %2$d', 'rodieja-contable' ),
			$result['created'],
			$result['updated']
		);
		if ( array() !== $result['errors'] ) {
			$msg .= ' — ' . implode( '; ', array_slice( $result['errors'], 0, 5 ) );
		}
		return $msg;
	}
}
