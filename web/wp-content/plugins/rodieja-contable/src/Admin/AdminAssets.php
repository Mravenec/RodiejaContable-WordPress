<?php
/**
 * Admin + login + front CSS for modern native chrome.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;

final class AdminAssets {

	public function register(): void {
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front' ) );
	}

	public function body_class( string $classes ): string {
		if ( current_user_can( CapabilityGate::MANAGE ) || current_user_can( 'manage_options' ) || $this->is_rodieja_screen() ) {
			$classes .= ' rodieja-contable-admin';
		}
		if ( $this->is_rodieja_list_screen() ) {
			$classes .= ' rodieja-cpt-list';
		}
		return $classes;
	}

	/**
	 * CPT list screens (edit.php) for Ant Table–like chrome.
	 */
	private function is_rodieja_list_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! ( $screen instanceof \WP_Screen ) ) {
			return false;
		}
		if ( 'edit' !== $screen->base ) {
			return false;
		}
		return is_string( $screen->post_type ) && str_starts_with( $screen->post_type, 'rodieja_' );
	}

	public function enqueue_admin( string $hook ): void {
		unset( $hook );
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) && ! $this->is_rodieja_screen() ) {
			return;
		}
		$admin_css = RODIEJA_PLUGIN_DIR . 'assets/css/admin.css';
		$shell_css = RODIEJA_PLUGIN_DIR . 'assets/css/admin-shell.css';
		$ver_admin = is_readable( $admin_css ) ? (string) filemtime( $admin_css ) : RODIEJA_PLUGIN_VERSION;
		$ver_shell = is_readable( $shell_css ) ? (string) filemtime( $shell_css ) : RODIEJA_PLUGIN_VERSION;

		wp_enqueue_style(
			'rodieja-contable-admin',
			RODIEJA_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			$ver_admin
		);
		wp_enqueue_style(
			'rodieja-contable-admin-shell',
			RODIEJA_PLUGIN_URL . 'assets/css/admin-shell.css',
			array( 'rodieja-contable-admin' ),
			$ver_shell
		);
	}

	public function enqueue_front(): void {
		if ( is_admin() ) {
			return;
		}
		wp_enqueue_style(
			'rodieja-contable-front',
			RODIEJA_PLUGIN_URL . 'assets/css/front.css',
			array(),
			RODIEJA_PLUGIN_VERSION
		);
	}

	private function is_rodieja_screen(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen instanceof \WP_Screen ) {
			if ( is_string( $screen->post_type ) && str_starts_with( $screen->post_type, 'rodieja_' ) ) {
				return true;
			}
			if ( is_string( $screen->id ) && str_contains( $screen->id, 'rodieja' ) ) {
				return true;
			}
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( (string) wp_unslash( $_GET['page'] ) ) : '';
		if ( '' !== $page && str_starts_with( $page, 'rodieja' ) ) {
			return true;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$ptype = isset( $_GET['post_type'] ) ? sanitize_key( (string) wp_unslash( $_GET['post_type'] ) ) : '';
		return '' !== $ptype && str_starts_with( $ptype, 'rodieja_' );
	}
}
