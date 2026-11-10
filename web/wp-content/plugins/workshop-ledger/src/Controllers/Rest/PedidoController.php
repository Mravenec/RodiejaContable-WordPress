<?php
/**
 * Secured REST for pedidos + items (native CPT/meta).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Controllers\Rest;

use WorkshopLedger\Security\CapabilityGate;
use WP_REST_Request;
use WP_REST_Response;

final class PedidoController {

	public const NAMESPACE = 'workshop-ledger/v1';

	public function __construct( private CapabilityGate $gate ) {}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/pedidos',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_pedidos' ),
					'permission_callback' => array( $this->gate, 'rest_can_manage' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_pedido' ),
					'permission_callback' => array( $this->gate, 'rest_can_manage' ),
				),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/pedidos/(?P<id>\d+)/items',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this->gate, 'rest_can_manage' ),
			)
		);
	}

	public function list_pedidos( WP_REST_Request $request ): WP_REST_Response {
		unset( $request );
		$ids = get_posts(
			array(
				'post_type'      => 'wl_pedido',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'fields'         => 'ids',
			)
		);
		$out = array();
		foreach ( $ids as $id ) {
			$out[] = array(
				'id'     => (int) $id,
				'title'  => get_the_title( (int) $id ),
				'estado' => get_post_meta( (int) $id, 'wl_estado_pedido', true ),
				'total'  => (float) get_post_meta( (int) $id, 'wl_total_pedido', true ),
				'origen' => get_post_meta( (int) $id, 'wl_origen', true ),
			);
		}
		return new WP_REST_Response( array( 'native' => true, 'pedidos' => $out ), 200 );
	}

	public function create_pedido( WP_REST_Request $request ): WP_REST_Response {
		$title = sanitize_text_field( (string) ( $request->get_param( 'title' ) ?: 'Pedido' ) );
		$id    = wp_insert_post(
			array(
				'post_type'   => 'wl_pedido',
				'post_title'  => $title,
				'post_status' => 'publish',
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return new WP_REST_Response( array( 'error' => $id->get_error_message() ), 500 );
		}
		$map = array(
			'origen'         => 'wl_origen',
			'estado'         => 'wl_estado_pedido',
			'cotizacion_id'  => 'wl_cotizacion_id',
			'siniestro'      => 'wl_siniestro',
			'wan'            => 'wl_wan',
			'aseguradora'    => 'wl_aseguradora',
			'vin'            => 'wl_vin',
			'numero_pedido'  => 'wl_numero_pedido',
		);
		foreach ( $map as $param => $meta ) {
			$v = $request->get_param( $param );
			if ( null !== $v && '' !== $v ) {
				update_post_meta( (int) $id, $meta, sanitize_text_field( (string) $v ) );
			}
		}
		if ( null !== $request->get_param( 'total' ) ) {
			update_post_meta( (int) $id, 'wl_total_pedido', (float) $request->get_param( 'total' ) );
		}
		if ( ! get_post_meta( (int) $id, 'wl_origen', true ) ) {
			update_post_meta( (int) $id, 'wl_origen', 'manual' );
		}
		if ( ! get_post_meta( (int) $id, 'wl_estado_pedido', true ) ) {
			update_post_meta( (int) $id, 'wl_estado_pedido', 'Awaiting confirmation' );
		}
		return new WP_REST_Response( array( 'id' => (int) $id, 'native' => true ), 201 );
	}

	public function create_item( WP_REST_Request $request ): WP_REST_Response {
		$pedido_id = absint( $request->get_param( 'id' ) );
		if ( ! $pedido_id || 'wl_pedido' !== get_post_type( $pedido_id ) ) {
			return new WP_REST_Response( array( 'error' => 'pedido_not_found' ), 404 );
		}
		$item_id = wp_insert_post(
			array(
				'post_type'   => 'wl_pedido_item',
				'post_title'  => sanitize_text_field( (string) ( $request->get_param( 'title' ) ?: 'Ítem' ) ),
				'post_status' => 'publish',
			),
			true
		);
		if ( is_wp_error( $item_id ) ) {
			return new WP_REST_Response( array( 'error' => $item_id->get_error_message() ), 500 );
		}
		update_post_meta( (int) $item_id, 'wl_pedido_id', (string) $pedido_id );
		update_post_meta( (int) $item_id, 'wl_repuesto_id', (string) absint( $request->get_param( 'repuesto_id' ) ) );
		update_post_meta( (int) $item_id, 'wl_precio_ofrecido', (float) $request->get_param( 'precio_ofrecido' ) );
		update_post_meta( (int) $item_id, 'wl_cantidad', (int) ( $request->get_param( 'cantidad' ) ?: 1 ) );
		update_post_meta( (int) $item_id, 'wl_descripcion', sanitize_text_field( (string) $request->get_param( 'descripcion' ) ) );
		return new WP_REST_Response( array( 'id' => (int) $item_id, 'pedido_id' => $pedido_id, 'native' => true ), 201 );
	}
}
