<?php
/**
 * One-time migrate Rodieja Contable identifiers → Workshop Ledger (native $wpdb).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Core;

use WorkshopLedger\Roles\RoleRegistrar;

final class BrandMigrate {

	public const OPTION_FLAG = 'wl_brand_migrated_v1';

	/** @var array<string, string> */
	private const POST_TYPES = array(
		'rodieja_marca'        => 'wl_marca',
		'rodieja_modelo'       => 'wl_modelo',
		'rodieja_generacion'   => 'wl_generacion',
		'rodieja_vehiculo'     => 'wl_vehiculo',
		'rodieja_repuesto'     => 'wl_repuesto',
		'rodieja_empleado'     => 'wl_empleado',
		'rodieja_transaccion'  => 'wl_transaccion',
		'rodieja_pedido'       => 'wl_pedido',
		'rodieja_pedido_item'  => 'wl_pedido_item',
		'rodieja_evento'       => 'wl_evento',
	);

	/** @var array<string, string> */
	private const TAXONOMIES = array(
		'rodieja_tipo_tx' => 'wl_tipo_tx',
		'rodieja_parte'   => 'wl_parte',
	);

	/** @var array<string, string> */
	private const OPTIONS = array(
		'rodieja_contable_version'      => 'wl_version',
		'rodieja_contable_org_name'     => 'wl_org_name',
		'rodieja_audatex_ins_settings'  => 'wl_audatex_ins_settings',
		'rodieja_cr_cantones'           => 'wl_cr_cantones',
	);

	public static function maybe_run(): void {
		if ( get_option( self::OPTION_FLAG ) ) {
			return;
		}
		$m = new self();
		$m->migrate_post_types();
		$m->migrate_taxonomies();
		$m->migrate_meta_keys();
		$m->migrate_options();
		$m->migrate_roles();
		$m->migrate_active_plugins();
		update_option( self::OPTION_FLAG, gmdate( 'c' ), false );
	}

	private function migrate_post_types(): void {
		global $wpdb;
		foreach ( self::POST_TYPES as $from => $to ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->posts} SET post_type = %s WHERE post_type = %s",
					$to,
					$from
				)
			);
		}
	}

	private function migrate_taxonomies(): void {
		global $wpdb;
		foreach ( self::TAXONOMIES as $from => $to ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->term_taxonomy} SET taxonomy = %s WHERE taxonomy = %s",
					$to,
					$from
				)
			);
		}
	}

	private function migrate_meta_keys(): void {
		global $wpdb;
		// Rename post meta keys that still start with rodieja_
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
				$wpdb->esc_like( 'rodieja_' ) . '%'
			)
		);
		if ( ! is_array( $rows ) ) {
			return;
		}
		foreach ( $rows as $key ) {
			$key = (string) $key;
			$new = 'wl_' . substr( $key, strlen( 'rodieja_' ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} SET meta_key = %s WHERE meta_key = %s",
					$new,
					$key
				)
			);
		}

		// User meta
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$umeta = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				$wpdb->esc_like( 'rodieja_' ) . '%'
			)
		);
		if ( is_array( $umeta ) ) {
			foreach ( $umeta as $key ) {
				$key = (string) $key;
				$new = 'wl_' . substr( $key, strlen( 'rodieja_' ) );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->usermeta} SET meta_key = %s WHERE meta_key = %s",
						$new,
						$key
					)
				);
			}
		}
	}

	private function migrate_options(): void {
		foreach ( self::OPTIONS as $from => $to ) {
			$val = get_option( $from, null );
			if ( null === $val ) {
				continue;
			}
			if ( false === get_option( $to, false ) && null !== get_option( $to, null ) ) {
				// already has destination
			} else {
				update_option( $to, $val, false );
			}
			delete_option( $from );
		}

		// Catch-all remaining options with rodieja_ prefix
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$opts = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( 'rodieja_' ) . '%'
			)
		);
		if ( ! is_array( $opts ) ) {
			return;
		}
		foreach ( $opts as $name ) {
			$name = (string) $name;
			$new  = 'wl_' . substr( $name, strlen( 'rodieja_' ) );
			$val  = get_option( $name, null );
			if ( null === $val ) {
				continue;
			}
			update_option( $new, $val, false );
			delete_option( $name );
		}
	}

	private function migrate_roles(): void {
		$wp_roles = wp_roles();
		if ( $wp_roles->is_role( 'rodieja_contador' ) && ! $wp_roles->is_role( RoleRegistrar::ROLE_CONTADOR ) ) {
			$old = get_role( 'rodieja_contador' );
			if ( $old ) {
				add_role( RoleRegistrar::ROLE_CONTADOR, __( 'Workshop Accountant', 'workshop-ledger' ), $old->capabilities );
			}
		}
		// Swap capability keys on all roles
		foreach ( $wp_roles->roles as $role_key => $_info ) {
			$role = get_role( $role_key );
			if ( ! $role ) {
				continue;
			}
			if ( $role->has_cap( 'manage_rodieja_contable' ) ) {
				$role->add_cap( RoleRegistrar::CAP_MANAGE );
				$role->remove_cap( 'manage_rodieja_contable' );
			}
			foreach ( array_keys( $role->capabilities ) as $cap ) {
				if ( ! is_string( $cap ) || ! str_contains( $cap, 'rodieja_' ) ) {
					continue;
				}
				$new = str_replace( 'rodieja_', 'wl_', $cap );
				$role->add_cap( $new );
				$role->remove_cap( $cap );
			}
		}
		if ( $wp_roles->is_role( 'rodieja_contador' ) ) {
			remove_role( 'rodieja_contador' );
		}
		( new RoleRegistrar() )->install();
	}

	private function migrate_active_plugins(): void {
		$plugins = get_option( 'active_plugins', array() );
		if ( ! is_array( $plugins ) ) {
			return;
		}
		$changed = false;
		$next    = array();
		foreach ( $plugins as $p ) {
			if ( 'rodieja-contable/rodieja-contable.php' === $p ) {
				$next[]  = 'workshop-ledger/workshop-ledger.php';
				$changed = true;
			} else {
				$next[] = $p;
			}
		}
		$next = array_values( array_unique( $next ) );
		if ( $changed ) {
			update_option( 'active_plugins', $next );
		}
	}
}
