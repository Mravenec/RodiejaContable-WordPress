<?php
/**
 * Native wp-login.php branding for Rodieja Contable.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Front;

final class LoginChrome {

	public function register(): void {
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'login_headerurl', array( $this, 'header_url' ) );
		add_filter( 'login_headertext', array( $this, 'header_text' ) );
	}

	public function enqueue(): void {
		wp_enqueue_style(
			'rodieja-contable-login',
			RODIEJA_PLUGIN_URL . 'assets/css/login.css',
			array(),
			RODIEJA_PLUGIN_VERSION
		);
	}

	public function header_url(): string {
		return home_url( '/' );
	}

	public function header_text(): string {
		return (string) get_option( 'rodieja_contable_org_name', 'Rodieja Contable' );
	}
}
