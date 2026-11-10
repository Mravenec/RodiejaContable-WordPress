<?php
/**
 * Neutral health payload — no domain secrets.
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Services;

final class HealthService {

	/**
	 * @return array{status:string,plugin:string,version:string,php:string,wordpress:string}
	 */
	public function status(): array {
		return array(
			'status'     => 'ok',
			'plugin'     => 'harness-plugin',
			'version'    => HARNESS_PLUGIN_VERSION,
			'php'        => PHP_VERSION,
			'wordpress'  => get_bloginfo( 'version' ),
		);
	}
}
