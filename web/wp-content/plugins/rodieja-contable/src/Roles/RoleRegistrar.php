<?php
/**
 * Native WP roles/capabilities for Rodieja.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Roles;

final class RoleRegistrar {

	public const CAP_MANAGE = 'manage_rodieja_contable';
	public const ROLE_CONTADOR = 'rodieja_contador';

	/** @return list<string> */
	public static function caps(): array {
		return array(
			self::CAP_MANAGE,
			'edit_rodieja_vehiculos',
			'edit_others_rodieja_vehiculos',
			'publish_rodieja_vehiculos',
			'read_private_rodieja_vehiculos',
			'delete_rodieja_vehiculos',
			'edit_rodieja_transacciones',
			'edit_others_rodieja_transacciones',
			'publish_rodieja_transacciones',
			'read_private_rodieja_transacciones',
			'delete_rodieja_transacciones',
			'edit_rodieja_repuestos',
			'publish_rodieja_repuestos',
			'delete_rodieja_repuestos',
			'read',
		);
	}

	public static function install_roles(): void {
		$caps = array_fill_keys( self::caps(), true );

		$admin = get_role( 'administrator' );
		if ( $admin instanceof \WP_Role ) {
			foreach ( array_keys( $caps ) as $cap ) {
				$admin->add_cap( $cap );
			}
		}

		if ( ! get_role( self::ROLE_CONTADOR ) ) {
			add_role(
				self::ROLE_CONTADOR,
				__( 'Contador Rodieja', 'rodieja-contable' ),
				$caps
			);
		} else {
			$role = get_role( self::ROLE_CONTADOR );
			if ( $role instanceof \WP_Role ) {
				foreach ( array_keys( $caps ) as $cap ) {
					$role->add_cap( $cap );
				}
			}
		}
	}

	public function register(): void {
		// Ensure caps stay on admin after upgrades.
		add_action( 'admin_init', array( self::class, 'install_roles' ) );
	}
}
