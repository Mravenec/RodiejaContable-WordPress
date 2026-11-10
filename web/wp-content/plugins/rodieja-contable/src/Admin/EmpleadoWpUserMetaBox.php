<?php
/**
 * Link empleado CPT to WP user (rodieja_user_id).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;

final class EmpleadoWpUserMetaBox {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'box' ) );
		add_action( 'save_post_rodieja_empleado', array( $this, 'save' ), 10, 2 );
	}

	public function box(): void {
		add_meta_box(
			'rodieja_empleado_user',
			__( 'WordPress user', 'rodieja-contable' ),
			array( $this, 'render' ),
			'rodieja_empleado',
			'side',
			'default'
		);
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_empleado_user_meta' );
		$selected = (int) get_post_meta( $post->ID, 'rodieja_user_id', true );
		echo '<select name="rodieja_user_id" class="widefat"><option value="0">—</option>';
		foreach ( get_users( array( 'orderby' => 'display_name', 'number' => 200 ) ) as $user ) {
			printf(
				'<option value="%1$d" %2$s>%3$s (%4$s)</option>',
				(int) $user->ID,
				selected( $selected, (int) $user->ID, false ),
				esc_html( $user->display_name ),
				esc_html( $user->user_login )
			);
		}
		echo '</select>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || empty( $_POST['_rodieja_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_rodieja_nonce'] ) ), 'rodieja_empleado_user_meta' ) ) {
			return;
		}
		$uid = isset( $_POST['rodieja_user_id'] ) ? absint( wp_unslash( $_POST['rodieja_user_id'] ) ) : 0;
		update_post_meta( $post_id, 'rodieja_user_id', $uid );
	}
}
