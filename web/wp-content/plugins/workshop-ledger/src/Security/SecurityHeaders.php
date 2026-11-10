<?php
/**
 * Hardening headers for admin + front when the plugin is active.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Security;

final class SecurityHeaders {

	public function register(): void {
		add_action( 'send_headers', array( $this, 'send' ) );
		add_action( 'admin_init', array( $this, 'send_admin' ) );
		add_filter( 'wp_headers', array( $this, 'filter_headers' ) );
	}

	public function send(): void {
		if ( headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
	}

	public function send_admin(): void {
		$this->send();
	}

	/**
	 * @param array<string, string> $headers
	 * @return array<string, string>
	 */
	public function filter_headers( array $headers ): array {
		$headers['X-Content-Type-Options'] = 'nosniff';
		$headers['X-Frame-Options']        = 'SAMEORIGIN';
		$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
		return $headers;
	}
}
