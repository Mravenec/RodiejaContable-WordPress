<?php
/**
 * Authenticated finance totals REST (mutations would use same gate).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Controllers\Rest;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Services\FinanceTotalsService;
use WP_REST_Request;
use WP_REST_Response;

final class FinanceController {

	public const NAMESPACE = 'workshop-ledger/v1';

	public function __construct(
		private FinanceTotalsService $totals,
		private CapabilityGate $gate
	) {}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/finance/totals',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_totals' ),
				'permission_callback' => array( $this->gate, 'rest_can_manage' ),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/finance/transactions',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_transaction' ),
				'permission_callback' => array( $this->gate, 'rest_can_manage' ),
				'args'                => array(
					'title'  => array( 'required' => true, 'type' => 'string' ),
					'monto'  => array( 'required' => true, 'type' => 'number' ),
					'sentido'=> array( 'required' => true, 'type' => 'string' ),
					'fecha'  => array( 'required' => false, 'type' => 'string' ),
				),
			)
		);
	}

	public function get_totals( WP_REST_Request $request ): WP_REST_Response {
		$month = $request->get_param( 'month' );
		$month = is_string( $month ) && $month !== '' ? sanitize_text_field( $month ) : null;
		return new WP_REST_Response(
			array(
				'native' => true,
				'totals' => $this->totals->totals( $month ),
			),
			200
		);
	}

	public function create_transaction( WP_REST_Request $request ): WP_REST_Response {
		$sentido = sanitize_key( (string) $request->get_param( 'sentido' ) );
		if ( ! in_array( $sentido, array( 'ingreso', 'egreso' ), true ) ) {
			return new WP_REST_Response( array( 'error' => 'invalid_sentido' ), 400 );
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'wl_transaccion',
				'post_title'  => sanitize_text_field( (string) $request->get_param( 'title' ) ),
				'post_status' => 'publish',
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return new WP_REST_Response( array( 'error' => $id->get_error_message() ), 500 );
		}
		update_post_meta( (int) $id, 'wl_monto', (float) $request->get_param( 'monto' ) );
		update_post_meta( (int) $id, 'wl_sentido', $sentido );
		$fecha = $request->get_param( 'fecha' );
		update_post_meta( (int) $id, 'wl_fecha', $fecha ? sanitize_text_field( (string) $fecha ) : gmdate( 'Y-m-d' ) );
		$term = 'ingreso' === $sentido ? 'Ingreso' : 'Egreso';
		if ( ! term_exists( $term, 'wl_tipo_tx' ) ) {
			wp_insert_term( $term, 'wl_tipo_tx' );
		}
		wp_set_object_terms( (int) $id, $term, 'wl_tipo_tx' );
		return new WP_REST_Response( array( 'id' => (int) $id, 'native' => true ), 201 );
	}
}
