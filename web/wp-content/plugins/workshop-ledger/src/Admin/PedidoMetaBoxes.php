<?php
/**
 * Native meta boxes for pedidos (Audatex-shape fields) and pedido items.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;

final class PedidoMetaBoxes {

	/** @var array<string, string> */
	private const PEDIDO_STRING = array(
		'wl_origen'                   => 'Origin',
		'wl_estado_pedido'            => 'Order status',
		'wl_referencia_ext'           => 'External reference',
		'wl_numero_pedido'            => 'Order number',
		'wl_cotizacion_id'            => 'Quote ID',
		'wl_siniestro'                => 'Claim',
		'wl_wan'                      => 'WAN',
		'wl_aseguradora'              => 'Insurer',
		'wl_vehiculo_label'           => 'Vehicle (label)',
		'wl_vin'                      => 'VIN',
		'wl_comision_pagada'          => 'Commission paid (0/1)',
		'wl_fecha_pago_comision'      => 'Commission paid date',
		'wl_referencia_pago_comision' => 'Commission payment ref',
		'wl_notas'                    => 'Notes',
	);

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_wl_pedido', array( $this, 'save_pedido' ), 10, 2 );
		add_action( 'save_post_wl_pedido_item', array( $this, 'save_item' ), 10, 2 );
		add_filter( 'manage_wl_pedido_posts_columns', array( $this, 'pedido_cols' ) );
		add_action( 'manage_wl_pedido_posts_custom_column', array( $this, 'pedido_col' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box( 'wl_pedido_audatex', __( 'Order · details', 'workshop-ledger' ), array( $this, 'render_pedido' ), 'wl_pedido', 'normal', 'high' );
		add_meta_box( 'wl_pedido_item_box', __( 'Item · link', 'workshop-ledger' ), array( $this, 'render_item' ), 'wl_pedido_item', 'normal', 'high' );
	}

	public function render_pedido( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_pedido_meta' );
		foreach ( self::PEDIDO_STRING as $key => $label ) {
			$val = (string) get_post_meta( $post->ID, $key, true );
			echo '<p><label>' . $this->escaper->html( __( $label, 'workshop-ledger' ) ) . '</label><br />';
			echo '<input type="text" class="regular-text" name="' . $this->escaper->attr( $key ) . '" value="' . $this->escaper->attr( $val ) . '" /></p>';
		}
		foreach ( array( 'wl_total_pedido' => 'Total', 'wl_comision_calculada' => 'Commission', 'wl_porcentaje_comision' => 'Commission %' ) as $key => $label ) {
			$val = get_post_meta( $post->ID, $key, true );
			echo '<p><label>' . $this->escaper->html( __( $label, 'workshop-ledger' ) ) . '</label><br />';
			echo '<input type="number" step="0.01" name="' . $this->escaper->attr( $key ) . '" value="' . $this->escaper->attr( (string) $val ) . '" /></p>';
		}
	}

	public function render_item( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_pedido_item_meta' );
		$pedido   = (int) get_post_meta( $post->ID, 'wl_pedido_id', true );
		$repuesto = (int) get_post_meta( $post->ID, 'wl_repuesto_id', true );
		$this->select_cpt( 'wl_pedido', 'wl_pedido_id', $pedido, __( 'Order', 'workshop-ledger' ) );
		$this->select_cpt( 'wl_repuesto', 'wl_repuesto_id', $repuesto, __( 'Part', 'workshop-ledger' ) );
		foreach ( array(
			'wl_precio_ofrecido' => array( 'number', __( 'Offered price', 'workshop-ledger' ) ),
			'wl_cantidad'        => array( 'number', __( 'Quantity', 'workshop-ledger' ) ),
			'wl_descripcion'     => array( 'text', __( 'Description', 'workshop-ledger' ) ),
			'wl_tipo_pieza'      => array( 'text', __( 'Part type', 'workshop-ledger' ) ),
			'wl_dias_entrega'    => array( 'number', __( 'Delivery days', 'workshop-ledger' ) ),
		) as $key => $cfg ) {
			$val = get_post_meta( $post->ID, $key, true );
			echo '<p><label>' . $this->escaper->html( $cfg[1] ) . '</label><br />';
			echo '<input type="' . $this->escaper->attr( $cfg[0] ) . '" name="' . $this->escaper->attr( $key ) . '" value="' . $this->escaper->attr( (string) $val ) . '" class="regular-text" /></p>';
		}
	}

	public function save_pedido( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! $this->ok_nonce( 'wl_pedido_meta' ) ) {
			return;
		}
		foreach ( array_keys( self::PEDIDO_STRING ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) ) );
			}
		}
		foreach ( array( 'wl_total_pedido', 'wl_comision_calculada', 'wl_porcentaje_comision' ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, (float) wp_unslash( $_POST[ $key ] ) );
			}
		}
	}

	public function save_item( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! $this->ok_nonce( 'wl_pedido_item_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, 'wl_pedido_id', (string) ( isset( $_POST['wl_pedido_id'] ) ? absint( wp_unslash( $_POST['wl_pedido_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'wl_repuesto_id', (string) ( isset( $_POST['wl_repuesto_id'] ) ? absint( wp_unslash( $_POST['wl_repuesto_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'wl_precio_ofrecido', isset( $_POST['wl_precio_ofrecido'] ) ? (float) wp_unslash( $_POST['wl_precio_ofrecido'] ) : 0 );
		update_post_meta( $post_id, 'wl_cantidad', isset( $_POST['wl_cantidad'] ) ? (int) wp_unslash( $_POST['wl_cantidad'] ) : 1 );
		update_post_meta( $post_id, 'wl_descripcion', isset( $_POST['wl_descripcion'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_descripcion'] ) ) : '' );
		update_post_meta( $post_id, 'wl_tipo_pieza', isset( $_POST['wl_tipo_pieza'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_tipo_pieza'] ) ) : '' );
		update_post_meta( $post_id, 'wl_dias_entrega', isset( $_POST['wl_dias_entrega'] ) ? (int) wp_unslash( $_POST['wl_dias_entrega'] ) : 0 );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function pedido_cols( array $cols ): array {
		$cols['wl_estado_pedido'] = __( 'Status', 'workshop-ledger' );
		$cols['wl_total']         = __( 'Total', 'workshop-ledger' );
		$cols['wl_origen']        = __( 'Origin', 'workshop-ledger' );
		return $cols;
	}

	public function pedido_col( string $col, int $post_id ): void {
		if ( 'wl_estado_pedido' === $col ) {
			echo $this->escaper->html( (string) get_post_meta( $post_id, 'wl_estado_pedido', true ) );
		} elseif ( 'wl_total' === $col ) {
			echo $this->escaper->html( number_format_i18n( (float) get_post_meta( $post_id, 'wl_total_pedido', true ), 2 ) );
		} elseif ( 'wl_origen' === $col ) {
			echo $this->escaper->html( (string) get_post_meta( $post_id, 'wl_origen', true ) );
		}
	}

	private function ok_nonce( string $action ): bool {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		if ( ! $this->gate->can_manage() ) {
			return false;
		}
		return $this->nonce->verify_post( $action );
	}

	private function select_cpt( string $type, string $name, int $selected, string $label ): void {
		echo '<p><label>' . $this->escaper->html( $label ) . '</label><br /><select name="' . $this->escaper->attr( $name ) . '"><option value="0">—</option>';
		foreach ( get_posts( array( 'post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
			printf( '<option value="%1$d" %2$s>%3$s</option>', (int) $p->ID, selected( $selected, (int) $p->ID, false ), $this->escaper->html( get_the_title( $p ) ) );
		}
		echo '</select></p>';
	}
}
