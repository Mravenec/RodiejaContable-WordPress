<?php
/**
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

final class HealthService {

	/**
	 * @return array{status:string,plugin:string,version:string,php:string,wordpress:string,native:bool}
	 */
	public function status(): array {
		return array(
			'status'    => 'ok',
			'plugin'    => 'workshop-ledger',
			'version'   => WL_PLUGIN_VERSION,
			'php'       => PHP_VERSION,
			'wordpress' => get_bloginfo( 'version' ),
			'native'    => true,
		);
	}
}
