<?php
/**
 * User profile link back to empleado CPT.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;

final class UserRodiejaProfileFields {

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
		<h2><?php esc_html_e( 'Rodieja Contable', 'rodieja-contable' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="rodieja_empleado_id"><?php esc_html_e( 'Empleado vinculado', 'rodieja-contable' ); ?></label></th>
				<td>
					<select name="rodieja_empleado_id" id="rodieja_empleado_id">
						<option value="0">—</option>
						<?php
						foreach ( get_posts(
							array(
								'post_type'      => 'rodieja_empleado',
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
					<p class="description"><?php esc_html_e( 'Actualiza meta rodieja_user_id en el empleado seleccionado.', 'rodieja-contable' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function save( int $user_id ): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['rodieja_empleado_id'] ) ) {
			return;
		}
		$empleado_id = absint( wp_unslash( $_POST['rodieja_empleado_id'] ) );

		$this->clear_user_from_empleados( $user_id );

		if ( $empleado_id > 0 && 'rodieja_empleado' === get_post_type( $empleado_id ) ) {
			update_post_meta( $empleado_id, 'rodieja_user_id', $user_id );
		}
	}

	private function find_empleado_for_user( int $user_id ): ?\WP_Post {
		$q = new \WP_Query(
			array(
				'post_type'      => 'rodieja_empleado',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => 'rodieja_user_id',
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
				'post_type'      => 'rodieja_empleado',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => 'rodieja_user_id',
						'value' => $user_id,
					),
				),
			)
		);
		foreach ( $q->posts as $id ) {
			delete_post_meta( (int) $id, 'rodieja_user_id' );
		}
	}
}
