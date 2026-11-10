<?php
/**
 * Employee CPT: WP user link + contact fields (email, phone, role).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;

final class EmpleadoWpUserMetaBox {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'box' ) );
		add_action( 'save_post_wl_empleado', array( $this, 'save' ), 10, 2 );
	}

	public function box(): void {
		add_meta_box(
			'wl_empleado_user',
			__( 'Employee · Contact', 'workshop-ledger' ),
			array( $this, 'render' ),
			'wl_empleado',
			'normal',
			'high'
		);
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_empleado_user_meta' );
		$selected = (int) get_post_meta( $post->ID, 'wl_user_id', true );
		$email    = (string) get_post_meta( $post->ID, 'wl_email', true );
		$tel      = (string) get_post_meta( $post->ID, 'wl_telefono', true );
		$puesto   = (string) get_post_meta( $post->ID, 'wl_puesto', true );

		echo '<p class="description">' . $this->escaper->html( __( 'Contact fields like Read-Only employee create — native post meta. Optional link to a WordPress user.', 'workshop-ledger' ) ) . '</p>';
		echo '<div class="wl-part-form__grid">';
		echo '<p><label for="wl_email">' . $this->escaper->html( __( 'Email', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="email" class="regular-text" name="wl_email" id="wl_email" value="' . $this->escaper->attr( $email ) . '" /></p>';
		echo '<p><label for="wl_telefono">' . $this->escaper->html( __( 'Phone', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="text" class="regular-text" name="wl_telefono" id="wl_telefono" value="' . $this->escaper->attr( $tel ) . '" /></p>';
		echo '<p><label for="wl_puesto">' . $this->escaper->html( __( 'Role / position', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="text" class="regular-text" name="wl_puesto" id="wl_puesto" value="' . $this->escaper->attr( $puesto ) . '" /></p>';
		echo '<p><label for="wl_user_id">' . $this->escaper->html( __( 'WordPress user', 'workshop-ledger' ) ) . '</label><br />';
		echo '<select name="wl_user_id" id="wl_user_id" class="regular-text"><option value="0">—</option>';
		foreach ( get_users( array( 'orderby' => 'display_name', 'number' => 200 ) ) as $user ) {
			printf(
				'<option value="%1$d" %2$s>%3$s (%4$s)</option>',
				(int) $user->ID,
				selected( $selected, (int) $user->ID, false ),
				esc_html( $user->display_name ),
				esc_html( $user->user_login )
			);
		}
		echo '</select></p></div>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || ! $this->nonce->verify_post( 'wl_empleado_user_meta' ) ) {
			return;
		}
		$uid = isset( $_POST['wl_user_id'] ) ? absint( wp_unslash( $_POST['wl_user_id'] ) ) : 0;
		update_post_meta( $post_id, 'wl_user_id', $uid );
		update_post_meta( $post_id, 'wl_email', isset( $_POST['wl_email'] ) ? sanitize_email( wp_unslash( (string) $_POST['wl_email'] ) ) : '' );
		update_post_meta( $post_id, 'wl_telefono', isset( $_POST['wl_telefono'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_telefono'] ) ) : '' );
		update_post_meta( $post_id, 'wl_puesto', isset( $_POST['wl_puesto'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['wl_puesto'] ) ) : '' );
	}
}
