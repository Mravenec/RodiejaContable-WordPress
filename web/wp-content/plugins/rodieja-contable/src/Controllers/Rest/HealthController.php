<?php
/**
 * Public health probe (allowlisted).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Controllers\Rest;

use Rodieja\Contable\Services\HealthService;
use WP_REST_Request;
use WP_REST_Response;

final class HealthController {

	public const NAMESPACE = 'rodieja/v1';

	public function __construct( private HealthService $health ) {}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/health',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_health' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function get_health( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );
		return new WP_REST_Response( $this->health->status(), 200 );
	}
}
