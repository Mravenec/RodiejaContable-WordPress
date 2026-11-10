<?php
/**
 * Native meta boxes for pedidos (Audatex-shape fields) and pedido items.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;

final class PedidoMetaBoxes {

	/** @var array<string, string> */
	private const PEDIDO_STRING = array(
		'rodieja_origen'                   => 'Origen',
		'rodieja_estado_pedido'            => 'Estado',
		'rodieja_referencia_ext'           => 'Referencia ext',
		'rodieja_numero_pedido'            => 'Número pedido',
		'rodieja_cotizacion_id'            => 'Cotización ID',
		'rodieja_siniestro'                => 'Siniestro',
		'rodieja_wan'                      => 'WAN',
		'rodieja_aseguradora'              => 'Aseguradora',
		'rodieja_vehiculo_label'           => 'Vehículo (label)',
		'rodieja_vin'                      => 'VIN',
		'rodieja_comision_pagada'          => 'Comisión pagada (0/1)',
		'rodieja_fecha_pago_comision'      => 'Fecha pago comisión',
		'rodieja_referencia_pago_comision' => 'Ref. pago comisión',
		'rodieja_notas'                    => 'Notas',
	);

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_rodieja_pedido', array( $this, 'save_pedido' ), 10, 2 );
		add_action( 'save_post_rodieja_pedido_item', array( $this, 'save_item' ), 10, 2 );
		add_filter( 'manage_rodieja_pedido_posts_columns', array( $this, 'pedido_cols' ) );
		add_action( 'manage_rodieja_pedido_posts_custom_column', array( $this, 'pedido_col' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box( 'rodieja_pedido_audatex', __( 'Order · datos', 'rodieja-contable' ), array( $this, 'render_pedido' ), 'rodieja_pedido', 'normal', 'high' );
		add_meta_box( 'rodieja_pedido_item_box', __( 'Item · link', 'rodieja-contable' ), array( $this, 'render_item' ), 'rodieja_pedido_item', 'normal', 'high' );
	}

	public function render_pedido( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_pedido_meta' );
		foreach ( self::PEDIDO_STRING as $key => $label ) {
			$val = (string) get_post_meta( $post->ID, $key, true );
			echo '<p><label>' . $this->escaper->html( __( $label, 'rodieja-contable' ) ) . '</label><br />';
			echo '<input type="text" class="regular-text" name="' . $this->escaper->attr( $key ) . '" value="' . $this->escaper->attr( $val ) . '" /></p>';
		}
		foreach ( array( 'rodieja_total_pedido' => 'Total', 'rodieja_comision_calculada' => 'Comisión', 'rodieja_porcentaje_comision' => '% comisión' ) as $key => $label ) {
			$val = get_post_meta( $post->ID, $key, true );
			echo '<p><label>' . $this->escaper->html( __( $label, 'rodieja-contable' ) ) . '</label><br />';
			echo '<input type="number" step="0.01" name="' . $this->escaper->attr( $key ) . '" value="' . $this->escaper->attr( (string) $val ) . '" /></p>';
		}
	}

	public function render_item( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_pedido_item_meta' );
		$pedido   = (int) get_post_meta( $post->ID, 'rodieja_pedido_id', true );
		$repuesto = (int) get_post_meta( $post->ID, 'rodieja_repuesto_id', true );
		$this->select_cpt( 'rodieja_pedido', 'rodieja_pedido_id', $pedido, __( 'Order', 'rodieja-contable' ) );
		$this->select_cpt( 'rodieja_repuesto', 'rodieja_repuesto_id', $repuesto, __( 'Part', 'rodieja-contable' ) );
		foreach ( array(
			'rodieja_precio_ofrecido' => array( 'number', __( 'Price ofrecido', 'rodieja-contable' ) ),
			'rodieja_cantidad'        => array( 'number', __( 'Quantity', 'rodieja-contable' ) ),
			'rodieja_descripcion'     => array( 'text', __( 'Description', 'rodieja-contable' ) ),
			'rodieja_tipo_pieza'      => array( 'text', __( 'Tipo pieza', 'rodieja-contable' ) ),
			'rodieja_dias_entrega'    => array( 'number', __( 'Delivery days', 'rodieja-contable' ) ),
		) as $key => $cfg ) {
			$val = get_post_meta( $post->ID, $key, true );
			echo '<p><label>' . $this->escaper->html( $cfg[1] ) . '</label><br />';
			echo '<input type="' . $this->escaper->attr( $cfg[0] ) . '" name="' . $this->escaper->attr( $key ) . '" value="' . $this->escaper->attr( (string) $val ) . '" class="regular-text" /></p>';
		}
	}

	public function save_pedido( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! $this->ok_nonce( 'rodieja_pedido_meta' ) ) {
			return;
		}
		foreach ( array_keys( self::PEDIDO_STRING ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) ) );
			}
		}
		foreach ( array( 'rodieja_total_pedido', 'rodieja_comision_calculada', 'rodieja_porcentaje_comision' ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, (float) wp_unslash( $_POST[ $key ] ) );
			}
		}
	}

	public function save_item( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! $this->ok_nonce( 'rodieja_pedido_item_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, 'rodieja_pedido_id', (string) ( isset( $_POST['rodieja_pedido_id'] ) ? absint( wp_unslash( $_POST['rodieja_pedido_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'rodieja_repuesto_id', (string) ( isset( $_POST['rodieja_repuesto_id'] ) ? absint( wp_unslash( $_POST['rodieja_repuesto_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'rodieja_precio_ofrecido', isset( $_POST['rodieja_precio_ofrecido'] ) ? (float) wp_unslash( $_POST['rodieja_precio_ofrecido'] ) : 0 );
		update_post_meta( $post_id, 'rodieja_cantidad', isset( $_POST['rodieja_cantidad'] ) ? (int) wp_unslash( $_POST['rodieja_cantidad'] ) : 1 );
		update_post_meta( $post_id, 'rodieja_descripcion', isset( $_POST['rodieja_descripcion'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['rodieja_descripcion'] ) ) : '' );
		update_post_meta( $post_id, 'rodieja_tipo_pieza', isset( $_POST['rodieja_tipo_pieza'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['rodieja_tipo_pieza'] ) ) : '' );
		update_post_meta( $post_id, 'rodieja_dias_entrega', isset( $_POST['rodieja_dias_entrega'] ) ? (int) wp_unslash( $_POST['rodieja_dias_entrega'] ) : 0 );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function pedido_cols( array $cols ): array {
		$cols['rodieja_estado'] = __( 'Status', 'rodieja-contable' );
		$cols['rodieja_total']  = __( 'Total', 'rodieja-contable' );
		$cols['rodieja_origen'] = __( 'Origen', 'rodieja-contable' );
		return $cols;
	}

	public function pedido_col( string $col, int $post_id ): void {
		if ( 'rodieja_estado' === $col ) {
			echo $this->escaper->html( (string) get_post_meta( $post_id, 'rodieja_estado_pedido', true ) );
		} elseif ( 'rodieja_total' === $col ) {
			echo $this->escaper->html( number_format_i18n( (float) get_post_meta( $post_id, 'rodieja_total_pedido', true ), 2 ) );
		} elseif ( 'rodieja_origen' === $col ) {
			echo $this->escaper->html( (string) get_post_meta( $post_id, 'rodieja_origen', true ) );
		}
	}

	private function ok_nonce( string $action ): bool {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		if ( ! $this->gate->can_manage() || empty( $_POST['_rodieja_nonce'] ) ) {
			return false;
		}
		return (bool) wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_rodieja_nonce'] ) ), $action );
	}

	private function select_cpt( string $type, string $name, int $selected, string $label ): void {
		echo '<p><label>' . $this->escaper->html( $label ) . '</label><br /><select name="' . $this->escaper->attr( $name ) . '"><option value="0">—</option>';
		foreach ( get_posts( array( 'post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
			printf( '<option value="%1$d" %2$s>%3$s</option>', (int) $p->ID, selected( $selected, (int) $p->ID, false ), $this->escaper->html( get_the_title( $p ) ) );
		}
		echo '</select></p>';
	}
}
