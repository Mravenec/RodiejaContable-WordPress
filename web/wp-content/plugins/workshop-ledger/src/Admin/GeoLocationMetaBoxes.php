<?php
/**
 * Meta geo — province, canton, lat, lng (vehicle + employee) with JS cascade.
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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_cascade' ) );
	}

	public function add_boxes(): void {
		add_meta_box( 'wl_geo', __( 'Location', 'workshop-ledger' ), array( $this, 'render' ), 'wl_vehiculo', 'side', 'default' );
		add_meta_box( 'wl_geo', __( 'Location', 'workshop-ledger' ), array( $this, 'render' ), 'wl_empleado', 'side', 'default' );
	}

	public function enqueue_cascade( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! ( $screen instanceof \WP_Screen ) || ! in_array( $screen->post_type, array( 'wl_vehiculo', 'wl_empleado' ), true ) ) {
			return;
		}
		$path = WL_PLUGIN_DIR . 'assets/js/geo-canton-cascade.js';
		$ver  = is_readable( $path ) ? (string) filemtime( $path ) : WL_PLUGIN_VERSION;
		wp_enqueue_script(
			'workshop-ledger-geo-canton',
			WL_PLUGIN_URL . 'assets/js/geo-canton-cascade.js',
			array(),
			$ver,
			true
		);
		$map = array();
		foreach ( $this->cantones->provincias() as $p ) {
			$map[ $p ] = $this->cantones->cantones_for( $p );
		}
		wp_localize_script( 'workshop-ledger-geo-canton', 'wlGeoCantones', $map );
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
		echo '<p><label for="wl_provincia">' . $this->escaper->html( __( 'Province', 'workshop-ledger' ) ) . '</label><br />';
		echo '<select name="wl_provincia" id="wl_provincia">';
		echo '<option value="">—</option>';
		foreach ( $this->cantones->provincias() as $p ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', $this->escaper->attr( $p ), selected( $provincia, $p, false ), $this->escaper->html( $p ) );
		}
		echo '</select></p>';
		echo '<p><label for="wl_canton">' . $this->escaper->html( __( 'Canton', 'workshop-ledger' ) ) . '</label><br />';
		echo '<select name="wl_canton" id="wl_canton">';
		echo '<option value="">—</option>';
		$cantones = '' !== $provincia ? $this->cantones->cantones_for( $provincia ) : array();
		foreach ( $cantones as $c ) {
			printf( '<option value="%1$s" %2$s>%3$s</option>', $this->escaper->attr( $c ), selected( $canton, $c, false ), $this->escaper->html( $c ) );
		}
		echo '</select></p>';
		echo '<p><label for="wl_lat">' . $this->escaper->html( __( 'Latitude', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="text" name="wl_lat" id="wl_lat" class="regular-text" value="' . $this->escaper->attr( $lat ) . '" /></p>';
		echo '<p><label for="wl_lng">' . $this->escaper->html( __( 'Longitude', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="text" name="wl_lng" id="wl_lng" class="regular-text" value="' . $this->escaper->attr( $lng ) . '" /></p>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || ! $this->nonce->verify_post( 'wl_geo_meta' ) ) {
			return;
		}
		update_post_meta( $post_id, 'wl_provincia', isset( $_POST['wl_provincia'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_provincia'] ) ) : '' );
		update_post_meta( $post_id, 'wl_canton', isset( $_POST['wl_canton'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_canton'] ) ) : '' );
		update_post_meta( $post_id, 'wl_lat', isset( $_POST['wl_lat'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_lat'] ) ) : '' );
		update_post_meta( $post_id, 'wl_lng', isset( $_POST['wl_lng'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_lng'] ) ) : '' );
	}
}
