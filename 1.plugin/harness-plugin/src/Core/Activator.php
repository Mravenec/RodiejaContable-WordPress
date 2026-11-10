<?php
/**
 * Plugin activation: schema + capability bootstrap.
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Core;

use Harness\Plugin\Repositories\ItemRepository;

final class Activator {

	public static function activate(): void {
		// WordPress already gates who may activate plugins (admin UI / WP-CLI).
		// Do not call current_user_can() here — WP-CLI often has no user context.

		ItemRepository::install_schema();
		update_option( 'harness_plugin_version', HARNESS_PLUGIN_VERSION, false );
		update_option( 'harness_plugin_schema_version', ItemRepository::SCHEMA_VERSION, false );

		$role = get_role( 'administrator' );
		if ( $role instanceof \WP_Role ) {
			$role->add_cap( 'manage_harness_plugin' );
		}

		flush_rewrite_rules( false );
	}
}
