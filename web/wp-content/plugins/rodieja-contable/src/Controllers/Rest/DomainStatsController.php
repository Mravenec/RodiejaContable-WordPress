<?php
/**
 * Authenticated domain counts (native REST).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Controllers\Rest;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Services\DomainStatsService;
use WP_REST_Request;
use WP_REST_Response;

final class DomainStatsController {

	public const NAMESPACE = 'rodieja/v1';

	public function __construct( private DomainStatsService $stats ) {}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/stats',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( $this, 'can_view' ),
			)
		);
	}

	public function can_view(): bool {
		return current_user_can( CapabilityGate::MANAGE ) || current_user_can( 'manage_options' );
	}

	public function get_stats( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );
		return new WP_REST_Response(
			array(
				'native' => true,
				'counts' => $this->stats->counts(),
			),
			200
		);
	}
}
