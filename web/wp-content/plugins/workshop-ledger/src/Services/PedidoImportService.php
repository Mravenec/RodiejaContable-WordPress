<?php
/**
 * Import local JSON/CSV → wl_pedido + wl_pedido_item.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

final class PedidoImportService {

	public function __construct( private AudatexInsSettingsService $settings ) {}

	/**
	 * @return array{created: int, items: int, errors: list<string>}
	 */
	public function from_json_string( string $json ): array {
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			return array( 'created' => 0, 'items' => 0, 'errors' => array( __( 'Invalid JSON.', 'workshop-ledger' ) ) );
		}
		$pedidos = $data['pedidos'] ?? $data;
		if ( ! is_array( $pedidos ) ) {
			return array( 'created' => 0, 'items' => 0, 'errors' => array( __( 'Orders structure missing.', 'workshop-ledger' ) ) );
		}
		return $this->import_pedidos( $pedidos );
	}

	/**
	 * @return array{created: int, items: int, errors: list<string>}
	 */
	public function from_csv_string( string $csv ): array {
		$lines = preg_split( '/\r\n|\r|\n/', trim( $csv ) ) ?: array();
		if ( count( $lines ) < 2 ) {
			return array( 'created' => 0, 'items' => 0, 'errors' => array( __( 'CSV empty or has no rows.', 'workshop-ledger' ) ) );
		}
		$header = str_getcsv( (string) array_shift( $lines ) );
		$header = array_map( static fn( $h ) => sanitize_key( (string) $h ), $header );
		$rows   = array();
		foreach ( $lines as $line ) {
			if ( '' === trim( $line ) ) {
				continue;
			}
			$cells = str_getcsv( $line );
			$row   = array();
			foreach ( $header as $i => $key ) {
				$row[ $key ] = $cells[ $i ] ?? '';
			}
			$rows[] = $row;
		}
		return $this->import_pedidos( $rows );
	}

	/**
	 * @param list<array<string, mixed>> $pedidos
	 * @return array{created: int, items: int, errors: list<string>}
	 */
	private function import_pedidos( array $pedidos ): array {
		$created = 0;
		$items   = 0;
		$errors  = array();
		foreach ( $pedidos as $i => $row ) {
			if ( ! is_array( $row ) ) {
				$errors[] = sprintf( __( 'Row %d is not an object.', 'workshop-ledger' ), $i + 1 );
				continue;
			}
			$title = sanitize_text_field( (string) ( $row['title'] ?? $row['titulo'] ?? __( 'Imported order', 'workshop-ledger' ) ) );
			$origen = $this->sanitize_origen( (string) ( $row['origen'] ?? 'manual' ) );
			$id     = wp_insert_post(
				array(
					'post_type'   => 'wl_pedido',
					'post_title'  => $title,
					'post_status' => 'publish',
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				$errors[] = $id->get_error_message();
				continue;
			}
			++$created;
			$post_id = (int) $id;
			update_post_meta( $post_id, 'wl_origen', $origen );
			update_post_meta( $post_id, 'wl_estado_pedido', sanitize_text_field( (string) ( $row['estado'] ?? $row['estado_pedido'] ?? 'Awaiting confirmation' ) ) );
			update_post_meta( $post_id, 'wl_referencia_ext', sanitize_text_field( (string) ( $row['referencia_ext'] ?? $row['referencia'] ?? '' ) ) );
			update_post_meta( $post_id, 'wl_numero_pedido', sanitize_text_field( (string) ( $row['numero_pedido'] ?? '' ) ) );
			update_post_meta( $post_id, 'wl_total_pedido', (float) ( $row['total'] ?? $row['total_pedido'] ?? 0 ) );
			$string_map = array(
				'cotizacion_id' => 'wl_cotizacion_id',
				'siniestro'     => 'wl_siniestro',
				'wan'           => 'wl_wan',
				'aseguradora'   => 'wl_aseguradora',
				'vin'           => 'wl_vin',
			);
			foreach ( $string_map as $src => $meta ) {
				if ( isset( $row[ $src ] ) && '' !== (string) $row[ $src ] ) {
					update_post_meta( $post_id, $meta, sanitize_text_field( (string) $row[ $src ] ) );
				}
			}
			$item_rows = $row['items'] ?? array();
			if ( is_string( $item_rows ) && '' !== $item_rows ) {
				$decoded = json_decode( $item_rows, true );
				$item_rows = is_array( $decoded ) ? $decoded : array();
			}
			if ( is_array( $item_rows ) ) {
				foreach ( $item_rows as $item ) {
					if ( ! is_array( $item ) ) {
						continue;
					}
					$item_id = wp_insert_post(
						array(
							'post_type'   => 'wl_pedido_item',
							'post_title'  => sanitize_text_field( (string) ( $item['title'] ?? $item['descripcion'] ?? __( 'Item', 'workshop-ledger' ) ) ),
							'post_status' => 'publish',
						),
						true
					);
					if ( is_wp_error( $item_id ) ) {
						$errors[] = $item_id->get_error_message();
						continue;
					}
					++$items;
					$iid = (int) $item_id;
					update_post_meta( $iid, 'wl_pedido_id', (string) $post_id );
					update_post_meta( $iid, 'wl_descripcion', sanitize_text_field( (string) ( $item['descripcion'] ?? '' ) ) );
					update_post_meta( $iid, 'wl_cantidad', (int) ( $item['cantidad'] ?? 1 ) );
					update_post_meta( $iid, 'wl_precio_ofrecido', (float) ( $item['precio_ofrecido'] ?? $item['precio'] ?? 0 ) );
					update_post_meta( $iid, 'wl_tipo_pieza', sanitize_text_field( (string) ( $item['tipo_pieza'] ?? '' ) ) );
				}
			}
		}
		if ( $created > 0 ) {
			$this->settings->note_import(
				sprintf(
					/* translators: %1$d pedidos, %2$d items */
					__( 'Import: %1$d orders, %2$d items.', 'workshop-ledger' ),
					$created,
					$items
				)
			);
		}
		return array( 'created' => $created, 'items' => $items, 'errors' => $errors );
	}

	private function sanitize_origen( string $origen ): string {
		$origen = sanitize_key( $origen );
		if ( in_array( $origen, array( 'audatex', 'ins', 'manual' ), true ) ) {
			return $origen;
		}
		return 'manual';
	}
}
