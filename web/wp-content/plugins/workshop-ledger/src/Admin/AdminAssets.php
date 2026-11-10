<?php
/**
 * Admin + login + front CSS for modern native chrome.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;

final class AdminAssets {

	public function register(): void {
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front' ) );
	}

	public function body_class( string $classes ): string {
		if ( current_user_can( CapabilityGate::MANAGE ) || current_user_can( 'manage_options' ) || $this->is_wl_screen() ) {
			$classes .= ' workshop-ledger-admin';
		}
		if ( $this->is_wl_list_screen() ) {
			$classes .= ' wl-cpt-list';
		}
		return $classes;
	}

	/**
	 * CPT / taxonomy list screens for Ant Table–like chrome (all wl_*).
	 */
	private function is_wl_list_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! ( $screen instanceof \WP_Screen ) ) {
			return false;
		}
		if ( 'edit' === $screen->base ) {
			return is_string( $screen->post_type ) && str_starts_with( $screen->post_type, 'wl_' );
		}
		if ( 'edit-tags' === $screen->base || 'term' === $screen->base ) {
			return is_string( $screen->taxonomy ) && str_starts_with( $screen->taxonomy, 'wl_' );
		}
		return false;
	}

	public function enqueue_admin( string $hook ): void {
		unset( $hook );
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) && ! $this->is_wl_screen() ) {
			return;
		}
		$admin_css = WL_PLUGIN_DIR . 'assets/css/admin.css';
		$shell_css = WL_PLUGIN_DIR . 'assets/css/admin-shell.css';
		$ver_admin = is_readable( $admin_css ) ? (string) filemtime( $admin_css ) : WL_PLUGIN_VERSION;
		$ver_shell = is_readable( $shell_css ) ? (string) filemtime( $shell_css ) : WL_PLUGIN_VERSION;

		wp_enqueue_style(
			'workshop-ledger-admin',
			WL_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$ver_admin
		);
		wp_enqueue_style(
			'workshop-ledger-admin-shell',
			WL_PLUGIN_URL . 'assets/css/admin-shell.css',
			array( 'workshop-ledger-admin' ),
			$ver_shell
		);

		$list_css = WL_PLUGIN_DIR . 'assets/css/admin-list.css';
		$ver_list = is_readable( $list_css ) ? (string) filemtime( $list_css ) : WL_PLUGIN_VERSION;
		wp_enqueue_style(
			'workshop-ledger-admin-list',
			WL_PLUGIN_URL . 'assets/css/admin-list.css',
			array( 'workshop-ledger-admin-shell' ),
			$ver_list
		);

		$menu_js = WL_PLUGIN_DIR . 'assets/js/admin-menu-collapse.js';
		$ver_js  = is_readable( $menu_js ) ? (string) filemtime( $menu_js ) : WL_PLUGIN_VERSION;
		wp_enqueue_script(
			'workshop-ledger-admin-menu',
			WL_PLUGIN_URL . 'assets/js/admin-menu-collapse.js',
			array(),
			$ver_js,
			true
		);
	}

	public function enqueue_front(): void {
		if ( is_admin() ) {
			return;
		}
		wp_enqueue_style(
			'workshop-ledger-front',
			WL_PLUGIN_URL . 'assets/css/front.css',
			array(),
			WL_PLUGIN_VERSION
		);
	}

	private function is_wl_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen instanceof \WP_Screen ) {
			if ( is_string( $screen->post_type ) && str_starts_with( $screen->post_type, 'wl_' ) ) {
				return true;
			}
			if ( is_string( $screen->id ) && str_contains( $screen->id, 'wl-' ) || str_contains( $screen->id, 'workshop' ) ) {
				return true;
			}
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( (string) wp_unslash( $_GET['page'] ) ) : '';
		if ( '' !== $page && str_starts_with( $page, 'wl-' ) || str_starts_with( $page, 'workshop' ) ) {
			return true;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$ptype = isset( $_GET['post_type'] ) ? sanitize_key( (string) wp_unslash( $_GET['post_type'] ) ) : '';
		return '' !== $ptype && str_starts_with( $ptype, 'wl_' );
	}
}
