<?php
/**
 * Meta geo — provincia, cantón, lat, lng (vehículo + empleado).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\CostaRicaCantonesService;

final class GeoLocationMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private CostaRicaCantonesService $cantones
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_wl_vehiculo', array( $this, 'save' ), 10, 2 );
		add_action( 'save_post_wl_empleado', array( $this, 'save' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box( 'wl_geo', __( 'Location', 'workshop-ledger' ), array( $this, 'render' ), 'wl_vehiculo', 'side', 'default' );
		add_meta_box( 'wl_geo', __( 'Location', 'workshop-ledger' ), array( $this, 'render' ), 'wl_empleado', 'side', 'default' );
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_geo_meta' );
		$provincia = (string) get_post_meta( $post->ID, 'wl_provincia', true );
		$canton    = (string) get_post_meta( $post->ID, 'wl_canton', true );
		$lat       = (string) get_post_meta( $post->ID, 'wl_lat', true );
		$lng       = (string) get_post_meta( $post->ID, 'wl_lng', true );
		echo '<p><label>' . $this->escaper->html( __( 'Province', 'workshop-ledger' ) ) . '</label><br />';
		echo '<select name="wl_provincia" id="wl_provincia">';
		echo '<option value="">—</option>';
		foreach ( $this->cantones->provincias() as $p ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', $this->escaper->attr( $p ), selected( $provincia, $p, false ), $this->escaper->html( $p ) );
		}
		echo '</select></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Canton', 'workshop-ledger' ) ) . '</label><br />';
		echo '<select name="wl_canton">';
		echo '<option value="">—</option>';
		$cantones = '' !== $provincia ? $this->cantones->cantones_for( $provincia ) : array();
		foreach ( $cantones as $c ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', $this->escaper->attr( $c ), selected( $canton, $c, false ), $this->escaper->html( $c ) );
		}
		echo '</select></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Latitud', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="text" name="wl_lat" class="regular-text" value="' . $this->escaper->attr( $lat ) . '" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Longitud', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="text" name="wl_lng" class="regular-text" value="' . $this->escaper->attr( $lng ) . '" /></p>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || empty( $_POST['_wl_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_wl_nonce'] ) ), 'wl_geo_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, 'wl_provincia', isset( $_POST['wl_provincia'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_provincia'] ) ) : '' );
		update_post_meta( $post_id, 'wl_canton', isset( $_POST['wl_canton'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_canton'] ) ) : '' );
		update_post_meta( $post_id, 'wl_lat', isset( $_POST['wl_lat'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_lat'] ) ) : '' );
		update_post_meta( $post_id, 'wl_lng', isset( $_POST['wl_lng'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_lng'] ) ) : '' );
	}
}
