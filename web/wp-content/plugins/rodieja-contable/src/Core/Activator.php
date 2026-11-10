<?php
/**
 * Activation — native caps/roles only (no custom tables).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Core;

use Rodieja\Contable\Roles\RoleRegistrar;

final class Activator {

	public static function activate(): void {
		update_option( 'rodieja_contable_version', RODIEJA_PLUGIN_VERSION, false );
		( new RoleRegistrar() )->install();
		flush_rewrite_rules( false );
	}
}
