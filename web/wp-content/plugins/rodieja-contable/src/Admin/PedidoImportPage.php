<?php
/**
 * Cotizaciones InPart · importar pedidos JSON/CSV.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\PedidoImportService;

final class PedidoImportPage {

	private const MAX_BYTES = 2_000_000;

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private NonceGuard $nonce,
		private PedidoImportService $importer
	) {}

	public function register(): void {
		// Rendered via AdminMenu · Cotizaciones InPart.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$notice = '';
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			$this->nonce->verify_admin( 'rodieja_pedido_import' );
			$result = $this->handle_upload();
			if ( isset( $result['message'] ) ) {
				$notice = (string) $result['message'];
			}
		}
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'InPart Quotes', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Upload local JSON or CSV to create orders and items (wp_insert_post + meta). No live API.', 'rodieja-contable' ) ); ?></p>
			<?php if ( '' !== $notice ) : ?>
				<div class="notice notice-info"><p><?php echo $this->escaper->html( $notice ); ?></p></div>
			<?php endif; ?>
			<form method="post" enctype="multipart/form-data">
				<?php $this->nonce->field( 'rodieja_pedido_import' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="rodieja_import_file"><?php echo $this->escaper->html( __( 'Archivo', 'rodieja-contable' ) ); ?></label></th>
						<td><input type="file" name="rodieja_import_file" id="rodieja_import_file" accept=".json,.csv,application/json,text/csv" required /></td>
					</tr>
				</table>
				<?php submit_button( __( 'Import', 'rodieja-contable' ) ); ?>
			</form>
			<h2><?php echo $this->escaper->html( __( 'Formato JSON (ejemplo)', 'rodieja-contable' ) ); ?></h2>
			<pre style="max-width:640px;background:#f6f7f7;padding:12px;">{"pedidos":[{"title":"Pedido 1","origen":"audatex","total":1200,"items":[{"descripcion":"Parachoques","cantidad":1,"precio_ofrecido":450}]}]}</pre>
		</div>
		<?php
	}

	/**
	 * @return array{message?: string}
	 */
	private function handle_upload(): array {
		if ( empty( $_FILES['rodieja_import_file']['tmp_name'] ) ) {
			return array( 'message' => __( 'Archivo requerido.', 'rodieja-contable' ) );
		}
		$file = $_FILES['rodieja_import_file'];
		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			return array( 'message' => __( 'Error al subir el archivo.', 'rodieja-contable' ) );
		}
		$size = (int) ( $file['size'] ?? 0 );
		if ( $size <= 0 || $size > self::MAX_BYTES ) {
			return array( 'message' => __( 'File size not allowed.', 'rodieja-contable' ) );
		}
		$name = sanitize_file_name( (string) ( $file['name'] ?? 'import.dat' ) );
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		$body = file_get_contents( (string) $file['tmp_name'] );
		if ( ! is_string( $body ) || '' === $body ) {
			return array( 'message' => __( 'Empty file.', 'rodieja-contable' ) );
		}
		$result = 'csv' === $ext ? $this->importer->from_csv_string( $body ) : $this->importer->from_json_string( $body );
		$msg    = sprintf(
			__( 'Created: %1$d orders, %2$d items.', 'rodieja-contable' ),
			$result['created'],
			$result['items']
		);
		if ( array() !== $result['errors'] ) {
			$msg .= ' ' . implode( '; ', array_slice( $result['errors'], 0, 5 ) );
		}
		return array( 'message' => $msg );
	}
}
