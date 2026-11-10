<?php
/**
 * Native media gallery meta (attachment IDs) for vehículo and repuesto.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;

final class GalleryMetaBox {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'boxes' ) );
		add_action( 'save_post_wl_vehiculo', array( $this, 'save' ), 10, 2 );
		add_action( 'save_post_wl_repuesto', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	public function boxes(): void {
		foreach ( array( 'wl_vehiculo', 'wl_repuesto' ) as $type ) {
			add_meta_box(
				'wl_gallery',
				__( 'Gallery (media)', 'workshop-ledger' ),
				array( $this, 'render' ),
				$type,
				'side',
				'default'
			);
		}
	}

	public function scripts( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, array( 'wl_vehiculo', 'wl_repuesto' ), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_add_inline_script(
			'jquery',
			'(function($){$(document).on("click",".wl-pick-gallery",function(e){e.preventDefault();var t=$("#wl_gallery_ids");var f=wp.media({multiple:true,library:{type:"image"}});f.on("select",function(){var ids=f.state().get("selection").map(function(a){return a.id;});t.val(ids.join(","));});f.open();});})(jQuery);'
		);
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_gallery_meta' );
		$raw = get_post_meta( $post->ID, 'wl_gallery_ids', true );
		$val = is_array( $raw ) ? implode( ',', array_map( 'intval', $raw ) ) : (string) $raw;
		echo '<p><input type="text" class="widefat" id="wl_gallery_ids" name="wl_gallery_ids" value="' . $this->escaper->attr( $val ) . '" placeholder="1,2,3" /></p>';
		echo '<p><button type="button" class="button wl-pick-gallery">' . esc_html__( 'Choose images', 'workshop-ledger' ) . '</button></p>';
		echo '<p class="description">' . esc_html__( 'IDs de adjuntos WP (separados por coma). Imagen destacada sigue siendo la miniatura nativa.', 'workshop-ledger' ) . '</p>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || ! $this->nonce->verify_post( 'wl_gallery_meta' ) ) {
			return;
		}
		if ( ! isset( $_POST['wl_gallery_ids'] ) ) {
			return;
		}
		$parts = array_filter( array_map( 'absint', explode( ',', wp_unslash( (string) $_POST['wl_gallery_ids'] ) ) ) );
		update_post_meta( $post_id, 'wl_gallery_ids', array_values( $parts ) );
	}
}
