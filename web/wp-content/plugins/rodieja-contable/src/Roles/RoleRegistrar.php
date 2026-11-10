<?php
/**
 * Native WP roles/capabilities for Rodieja.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Roles;

final class RoleRegistrar {

	public const ROLE_CONTADOR = 'rodieja_contador';
	public const CAP_MANAGE    = 'manage_rodieja_contable';

	/** @return list<string> */
	public function post_types(): array {
		return array(
			'rodieja_marca',
			'rodieja_modelo',
			'rodieja_generacion',
			'rodieja_vehiculo',
			'rodieja_repuesto',
			'rodieja_empleado',
			'rodieja_transaccion',
			'rodieja_pedido',
		);
	}

	/** @return list<string> */
	public function post_caps(): array {
		$caps = array( self::CAP_MANAGE, 'read' );
		foreach ( $this->post_types() as $type ) {
			$caps[] = "edit_{$type}";
			$caps[] = "read_{$type}";
			$caps[] = "delete_{$type}";
			$caps[] = "edit_{$type}s";
			$caps[] = "edit_others_{$type}s";
			$caps[] = "publish_{$type}s";
			$caps[] = "read_private_{$type}s";
			$caps[] = "delete_{$type}s";
			$caps[] = "delete_others_{$type}s";
			$caps[] = "delete_private_{$type}s";
			$caps[] = "edit_private_{$type}s";
		}
		return array_values( array_unique( $caps ) );
	}

	public function install(): void {
		$caps = array_fill_keys( $this->post_caps(), true );
		remove_role( self::ROLE_CONTADOR );
		add_role( self::ROLE_CONTADOR, __( 'Rodieja Contador', 'rodieja-contable' ), $caps );
		$this->sync_caps();
	}

	/** Keep administrator + contador caps aligned after CPT growth. */
	public function sync_caps(): void {
		$caps = $this->post_caps();
		$admin = get_role( 'administrator' );
		if ( $admin instanceof \WP_Role ) {
			foreach ( $caps as $cap ) {
				$admin->add_cap( $cap );
			}
		}
		$contador = get_role( self::ROLE_CONTADOR );
		if ( $contador instanceof \WP_Role ) {
			foreach ( $caps as $cap ) {
				$contador->add_cap( $cap );
			}
		}
	}

	public function register(): void {
		if ( ! get_role( self::ROLE_CONTADOR ) ) {
			$this->install();
			return;
		}
		$this->sync_caps();
	}
}
