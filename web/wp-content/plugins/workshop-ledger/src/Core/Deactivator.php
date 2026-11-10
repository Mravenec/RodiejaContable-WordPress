<?php
/**
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Core;

final class Deactivator {

	public static function deactivate(): void {
		flush_rewrite_rules( false );
	}
}
