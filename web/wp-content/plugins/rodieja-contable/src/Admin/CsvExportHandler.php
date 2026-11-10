<?php
/**
 * admin-post.php CSV downloads (nonce + cap).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Services\CsvExportService;

final class CsvExportHandler {

	public const ACTION = 'rodieja_csv_export';

	public function __construct( private CsvExportService $exporter ) {}

	public function register(): void {
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle' ) );
	}

	public function handle(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		check_admin_referer( self::ACTION, '_rodieja_nonce' );

		$kind = isset( $_GET['export'] ) ? sanitize_key( wp_unslash( (string) $_GET['export'] ) ) : '';
		$name = 'rodieja-' . $kind . '-' . gmdate( 'Y-m-d' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $name );

		$stream = $this->exporter->open_stream();
		if ( ! is_resource( $stream ) ) {
			wp_die( esc_html__( 'Export failed.', 'rodieja-contable' ), 500 );
		}

		match ( $kind ) {
			'vehiculos'      => $this->exporter->export_vehiculos( $stream ),
			'repuestos'      => $this->exporter->export_repuestos( $stream ),
			'transacciones'  => $this->exporter->export_transacciones( $stream ),
			default          => wp_die( esc_html__( 'Unknown export.', 'rodieja-contable' ), 400 ),
		};

		fclose( $stream );
		exit;
	}
}
