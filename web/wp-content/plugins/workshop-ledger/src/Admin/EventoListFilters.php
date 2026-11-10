<?php
/**
 * Admin list filters for historial (origen CPT).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\PostTypes\DomainPostTypes;
use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;

final class EventoListFilters {

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		add_action( 'restrict_manage_posts', array( $this, 'dropdown' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_query' ) );
		add_filter( 'manage_' . DomainPostTypes::EVENTO . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . DomainPostTypes::EVENTO . '_posts_custom_column', array( $this, 'column' ), 10, 2 );
	}

	public function dropdown( string $post_type ): void {
		if ( DomainPostTypes::EVENTO !== $post_type || ! current_user_can( CapabilityGate::MANAGE ) ) {
			return;
		}

		$current = isset( $_GET['wl_origen_cpt'] ) ? sanitize_key( wp_unslash( (string) $_GET['wl_origen_cpt'] ) ) : '';
		echo '<select name="wl_origen_cpt"><option value="">' . esc_html__( 'All sources', 'workshop-ledger' ) . '</option>';
		foreach ( DomainPostTypes::historial_tracked() as $cpt ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $cpt ),
				selected( $current, $cpt, false ),
				esc_html( $cpt )
			);
		}
		echo '</select>';
	}

	public function filter_query( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || DomainPostTypes::EVENTO !== $screen->post_type ) {
			return;
		}
		if ( empty( $_GET['wl_origen_cpt'] ) ) {
			return;
		}
		$cpt = sanitize_key( wp_unslash( (string) $_GET['wl_origen_cpt'] ) );
		if ( ! in_array( $cpt, DomainPostTypes::historial_tracked(), true ) ) {
			return;
		}
		$query->set(
			'meta_query',
			array(
				array(
					'key'   => 'origen_cpt',
					'value' => $cpt,
				),
			)
		);
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function columns( array $cols ): array {
		$cols['wl_origen'] = __( 'Origen', 'workshop-ledger' );
		$cols['wl_accion'] = __( 'Action', 'workshop-ledger' );
		$cols['wl_user']   = __( 'User', 'workshop-ledger' );
		return $cols;
	}

	public function column( string $col, int $post_id ): void {
		if ( 'wl_origen' === $col ) {
			$cpt = (string) get_post_meta( $post_id, 'origen_cpt', true );
			$id  = (int) get_post_meta( $post_id, 'origen_id', true );
			echo $this->escaper->html( $cpt . ( $id ? " #{$id}" : '' ) );
			return;
		}
		if ( 'wl_accion' === $col ) {
			echo $this->escaper->html( (string) get_post_meta( $post_id, 'accion', true ) );
			return;
		}
		if ( 'wl_user' === $col ) {
			$uid = (int) get_post_meta( $post_id, 'user_id', true );
			$user = $uid ? get_userdata( $uid ) : false;
			echo $this->escaper->html( $user instanceof \WP_User ? $user->user_login : '—' );
		}
	}
}
