<?php
/**
 * Must-use hardening for the local harness (always on).
 *
 * @package Harness
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
	define( 'FORCE_SSL_ADMIN', true );
}

/**
 * Remove username enumeration via author query on front.
 */
add_action(
	'init',
	static function (): void {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	1
);

/**
 * Disable application password creation UI noise for subscribers (admins still can).
 */
add_filter(
	'wp_is_application_passwords_available_for_user',
	static function ( bool $available, $user ): bool {
		if ( ! ( $user instanceof WP_User ) ) {
			return false;
		}
		return user_can( $user, 'manage_options' );
	},
	10,
	2
);
