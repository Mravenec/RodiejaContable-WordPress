<?php
/**
 * Plugin deactivation (keeps data; uninstall removes it).
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Core;

final class Deactivator {

	public static function deactivate(): void {
		// Same as Activator: WP already authorizes deactivation; WP-CLI may lack a user.
		flush_rewrite_rules( false );
	}
}
