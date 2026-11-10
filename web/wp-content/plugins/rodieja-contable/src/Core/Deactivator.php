<?php
/**
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Core;

final class Deactivator {

	public static function deactivate(): void {
		flush_rewrite_rules( false );
	}
}
