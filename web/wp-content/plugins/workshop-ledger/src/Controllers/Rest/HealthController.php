<?php
/**
 * Public health probe (allowlisted).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Controllers\Rest;

use WorkshopLedger\Services\HealthService;
use WP_REST_Request;
use WP_REST_Response;

final class HealthController {

	public const NAMESPACE = 'workshop-ledger/v1';

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
