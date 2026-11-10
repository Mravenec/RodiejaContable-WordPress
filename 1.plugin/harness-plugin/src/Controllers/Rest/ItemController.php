<?php
/**
 * Protected REST CRUD — capability + schema validation.
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Controllers\Rest;

use Harness\Plugin\Security\CapabilityGate;
use Harness\Plugin\Services\ItemService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class ItemController {

	public const NAMESPACE = 'harness/v1';

	public function __construct(
		private ItemService $items,
		private CapabilityGate $gate
	) {}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/items',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'index' ),
					'permission_callback' => array( $this->gate, 'rest_can_manage' ),
					'args'                => array(
						'per_page' => array(
							'default'           => 20,
							'sanitize_callback' => 'absint',
							'validate_callback' => static fn( $v ) => $v >= 1 && $v <= 100,
						),
						'page'     => array(
							'default'           => 1,
							'sanitize_callback' => 'absint',
							'validate_callback' => static fn( $v ) => $v >= 1,
						),
					),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'store' ),
					'permission_callback' => array( $this->gate, 'rest_can_manage' ),
					'args'                => array(
						'title' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => static fn( $v ) => is_string( $v ) && '' !== trim( $v ),
						),
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/items/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'show' ),
					'permission_callback' => array( $this->gate, 'rest_can_manage' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
					),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'destroy' ),
					'permission_callback' => array( $this->gate, 'rest_can_manage' ),
					'args'                => array(
						'id' => array(
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
					),
				),
			)
		);
	}

	public function index( WP_REST_Request $request ): WP_REST_Response {
		$per_page = absint( $request->get_param( 'per_page' ) );
		$page     = absint( $request->get_param( 'page' ) );
		$offset   = max( 0, ( $page - 1 ) * $per_page );

		return new WP_REST_Response(
			array(
				'items' => $this->items->list_items( $per_page, $offset ),
			),
			200
		);
	}

	public function show( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$result = $this->items->get_item( absint( $request['id'] ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return new WP_REST_Response( $result, 200 );
	}

	public function store( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$result = $this->items->create_item( $request->get_param( 'title' ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return new WP_REST_Response( $result, 201 );
	}

	public function destroy( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$result = $this->items->delete_item( absint( $request['id'] ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return new WP_REST_Response( array( 'deleted' => true ), 200 );
	}
}
