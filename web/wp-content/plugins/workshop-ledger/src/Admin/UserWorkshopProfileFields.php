<?php
/**
 * User profile link back to empleado CPT.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;

final class UserWorkshopProfileFields {

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		add_action( 'show_user_profile', array( $this, 'render' ) );
		add_action( 'edit_user_profile', array( $this, 'render' ) );
		add_action( 'personal_options_update', array( $this, 'save' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save' ) );
	}

	public function render( \WP_User $user ): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$linked = $this->find_empleado_for_user( (int) $user->ID );
		$selected = $linked ? (int) $linked->ID : 0;
		?>
		<h2><?php esc_html_e( 'Workshop Ledger', 'workshop-ledger' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="wl_empleado_id"><?php esc_html_e( 'Employee vinculado', 'workshop-ledger' ); ?></label></th>
				<td>
					<select name="wl_empleado_id" id="wl_empleado_id">
						<option value="0">—</option>
						<?php
						foreach ( get_posts(
							array(
								'post_type'      => 'wl_empleado',
								'post_status'    => 'publish',
								'posts_per_page' => 200,
								'orderby'        => 'title',
								'order'          => 'ASC',
							)
						) as $empleado ) :
							?>
							<option value="<?php echo (int) $empleado->ID; ?>" <?php selected( $selected, (int) $empleado->ID ); ?>>
								<?php echo esc_html( get_the_title( $empleado ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Actualiza meta wl_user_id en el empleado seleccionado.', 'workshop-ledger' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function save( int $user_id ): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['wl_empleado_id'] ) ) {
			return;
		}
		$empleado_id = absint( wp_unslash( $_POST['wl_empleado_id'] ) );

		$this->clear_user_from_empleados( $user_id );

		if ( $empleado_id > 0 && 'wl_empleado' === get_post_type( $empleado_id ) ) {
			update_post_meta( $empleado_id, 'wl_user_id', $user_id );
		}
	}

	private function find_empleado_for_user( int $user_id ): ?\WP_Post {
		$q = new \WP_Query(
			array(
				'post_type'      => 'wl_empleado',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => 'wl_user_id',
						'value' => $user_id,
					),
				),
			)
		);
		return $q->posts[0] ?? null;
	}

	private function clear_user_from_empleados( int $user_id ): void {
		$q = new \WP_Query(
			array(
				'post_type'      => 'wl_empleado',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => 'wl_user_id',
						'value' => $user_id,
					),
				),
			)
		);
		foreach ( $q->posts as $id ) {
			delete_post_meta( (int) $id, 'wl_user_id' );
		}
	}
}
