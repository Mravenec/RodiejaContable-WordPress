<?php
/**
 * Native WP roles/capabilities for WorkshopLedger.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Roles;

final class RoleRegistrar {

	/** Contador operativo — CPT caps + {@see self::CAP_MANAGE}. */
	public const ROLE_CONTADOR = 'workshop_accountant';

	/** Hub, historial, reportes, perfil WorkshopLedger (administrator también la recibe en install). */
	public const CAP_MANAGE    = 'manage_workshop_ledger';

	/** @return list<string> */
	public function post_caps(): array {
		$types = array(
			'wl_marca',
			'wl_modelo',
			'wl_generacion',
			'wl_vehiculo',
			'wl_repuesto',
			'wl_empleado',
			'wl_transaccion',
			'wl_pedido',
			'wl_pedido_item',
			'wl_evento',
		);
		$caps  = array( self::CAP_MANAGE, 'read' );
		foreach ( $types as $type ) {
			$caps[] = "edit_{$type}";
			$caps[] = "read_{$type}";
			$caps[] = "delete_{$type}";
			$caps[] = "edit_{$type}s";
			$caps[] = "edit_others_{$type}s";
			$caps[] = "publish_{$type}s";
			$caps[] = "read_private_{$type}s";
			$caps[] = "delete_{$type}s";
			$caps[] = "delete_others_{$type}s";
		}
		return array_values( array_unique( $caps ) );
	}

	public function install(): void {
		$caps = array_fill_keys( $this->post_caps(), true );
		add_role( self::ROLE_CONTADOR, __( 'WorkshopLedger Accountant', 'workshop-ledger' ), $caps );

		$admin = get_role( 'administrator' );
		if ( $admin instanceof \WP_Role ) {
			foreach ( array_keys( $caps ) as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	public function register(): void {
		// Ensure role exists after upgrades.
		if ( ! get_role( self::ROLE_CONTADOR ) ) {
			$this->install();
		}
	}
}
