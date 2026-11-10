<?php
/**
 * Admin CSS — React palette, Rodieja screens only.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

final class AdminAssets {

	public function register(): void {
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function body_class( string $classes ): string {
		if ( $this->is_rodieja_context() ) {
			$classes .= ' rodieja-contable-admin';
		}
		return $classes;
	}

	public function enqueue( string $hook ): void {
		unset( $hook );
		if ( ! $this->is_rodieja_context() ) {
			return;
		}
		wp_enqueue_style(
			'rodieja-contable-admin',
			RODIEJA_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			RODIEJA_PLUGIN_VERSION
		);
	}

	private function is_rodieja_context(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen instanceof \WP_Screen ) {
			if ( is_string( $screen->post_type ) && str_starts_with( $screen->post_type, 'rodieja_' ) ) {
				return true;
			}
			if ( is_string( $screen->id ) && str_contains( $screen->id, 'rodieja' ) ) {
				return true;
			}
			if ( is_string( $screen->parent_base ) && 'rodieja-contable' === $screen->parent_base ) {
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
