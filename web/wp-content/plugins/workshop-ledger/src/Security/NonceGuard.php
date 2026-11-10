<?php
/**
 * Nonce verification helpers for admin forms and AJAX.
 *
 * Field names are unique per action so multiple meta boxes on one post-new
 * screen do not overwrite each other (Read-Only-safe native saves).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Security;

final class NonceGuard {

	public const ADMIN_ACTION = 'wl_plugin_admin';
	public const AJAX_ACTION  = 'wl_plugin_ajax';

	/**
	 * Stable unique input name for an action (e.g. wl_catalog_meta → _wl_n_wl_catalog_meta).
	 */
	public static function name_for( string $action ): string {
		$slug = preg_replace( '/[^a-z0-9]+/', '_', strtolower( $action ) );
		$slug = is_string( $slug ) ? trim( $slug, '_' ) : 'action';
		return '_wl_n_' . ( '' !== $slug ? $slug : 'action' );
	}

	public function field( string $action = self::ADMIN_ACTION, ?string $name = null ): void {
		wp_nonce_field( $action, $name ?? self::name_for( $action ), true, true );
	}

	public function verify_admin( string $action = self::ADMIN_ACTION, ?string $name = null ): void {
		check_admin_referer( $action, $name ?? self::name_for( $action ) );
	}

	/**
	 * Soft verify for save_post meta handlers (return false instead of wp_die).
	 */
	public function verify_post( string $action ): bool {
		$name = self::name_for( $action );
		if ( empty( $_POST[ $name ] ) ) {
			return false;
		}
		return (bool) wp_verify_nonce(
			sanitize_text_field( wp_unslash( (string) $_POST[ $name ] ) ),
			$action
		);
	}

	public function verify_ajax( string $action = self::AJAX_ACTION ): void {
		check_ajax_referer( $action, '_wl_nonce', true );
	}

	public function create( string $action = self::ADMIN_ACTION ): string {
		return wp_create_nonce( $action );
	}
}
