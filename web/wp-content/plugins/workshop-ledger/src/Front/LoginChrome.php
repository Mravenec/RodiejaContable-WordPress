<?php
/**
 * Native wp-login.php branding for Workshop Ledger.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Front;

final class LoginChrome {

	public function register(): void {
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'login_headerurl', array( $this, 'header_url' ) );
		add_filter( 'login_headertext', array( $this, 'header_text' ) );
	}

	public function enqueue(): void {
		wp_enqueue_style(
			'workshop-ledger-login',
			WL_PLUGIN_URL . 'assets/css/login.css',
			array(),
			WL_PLUGIN_VERSION
		);
	}

	public function header_url(): string {
		return home_url( '/' );
	}

	public function header_text(): string {
		return (string) get_option( 'wl_org_name', 'Workshop Ledger' );
	}
}
