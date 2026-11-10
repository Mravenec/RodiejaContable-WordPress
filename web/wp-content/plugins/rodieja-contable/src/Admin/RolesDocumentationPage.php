<?php
/**
 * Documents native roles: administrator + rodieja_contador (Read-Only Config → Roles).
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
		// Rendered via AdminMenu · Settings.
	}

	public function render(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}

		$contador = get_role( RoleRegistrar::ROLE_CONTADOR );
		$admin    = get_role( 'administrator' );
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-page-header">
				<div class="rodieja-page-header__main">
					<p class="rodieja-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-contable' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'rodieja-contable' ) ); ?></a>
						· <a href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-settings' ) ); ?>"><?php echo $this->escaper->html( __( 'Settings', 'rodieja-contable' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Roles', 'rodieja-contable' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Roles', 'rodieja-contable' ) ); ?></h1>
					<p class="rodieja-page-header__lede"><?php echo $this->escaper->html( __( 'Native WP roles via RoleRegistrar — assign users under Users (Read-Only Config → Roles parity).', 'rodieja-contable' ) ); ?></p>
				</div>
				<div class="rodieja-page-header__actions">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>"><?php echo $this->escaper->html( __( 'Manage users', 'rodieja-contable' ) ); ?></a>
				</div>
			</div>

			<div class="rodieja-shell-grid">
				<div class="rodieja-shell-panel">
					<h2 class="rodieja-shell-panel__title"><?php echo $this->escaper->html( __( 'administrator', 'rodieja-contable' ) ); ?></h2>
					<p class="rodieja-shell-panel__hint">
						<?php
						echo $this->escaper->html(
							$admin && $admin->has_cap( RoleRegistrar::CAP_MANAGE )
								? __( 'Has manage_rodieja_contable (activation / maybe_upgrade_caps).', 'rodieja-contable' )
								: __( 'manage_rodieja_contable not detected on administrator.', 'rodieja-contable' )
						);
						?>
					</p>
				</div>
				<div class="rodieja-shell-panel">
					<h2 class="rodieja-shell-panel__title"><code><?php echo $this->escaper->html( RoleRegistrar::ROLE_CONTADOR ); ?></code></h2>
					<p class="rodieja-shell-panel__hint">
						<?php
						echo $this->escaper->html(
							$contador
								? sprintf(
									/* translators: %d: capability count */
									__( 'Role present with %d CPT capabilities + manage_rodieja_contable.', 'rodieja-contable' ),
									count( $contador->capabilities )
								)
								: __( 'Role missing — created on register() if absent.', 'rodieja-contable' )
						);
						?>
					</p>
					<p><strong><?php echo $this->escaper->html( __( 'Central capability', 'rodieja-contable' ) ); ?></strong></p>
					<code><?php echo $this->escaper->html( RoleRegistrar::CAP_MANAGE ); ?></code>
					<p class="description"><?php echo $this->escaper->html( __( 'History, reports, and profile links use this capability.', 'rodieja-contable' ) ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}
