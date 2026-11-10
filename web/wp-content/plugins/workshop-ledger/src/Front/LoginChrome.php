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
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 10, 3 );
	}

	/**
	 * Operators land on the Workshop Ledger hub (same as header "Open app").
	 *
	 * @param string             $redirect_to           Default redirect.
	 * @param string             $requested_redirect_to Requested redirect_to.
	 * @param \WP_User|\WP_Error $user                  Authenticated user or error.
	 */
	public function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( ! $user instanceof \WP_User ) {
			return $redirect_to;
		}
		if ( ! ( user_can( $user, 'manage_workshop_ledger' ) || user_can( $user, 'manage_options' ) ) ) {
			return $redirect_to;
		}

		$app          = admin_url( 'admin.php?page=workshop-ledger' );
		$requested    = untrailingslashit( (string) $requested_redirect_to );
		$home         = untrailingslashit( home_url( '/' ) );
		$admin_root   = untrailingslashit( admin_url() );
		$admin_index  = untrailingslashit( admin_url( 'index.php' ) );

		if ( '' === $requested
			|| $requested === $home
			|| $requested === $admin_root
			|| $requested === $admin_index ) {
			return $app;
		}

		return $redirect_to ?: $app;
	}

	public function enqueue(): void {
		$path = WL_PLUGIN_DIR . 'assets/css/login.css';
		$ver  = is_readable( $path ) ? (string) filemtime( $path ) : WL_PLUGIN_VERSION;
		wp_enqueue_style(
			'workshop-ledger-login',
			WL_PLUGIN_URL . 'assets/css/login.css',
			array(),
			$ver
		);
	}

	public function header_url(): string {
		return home_url( '/' );
	}

	public function header_text(): string {
		return (string) get_option( 'wl_org_name', 'Workshop Ledger' );
	}
}
