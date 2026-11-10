<?php
/**
 * admin-post.php CSV downloads (nonce + cap).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Services\CsvExportService;

final class CsvExportHandler {

	public const ACTION = 'wl_csv_export';

	public function __construct( private CsvExportService $exporter ) {}

	public function register(): void {
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle' ) );
	}

	public function handle(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		check_admin_referer( self::ACTION, '_wl_nonce' );

		$kind = isset( $_GET['export'] ) ? sanitize_key( wp_unslash( (string) $_GET['export'] ) ) : '';
		$name = 'wl-' . $kind . '-' . gmdate( 'Y-m-d' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $name );

		$stream = $this->exporter->open_stream();
		if ( ! is_resource( $stream ) ) {
			wp_die( esc_html__( 'Export failed.', 'workshop-ledger' ), 500 );
		}

		match ( $kind ) {
			'vehiculos'      => $this->exporter->export_vehiculos( $stream ),
			'repuestos'      => $this->exporter->export_repuestos( $stream ),
			'transacciones'  => $this->exporter->export_transacciones( $stream ),
			default          => wp_die( esc_html__( 'Unknown export.', 'workshop-ledger' ), 400 ),
		};

		fclose( $stream );
		exit;
	}
}
