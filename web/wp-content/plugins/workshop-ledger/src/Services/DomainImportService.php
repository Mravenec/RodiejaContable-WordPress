<?php
/**
 * Import domain posts from JSON — validate + wp_insert_post.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

use WorkshopLedger\PostTypes\DomainPostTypes;

final class DomainImportService {

	/** @var list<string> */
	private array $allowed;

	public function __construct() {
		$this->allowed = DomainPostTypes::historial_tracked();
	}

	/**
	 * @return array{created: int, updated: int, errors: list<string>}
	 */
	public function from_json_string( string $json ): array {
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			return array( 'created' => 0, 'updated' => 0, 'errors' => array( __( 'Invalid JSON.', 'workshop-ledger' ) ) );
		}
		$created = 0;
		$updated = 0;
		$errors  = array();
		foreach ( $data as $type => $posts ) {
			if ( ! is_string( $type ) || ! in_array( $type, $this->allowed, true ) ) {
				continue;
			}
			if ( ! is_array( $posts ) ) {
				$errors[] = sprintf( __( 'Type %s: invalid list.', 'workshop-ledger' ), $type );
				continue;
			}
			foreach ( $posts as $i => $row ) {
				if ( ! is_array( $row ) ) {
					$errors[] = sprintf( __( '%s row %d invalid.', 'workshop-ledger' ), $type, $i + 1 );
					continue;
				}
				$result = $this->upsert( $type, $row );
				if ( isset( $result['error'] ) ) {
					$errors[] = (string) $result['error'];
					continue;
				}
				if ( ! empty( $result['created'] ) ) {
					++$created;
				} else {
					++$updated;
				}
			}
		}
		return array( 'created' => $created, 'updated' => $updated, 'errors' => $errors );
	}

	/**
	 * @param array<string, mixed> $row
	 * @return array{created?: bool, error?: string}
	 */
	private function upsert( string $type, array $row ): array {
		$title = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
		if ( '' === $title ) {
			return array( 'error' => __( 'Title required.', 'workshop-ledger' ) );
		}
		$status = sanitize_key( (string) ( $row['status'] ?? 'publish' ) );
		if ( ! in_array( $status, array( 'publish', 'draft', 'pending', 'private' ), true ) ) {
			$status = 'publish';
		}
		$existing = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
		$is_new   = true;
		if ( $existing > 0 && get_post_type( $existing ) === $type ) {
			$post_id = wp_update_post(
				array(
					'ID'          => $existing,
					'post_title'  => $title,
					'post_status' => $status,
				),
				true
			);
			$is_new = false;
		} else {
			$post_id = wp_insert_post(
				array(
					'post_type'   => $type,
					'post_title'  => $title,
					'post_status' => $status,
				),
				true
			);
		}
		if ( is_wp_error( $post_id ) ) {
			return array( 'error' => $post_id->get_error_message() );
		}
		$meta = $row['meta'] ?? array();
		if ( is_array( $meta ) ) {
			foreach ( $meta as $key => $val ) {
				if ( ! is_string( $key ) || ! str_starts_with( $key, 'wl_' ) ) {
					continue;
				}
				// Orphan key — vehicle status is taxonomy wl_estado_vehiculo.
				if ( 'wl_estado' === $key ) {
					if ( 'wl_vehiculo' === $type && is_scalar( $val ) && '' !== (string) $val ) {
						$term = sanitize_text_field( (string) $val );
						if ( ! term_exists( $term, 'wl_estado_vehiculo' ) ) {
							wp_insert_term( $term, 'wl_estado_vehiculo' );
						}
						wp_set_object_terms( (int) $post_id, $term, 'wl_estado_vehiculo', false );
					}
					continue;
				}
				update_post_meta( (int) $post_id, $key, is_scalar( $val ) ? $val : wp_json_encode( $val ) );
			}
		}
		return array( 'created' => $is_new );
	}
}
