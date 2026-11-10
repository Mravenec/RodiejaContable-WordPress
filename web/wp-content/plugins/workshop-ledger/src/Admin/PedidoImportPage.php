<?php
/**
 * Audatex InPart · local JSON/CSV order import (native wp_insert_post + meta).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\PedidoImportService;

final class PedidoImportPage {

	private const MAX_BYTES = 2_000_000;

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private NonceGuard $nonce,
		private PedidoImportService $importer
	) {}

	public function register(): void {
		// Rendered via AdminMenu · Audatex InPart.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		$notice = '';
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			$this->nonce->verify_admin( 'wl_pedido_import' );
			$result = $this->handle_upload();
			if ( isset( $result['message'] ) ) {
				$notice = (string) $result['message'];
			}
		}

		$sample = wp_json_encode(
			array(
				'pedidos' => array(
					array(
						'title'  => 'Order 1',
						'origen' => 'audatex',
						'total'  => 1200,
						'items'  => array(
							array(
								'descripcion'     => 'Bumper',
								'cantidad'        => 1,
								'precio_ofrecido' => 450,
							),
						),
					),
				),
			),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);
		?>
		<div class="wrap wl-admin-wrap">
			<div class="wl-page-header">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=workshop-ledger' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'workshop-ledger' ) ); ?></a>
						· <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wl_pedido' ) ); ?>"><?php echo $this->escaper->html( __( 'Audatex InPart', 'workshop-ledger' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'INS / Import', 'workshop-ledger' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'INS / Import', 'workshop-ledger' ) ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( __( 'Upload local JSON or CSV to create orders and line items — native wp_insert_post + meta. No live Audatex API.', 'workshop-ledger' ) ); ?></p>
				</div>
				<div class="wl-page-header__actions">
					<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wl_pedido' ) ); ?>"><?php echo $this->escaper->html( __( 'View orders', 'workshop-ledger' ) ); ?></a>
				</div>
			</div>

			<?php if ( '' !== $notice ) : ?>
				<div class="notice notice-info is-dismissible"><p><?php echo $this->escaper->html( $notice ); ?></p></div>
			<?php endif; ?>

			<div class="wl-shell-grid">
				<div class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'Upload file', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Accepts .json or .csv · max 2 MB · nonce protected.', 'workshop-ledger' ) ); ?></p>
					<form method="post" enctype="multipart/form-data">
						<?php $this->nonce->field( 'wl_pedido_import' ); ?>
						<p>
							<label class="screen-reader-text" for="wl_import_file"><?php echo $this->escaper->html( __( 'File', 'workshop-ledger' ) ); ?></label>
							<input type="file" name="wl_import_file" id="wl_import_file" accept=".json,.csv,application/json,text/csv" required />
						</p>
						<?php submit_button( __( 'Import', 'workshop-ledger' ), 'primary', 'submit', false ); ?>
					</form>
				</div>
				<div class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'JSON sample', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Shape expected by the importer (pretty-printed for operators).', 'workshop-ledger' ) ); ?></p>
					<pre class="wl-code-sample"><?php echo esc_html( is_string( $sample ) ? $sample : '{}' ); ?></pre>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * @return array{message?: string}
	 */
	private function handle_upload(): array {
		if ( empty( $_FILES['wl_import_file']['tmp_name'] ) ) {
			return array( 'message' => __( 'File required.', 'workshop-ledger' ) );
		}
		$file = $_FILES['wl_import_file'];
		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			return array( 'message' => __( 'Upload failed.', 'workshop-ledger' ) );
		}
		$size = (int) ( $file['size'] ?? 0 );
		if ( $size <= 0 || $size > self::MAX_BYTES ) {
			return array( 'message' => __( 'File size not allowed.', 'workshop-ledger' ) );
		}
		$name = sanitize_file_name( (string) ( $file['name'] ?? 'import.dat' ) );
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		$body = file_get_contents( (string) $file['tmp_name'] );
		if ( ! is_string( $body ) || '' === $body ) {
			return array( 'message' => __( 'Empty file.', 'workshop-ledger' ) );
		}
		$result = 'csv' === $ext ? $this->importer->from_csv_string( $body ) : $this->importer->from_json_string( $body );
		$msg    = sprintf(
			__( 'Created: %1$d orders, %2$d items.', 'workshop-ledger' ),
			$result['created'],
			$result['items']
		);
		if ( array() !== $result['errors'] ) {
			$msg .= ' ' . implode( '; ', array_slice( $result['errors'], 0, 5 ) );
		}
		return array( 'message' => $msg );
	}
}
