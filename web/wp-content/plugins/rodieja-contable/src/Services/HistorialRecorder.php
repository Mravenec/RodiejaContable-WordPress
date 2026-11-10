<?php
/**
 * Records domain CPT saves as rodieja_evento posts + meta.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

use Rodieja\Contable\PostTypes\DomainPostTypes;

final class HistorialRecorder {

	private static bool $writing = false;

	public function register(): void {
		add_action( 'save_post', array( $this, 'on_save' ), 20, 3 );
		add_action( 'transition_post_status', array( $this, 'on_status' ), 10, 3 );
	}

	public function on_save( int $post_id, \WP_Post $post, bool $update ): void {
		if ( self::$writing || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( ! in_array( $post->post_type, DomainPostTypes::historial_tracked(), true ) ) {
			return;
		}
		if ( 'auto-draft' === $post->post_status || 'trash' === $post->post_status ) {
			return;
		}

		$accion = $update ? 'actualizar' : 'crear';
		$this->insert_event( $post->post_type, $post_id, $accion, $post->post_title );
	}

	public function on_status( string $new_status, string $old_status, \WP_Post $post ): void {
		unset( $old_status );
		if ( self::$writing || 'trash' !== $new_status ) {
			return;
		}
		if ( ! in_array( $post->post_type, DomainPostTypes::historial_tracked(), true ) ) {
			return;
		}
		$this->insert_event( $post->post_type, (int) $post->ID, 'papelera', $post->post_title );
	}

	private function insert_event( string $origen_cpt, int $origen_id, string $accion, string $title ): void {
		$user_id = get_current_user_id();
		$label   = sprintf(
			/* translators: 1: action, 2: CPT, 3: post ID, 4: title */
			__( '%1$s · %2$s #%3$d — %4$s', 'rodieja-contable' ),
			$accion,
			$origen_cpt,
			$origen_id,
			$title ?: __( '(sin título)', 'rodieja-contable' )
		);

		self::$writing = true;
		$event_id      = wp_insert_post(
			array(
				'post_type'   => DomainPostTypes::EVENTO,
				'post_status' => 'publish',
				'post_title'  => $label,
			),
			true
		);
		self::$writing = false;

		if ( is_wp_error( $event_id ) || ! $event_id ) {
			return;
		}

		update_post_meta( $event_id, 'origen_cpt', $origen_cpt );
		update_post_meta( $event_id, 'origen_id', $origen_id );
		update_post_meta( $event_id, 'accion', $accion );
		update_post_meta( $event_id, 'user_id', $user_id );
	}
}
