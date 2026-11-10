<?php
/**
 * Activation — native caps/roles + one-time brand migrate (no custom tables).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Core;

use WorkshopLedger\Roles\RoleRegistrar;

final class Activator {

	public static function activate(): void {
		BrandMigrate::maybe_run();
		update_option( 'wl_version', WL_PLUGIN_VERSION, false );
		( new RoleRegistrar() )->install();
		flush_rewrite_rules( false );
	}
}
