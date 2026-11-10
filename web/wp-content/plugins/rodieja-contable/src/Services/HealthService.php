<?php
/**
 * Read-only health payload.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class HealthService {

	/**
	 * @return array{status:string,plugin:string,version:string,mode:string}
	 */
	public function status(): array {
		return array(
			'status'  => 'ok',
			'plugin'  => 'rodieja-contable',
			'version' => RODIEJA_CONTABLE_VERSION,
			'mode'    => 'native-wordpress-cpt',
		);
	}
}
