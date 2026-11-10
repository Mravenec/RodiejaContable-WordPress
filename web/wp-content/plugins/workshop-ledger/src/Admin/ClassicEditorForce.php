<?php
/**
 * Force classic (meta-box) editor for Workshop Ledger domain CPTs.
 * Read-Only vehicle/parts forms are not Gutenberg canvases.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\PostTypes\DomainPostTypes;

final class ClassicEditorForce {

	public function register(): void {
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
	}

	/**
	 * @param bool   $use  Whether to use block editor.
	 * @param string $type Post type.
	 */
	public function disable_block_editor( bool $use, string $type ): bool {
		$domain = DomainPostTypes::historial_tracked();
		$domain[] = DomainPostTypes::EVENTO;
		if ( in_array( $type, $domain, true ) ) {
			return false;
		}
		return $use;
	}
}
