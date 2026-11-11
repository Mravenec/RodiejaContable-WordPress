<?php
/**
 * Core WordPress admin menus stay, under the Workshop Ledger product menu.
 * Product slugs lead; Dashboard, Posts, Media, Pages, Comments, Appearance,
 * Plugins, Users, Tools, and Settings follow. WordPress capabilities still
 * hide screens the current role cannot use.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

final class AdminChrome {

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'tidy_core_menus' ), 999 );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'reorder' ) );
	}

	/**
	 * Core menus stay. Do not remove Dashboard, Posts, Media, Pages,
	 * Comments, Appearance, Plugins, Users, Tools, or Settings.
	 */
	public function tidy_core_menus(): void {
		// Core screens stay registered. WordPress hides what this role cannot open.
	}

	/**
	 * Product menu first, then the default WordPress screens, then anything else.
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
			'index.php',
			'edit.php',
			'upload.php',
			'edit.php?post_type=page',
			'edit-comments.php',
			'themes.php',
			'plugins.php',
			'users.php',
			'tools.php',
			'options-general.php',
		);
		$rest = array_values( array_diff( $menu_order, $preferred ) );
		return array_values( array_unique( array_merge( $preferred, $rest ) ) );
	}
}
