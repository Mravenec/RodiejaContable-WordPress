<?php
/**
 * Documents native roles: administrator + workshop_accountant (Read-Only Config → Roles).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Roles\RoleRegistrar;
use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;

final class RolesDocumentationPage {

	public const SLUG = 'wl-roles';

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		// Rendered via AdminMenu · Settings.
	}

	public function render(): void {
		if ( ! current_user_can( CapabilityGate::MANAGE ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}

		$contador = get_role( RoleRegistrar::ROLE_CONTADOR );
		$admin    = get_role( 'administrator' );
		?>
		<div class="wrap wl-admin-wrap">
			<div class="wl-page-header">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=workshop-ledger' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'workshop-ledger' ) ); ?></a>
						· <a href="<?php echo esc_url( admin_url( 'admin.php?page=wl-settings' ) ); ?>"><?php echo $this->escaper->html( __( 'Settings', 'workshop-ledger' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Roles', 'workshop-ledger' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Roles', 'workshop-ledger' ) ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( __( 'Native WP roles via RoleRegistrar — assign users under Users (Read-Only Config → Roles parity).', 'workshop-ledger' ) ); ?></p>
				</div>
				<div class="wl-page-header__actions">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>"><?php echo $this->escaper->html( __( 'Manage users', 'workshop-ledger' ) ); ?></a>
				</div>
			</div>

			<div class="wl-shell-grid">
				<div class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'administrator', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint">
						<?php
						echo $this->escaper->html(
							$admin && $admin->has_cap( RoleRegistrar::CAP_MANAGE )
								? __( 'Has manage_workshop_ledger (activation / maybe_upgrade_caps).', 'workshop-ledger' )
								: __( 'manage_workshop_ledger not detected on administrator.', 'workshop-ledger' )
						);
						?>
					</p>
				</div>
				<div class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><code><?php echo $this->escaper->html( RoleRegistrar::ROLE_CONTADOR ); ?></code></h2>
					<p class="wl-shell-panel__hint">
						<?php
						echo $this->escaper->html(
							$contador
								? sprintf(
									/* translators: %d: capability count */
									__( 'Role present with %d CPT capabilities + manage_workshop_ledger.', 'workshop-ledger' ),
									count( $contador->capabilities )
								)
								: __( 'Role missing — created on register() if absent.', 'workshop-ledger' )
						);
						?>
					</p>
					<p><strong><?php echo $this->escaper->html( __( 'Central capability', 'workshop-ledger' ) ); ?></strong></p>
					<code><?php echo $this->escaper->html( RoleRegistrar::CAP_MANAGE ); ?></code>
					<p class="description"><?php echo $this->escaper->html( __( 'History, reports, and profile links use this capability.', 'workshop-ledger' ) ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}
}
