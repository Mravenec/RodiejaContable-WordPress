<?php
/**
 * Native media gallery meta (attachment IDs) for vehículo and repuesto.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;

final class GalleryMetaBox {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'boxes' ) );
		add_action( 'save_post_rodieja_vehiculo', array( $this, 'save' ), 10, 2 );
		add_action( 'save_post_rodieja_repuesto', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	public function boxes(): void {
		foreach ( array( 'rodieja_vehiculo', 'rodieja_repuesto' ) as $type ) {
			add_meta_box(
				'rodieja_gallery',
				__( 'Gallery (media)', 'rodieja-contable' ),
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
		if ( ! $screen || ! in_array( $screen->post_type, array( 'rodieja_vehiculo', 'rodieja_repuesto' ), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_add_inline_script(
			'jquery',
			'(function($){$(document).on("click",".rodieja-pick-gallery",function(e){e.preventDefault();var t=$("#rodieja_gallery_ids");var f=wp.media({multiple:true,library:{type:"image"}});f.on("select",function(){var ids=f.state().get("selection").map(function(a){return a.id;});t.val(ids.join(","));});f.open();});})(jQuery);'
		);
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_gallery_meta' );
		$raw = get_post_meta( $post->ID, 'rodieja_gallery_ids', true );
		$val = is_array( $raw ) ? implode( ',', array_map( 'intval', $raw ) ) : (string) $raw;
		echo '<p><input type="text" class="widefat" id="rodieja_gallery_ids" name="rodieja_gallery_ids" value="' . $this->escaper->attr( $val ) . '" placeholder="1,2,3" /></p>';
		echo '<p><button type="button" class="button rodieja-pick-gallery">' . esc_html__( 'Choose images', 'rodieja-contable' ) . '</button></p>';
		echo '<p class="description">' . esc_html__( 'IDs de adjuntos WP (separados por coma). Imagen destacada sigue siendo la miniatura nativa.', 'rodieja-contable' ) . '</p>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || empty( $_POST['_rodieja_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_rodieja_nonce'] ) ), 'rodieja_gallery_meta' ) ) {
			return;
		}
		if ( ! isset( $_POST['rodieja_gallery_ids'] ) ) {
			return;
		}
		$parts = array_filter( array_map( 'absint', explode( ',', wp_unslash( (string) $_POST['rodieja_gallery_ids'] ) ) ) );
		update_post_meta( $post_id, 'rodieja_gallery_ids', array_values( $parts ) );
	}
}
