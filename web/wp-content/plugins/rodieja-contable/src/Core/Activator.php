<?php
/**
 * Activation: roles + rewrite flush.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Core;

use Rodieja\Contable\Roles\RoleRegistrar;

final class Activator {

	public static function activate(): void {
		RoleRegistrar::install_roles();
		update_option( 'rodieja_contable_version', RODIEJA_CONTABLE_VERSION, false );
		flush_rewrite_rules( false );
	}
}
