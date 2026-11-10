<?php
/**
 * Native WP roles/capabilities for Rodieja.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Roles;

final class RoleRegistrar {

	/** Contador operativo — CPT caps + {@see self::CAP_MANAGE}. */
	public const ROLE_CONTADOR = 'rodieja_contador';

	/** Hub, historial, reportes, perfil Rodieja (administrator también la recibe en install). */
	public const CAP_MANAGE    = 'manage_rodieja_contable';

	/** @return list<string> */
	public function post_caps(): array {
		$types = array(
			'rodieja_marca',
			'rodieja_modelo',
			'rodieja_generacion',
			'rodieja_vehiculo',
			'rodieja_repuesto',
			'rodieja_empleado',
			'rodieja_transaccion',
			'rodieja_pedido',
			'rodieja_pedido_item',
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
		add_role( self::ROLE_CONTADOR, __( 'Rodieja Contador', 'rodieja-contable' ), $caps );

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
