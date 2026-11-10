<?php
/**
 * Soften core WP admin menus for Rodieja operators (native remove_menu_page).
 * Mirrors Read-Only sider: product modules first; keep Media + Users.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;

final class AdminChrome {

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'tidy_core_menus' ), 999 );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'reorder' ) );
	}

	public function tidy_core_menus(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Hide WP chrome Read-Only never shows (still native — not removed from disk).
		remove_menu_page( 'index.php' );           // WP Dashboard (Rodieja Home replaces it)
		remove_menu_page( 'edit.php' );            // Posts
		remove_menu_page( 'edit-comments.php' );   // Comments
		remove_menu_page( 'edit.php?post_type=page' ); // Pages
		remove_menu_page( 'themes.php' );          // Appearance
		remove_menu_page( 'plugins.php' );         // Plugins
		remove_menu_page( 'tools.php' );           // WP Tools (Settings → Tools is ours)
		remove_menu_page( 'options-general.php' ); // WP Settings (Rodieja Settings replaces it)

		// Keep: Media (upload.php), Users (users.php) — roles assignment is native Users.
	}

	/**
	 * Read-Only sider order: Home → Vehicles → Inventory → Finance → Audatex InPart → Reports → Settings.
	 *
	 * @param string[] $menu_order
	 * @return string[]
	 */
	public function reorder( array $menu_order ): array {
		$preferred = array(
			AdminMenu::SLUG_INICIO,
			AdminMenu::SLUG_VEHICULOS,
			AdminMenu::SLUG_INVENTARIO,
			AdminMenu::SLUG_FINANZAS,
			AdminMenu::SLUG_INPART,
			AdminMenu::SLUG_REPORTES,
			AdminMenu::SLUG_CONFIG,
			'upload.php',
			'users.php',
		);
		$rest = array_values( array_diff( $menu_order, $preferred ) );
		return array_values( array_unique( array_merge( $preferred, $rest ) ) );
	}
}
