<?php
/**
 * Meta geo — provincia, cantón, lat, lng (vehículo + empleado).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\CostaRicaCantonesService;

final class GeoLocationMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private CostaRicaCantonesService $cantones
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_rodieja_vehiculo', array( $this, 'save' ), 10, 2 );
		add_action( 'save_post_rodieja_empleado', array( $this, 'save' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box( 'rodieja_geo', __( 'Location', 'rodieja-contable' ), array( $this, 'render' ), 'rodieja_vehiculo', 'side', 'default' );
		add_meta_box( 'rodieja_geo', __( 'Location', 'rodieja-contable' ), array( $this, 'render' ), 'rodieja_empleado', 'side', 'default' );
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_geo_meta' );
		$provincia = (string) get_post_meta( $post->ID, 'rodieja_provincia', true );
		$canton    = (string) get_post_meta( $post->ID, 'rodieja_canton', true );
		$lat       = (string) get_post_meta( $post->ID, 'rodieja_lat', true );
		$lng       = (string) get_post_meta( $post->ID, 'rodieja_lng', true );
		echo '<p><label>' . $this->escaper->html( __( 'Province', 'rodieja-contable' ) ) . '</label><br />';
		echo '<select name="rodieja_provincia" id="rodieja_provincia">';
		echo '<option value="">—</option>';
		foreach ( $this->cantones->provincias() as $p ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', $this->escaper->attr( $p ), selected( $provincia, $p, false ), $this->escaper->html( $p ) );
		}
		echo '</select></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Canton', 'rodieja-contable' ) ) . '</label><br />';
		echo '<select name="rodieja_canton">';
		echo '<option value="">—</option>';
		$cantones = '' !== $provincia ? $this->cantones->cantones_for( $provincia ) : array();
		foreach ( $cantones as $c ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', $this->escaper->attr( $c ), selected( $canton, $c, false ), $this->escaper->html( $c ) );
		}
		echo '</select></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Latitud', 'rodieja-contable' ) ) . '</label><br />';
		echo '<input type="text" name="rodieja_lat" class="regular-text" value="' . $this->escaper->attr( $lat ) . '" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Longitud', 'rodieja-contable' ) ) . '</label><br />';
		echo '<input type="text" name="rodieja_lng" class="regular-text" value="' . $this->escaper->attr( $lng ) . '" /></p>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || empty( $_POST['_rodieja_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_rodieja_nonce'] ) ), 'rodieja_geo_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, 'rodieja_provincia', isset( $_POST['rodieja_provincia'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['rodieja_provincia'] ) ) : '' );
		update_post_meta( $post_id, 'rodieja_canton', isset( $_POST['rodieja_canton'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['rodieja_canton'] ) ) : '' );
		update_post_meta( $post_id, 'rodieja_lat', isset( $_POST['rodieja_lat'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['rodieja_lat'] ) ) : '' );
		update_post_meta( $post_id, 'rodieja_lng', isset( $_POST['rodieja_lng'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['rodieja_lng'] ) ) : '' );
	}
}
