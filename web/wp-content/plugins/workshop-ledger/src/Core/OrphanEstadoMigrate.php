<?php
/**
 * One-time cleanup: orphan post meta `wl_estado` → native taxonomy / delete.
 *
 * Vehicle status lives on taxonomy `wl_estado_vehiculo`. Demo/import left stray
 * `wl_estado` meta on vehicles and transactions — not in MetaRegistrar.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Core;

final class OrphanEstadoMigrate {

	public const OPTION_FLAG = 'wl_orphan_estado_migrated_v1';

	public static function maybe_run(): void {
		if ( get_option( self::OPTION_FLAG ) ) {
			return;
		}
		( new self() )->run();
		update_option( self::OPTION_FLAG, gmdate( 'c' ), false );
	}

	private function run(): void {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'wl_estado'
			),
			ARRAY_A
		);
		if ( ! is_array( $rows ) ) {
			return;
		}
		foreach ( $rows as $row ) {
			$post_id = (int) ( $row['post_id'] ?? 0 );
			$value   = trim( (string) ( $row['meta_value'] ?? '' ) );
			if ( $post_id <= 0 ) {
				continue;
			}
			$type = get_post_type( $post_id );
			if ( 'wl_vehiculo' === $type && '' !== $value ) {
				if ( ! term_exists( $value, 'wl_estado_vehiculo' ) ) {
					wp_insert_term( $value, 'wl_estado_vehiculo' );
				}
				wp_set_object_terms( $post_id, $value, 'wl_estado_vehiculo', false );
			}
			delete_post_meta( $post_id, 'wl_estado' );
		}
		unset( $wpdb );
	}
}
