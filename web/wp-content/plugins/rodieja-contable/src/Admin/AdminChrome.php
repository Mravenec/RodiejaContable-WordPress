<?php
/**
 * Soften core WP admin menus for Rodieja operators (native remove_menu_page).
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

		// Blog chrome unused by Contable — keep Pages/Media/Users/Settings.
		remove_menu_page( 'edit-comments.php' );
		remove_menu_page( 'edit.php' );
	}

	/**
	 * @param string[] $menu_order
	 * @return string[]
	 */
	public function reorder( array $menu_order ): array {
		$preferred = array(
			'index.php',
			'rodieja-contable',
			'upload.php',
			'edit.php?post_type=page',
			'users.php',
			'tools.php',
			'options-general.php',
		);
		$rest      = array_values( array_diff( $menu_order, $preferred ) );
		return array_values( array_unique( array_merge( $preferred, $rest ) ) );
	}
}
