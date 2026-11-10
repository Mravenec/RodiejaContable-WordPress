<?php
/**
 * Nonce verification helpers for admin forms and AJAX.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Security;

final class NonceGuard {

	public const ADMIN_ACTION = 'wl_plugin_admin';
	public const AJAX_ACTION  = 'wl_plugin_ajax';

	public function field( string $action = self::ADMIN_ACTION, string $name = '_wl_nonce' ): void {
		wp_nonce_field( $action, $name, true, true );
	}

	public function verify_admin( string $action = self::ADMIN_ACTION, string $name = '_wl_nonce' ): void {
		check_admin_referer( $action, $name );
	}

	public function verify_ajax( string $action = self::AJAX_ACTION ): void {
		check_ajax_referer( $action, '_wl_nonce', true );
	}

	public function create( string $action = self::ADMIN_ACTION ): string {
		return wp_create_nonce( $action );
	}
}
