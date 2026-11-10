<?php
/**
 * Documents native roles: administrator + rodieja_contador.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Roles\RoleRegistrar;
use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;

final class RolesDocumentationPage {

	public const SLUG = 'rodieja-roles';

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		// Rendered via AdminMenu.
	}

	public function render(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}

		$contador = get_role( RoleRegistrar::ROLE_CONTADOR );
		$admin    = get_role( 'administrator' );
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Roles Rodieja', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Registro nativo vía RoleRegistrar en activación.', 'rodieja-contable' ) ); ?></p>

			<h2><?php echo $this->escaper->html( __( 'administrator', 'rodieja-contable' ) ); ?></h2>
			<p>
				<?php
				echo $this->escaper->html(
					$admin && $admin->has_cap( RoleRegistrar::CAP_MANAGE )
						? __( 'Tiene manage_rodieja_contable (vía activación / maybe_upgrade_caps).', 'rodieja-contable' )
						: __( 'Sin manage_rodieja_contable detectado.', 'rodieja-contable' )
				);
				?>
			</p>

			<h2><?php echo $this->escaper->html( RoleRegistrar::ROLE_CONTADOR ); ?></h2>
			<p>
				<?php
				echo $this->escaper->html(
					$contador
						? sprintf(
							/* translators: %d: capability count */
							__( 'Rol presente con %d capacidades CPT + manage_rodieja_contable.', 'rodieja-contable' ),
							count( $contador->capabilities )
						)
						: __( 'Rol no instalado — se crea en register() si falta.', 'rodieja-contable' )
				);
				?>
			</p>

			<h3><?php echo $this->escaper->html( __( 'Capacidad central', 'rodieja-contable' ) ); ?></h3>
			<code><?php echo $this->escaper->html( RoleRegistrar::CAP_MANAGE ); ?></code>
			<p class="description"><?php esc_html_e( 'Historial (rodieja_evento), reportes y enlaces de perfil usan esta capacidad.', 'rodieja-contable' ); ?></p>
		</div>
		<?php
	}
}
