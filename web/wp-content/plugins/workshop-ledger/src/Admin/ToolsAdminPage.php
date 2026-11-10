<?php
/**
 * Settings · Tools — domain export/import (admin-post + multipart).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\DomainExportService;
use WorkshopLedger\Services\DomainImportService;

final class ToolsAdminPage {

	private const ACTION_EXPORT_JSON = 'wl_export_json';
	private const ACTION_EXPORT_CSV  = 'wl_export_csv';
	private const ACTION_IMPORT      = 'wl_domain_import';

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
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		$notice = '';
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['wl_tools_action'] ) && self::ACTION_IMPORT === sanitize_key( (string) wp_unslash( $_POST['wl_tools_action'] ) ) ) {
			$this->nonce->verify_admin( 'wl_tools_import' );
			$notice = $this->handle_import();
		}
		$export_json_url = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::ACTION_EXPORT_JSON ), 'wl_export_json', '_wl_nonce' );
		$export_csv_url  = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::ACTION_EXPORT_CSV ), 'wl_export_csv', '_wl_nonce' );
		?>
		<div class="wrap wl-admin-wrap">
			<div class="wl-page-header">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=workshop-ledger' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'workshop-ledger' ) ); ?></a>
						· <a href="<?php echo esc_url( admin_url( 'admin.php?page=wl-settings' ) ); ?>"><?php echo $this->escaper->html( __( 'Settings', 'workshop-ledger' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Tools', 'workshop-ledger' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Tools', 'workshop-ledger' ) ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( __( 'Export or import domain CPT data — Read-Only Config → Database parity on native admin-post.', 'workshop-ledger' ) ); ?></p>
				</div>
			</div>

			<?php if ( '' !== $notice ) : ?>
				<div class="notice notice-info is-dismissible"><p><?php echo $this->escaper->html( $notice ); ?></p></div>
			<?php endif; ?>

			<div class="wl-shell-grid">
				<div class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'Export domain', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Download all WorkshopLedger CPT rows as JSON or CSV.', 'workshop-ledger' ) ); ?></p>
					<p>
						<a class="button button-primary" href="<?php echo $this->escaper->attr( $export_json_url ); ?>"><?php echo $this->escaper->html( __( 'Download JSON', 'workshop-ledger' ) ); ?></a>
						<a class="button" href="<?php echo $this->escaper->attr( $export_csv_url ); ?>"><?php echo $this->escaper->html( __( 'Download CSV', 'workshop-ledger' ) ); ?></a>
					</p>
				</div>
				<div class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'Import JSON', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Restore from a previous JSON export. Nonce required.', 'workshop-ledger' ) ); ?></p>
					<form method="post" enctype="multipart/form-data">
						<input type="hidden" name="wl_tools_action" value="<?php echo $this->escaper->attr( self::ACTION_IMPORT ); ?>" />
						<?php $this->nonce->field( 'wl_tools_import' ); ?>
						<p><input type="file" name="wl_domain_json" accept=".json,application/json" required /></p>
						<?php submit_button( __( 'Import domain', 'workshop-ledger' ), 'secondary', 'submit', false ); ?>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	public function download_json(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		check_admin_referer( 'wl_export_json', '_wl_nonce' );
		$body = $this->export->to_json_string();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=wl-domain-' . gmdate( 'Y-m-d' ) . '.json' );
		header( 'Content-Length: ' . (string) strlen( $body ) );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	public function download_csv(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		check_admin_referer( 'wl_export_csv', '_wl_nonce' );
		$body = $this->export->to_csv_string();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=wl-domain-' . gmdate( 'Y-m-d' ) . '.csv' );
		header( 'Content-Length: ' . (string) strlen( $body ) );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	private function handle_import(): string {
		if ( empty( $_FILES['wl_domain_json']['tmp_name'] ) ) {
			return __( 'JSON file required.', 'workshop-ledger' );
		}
		$file = $_FILES['wl_domain_json'];
		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			return __( 'Upload failed.', 'workshop-ledger' );
		}
		$body = file_get_contents( (string) $file['tmp_name'] );
		if ( ! is_string( $body ) || '' === $body ) {
			return __( 'Empty file.', 'workshop-ledger' );
		}
		$result = $this->import->from_json_string( $body );
		$msg    = sprintf(
			/* translators: 1: created count 2: updated count */
			__( 'Created: %1$d · Updated: %2$d', 'workshop-ledger' ),
			$result['created'],
			$result['updated']
		);
		if ( array() !== $result['errors'] ) {
			$msg .= ' — ' . implode( '; ', array_slice( $result['errors'], 0, 5 ) );
		}
		return $msg;
	}
}
