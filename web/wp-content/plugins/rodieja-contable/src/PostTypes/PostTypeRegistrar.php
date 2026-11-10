<?php
/**
 * Native CPT registration — maps sistema_vehicular entities to posts.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\PostTypes;

final class PostTypeRegistrar {

	public const MENU_PARENT = 'rodieja-contable';

	public function register(): void {
		add_action( 'init', array( $this, 'register_types' ) );
	}

	public function register_types(): void {
		// Menus are owned by AdminMenu (React-shaped top-levels) — hide CPT auto-menus.
		$hidden = array( 'show_in_menu' => false );

		$this->cpt(
			'rodieja_marca',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Brands', 'rodieja-contable' ),
					'labels' => $this->labels( __( 'Brand', 'rodieja-contable' ), __( 'Brands', 'rodieja-contable' ) ),
				)
			)
		);
		$this->cpt(
			'rodieja_modelo',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Models', 'rodieja-contable' ),
					'labels' => $this->labels( __( 'Model', 'rodieja-contable' ), __( 'Models', 'rodieja-contable' ) ),
				)
			)
		);
		$this->cpt(
			'rodieja_generacion',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Generations', 'rodieja-contable' ),
					'labels' => $this->labels( __( 'Generation', 'rodieja-contable' ), __( 'Generations', 'rodieja-contable' ) ),
				)
			)
		);
		$this->cpt(
			'rodieja_vehiculo',
			array_merge(
				$hidden,
				array(
					'label'    => __( 'Vehicles', 'rodieja-contable' ),
					'labels'   => $this->labels( __( 'Vehicle', 'rodieja-contable' ), __( 'Vehicles', 'rodieja-contable' ) ),
					'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
				)
			)
		);
		$this->cpt(
			'rodieja_repuesto',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Parts', 'rodieja-contable' ),
					'labels' => $this->labels( __( 'Part', 'rodieja-contable' ), __( 'Parts', 'rodieja-contable' ) ),
				)
			)
		);
		$this->cpt(
			'rodieja_empleado',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Employees', 'rodieja-contable' ),
					'labels' => $this->labels( __( 'Employee', 'rodieja-contable' ), __( 'Employees', 'rodieja-contable' ) ),
				)
			)
		);
		$this->cpt(
			'rodieja_transaccion',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Transactions', 'rodieja-contable' ),
					'labels' => $this->labels( __( 'Transaction', 'rodieja-contable' ), __( 'Transactions', 'rodieja-contable' ) ),
				)
			)
		);
		$this->cpt(
			'rodieja_pedido',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Orders', 'rodieja-contable' ),
					'labels' => $this->labels( __( 'Order', 'rodieja-contable' ), __( 'Orders', 'rodieja-contable' ) ),
				)
			)
		);
		$this->cpt(
			'rodieja_pedido_item',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Order items', 'rodieja-contable' ),
					'labels' => $this->labels( __( 'Order item', 'rodieja-contable' ), __( 'Order items', 'rodieja-contable' ) ),
				)
			)
		);
	}

	/**
	 * @param array<string, mixed> $args
	 */
	private function cpt( string $type, array $args ): void {
		$defaults = array(
			'public'           => false,
			'show_ui'          => true,
			'show_in_menu'     => false,
			'show_in_rest'     => true,
			'rest_base'        => $type,
			'has_archive'      => false,
			'hierarchical'     => false,
			'capability_type'  => $type,
			'map_meta_cap'     => true,
			'supports'         => array( 'title', 'custom-fields' ),
			'delete_with_user' => false,
		);
		register_post_type( $type, array_merge( $defaults, $args ) );
	}

	/**
	 * @return array<string, string>
	 */
	private function labels( string $one, string $many ): array {
		return array(
			'name'          => $many,
			'singular_name' => $one,
			'add_new_item'  => sprintf( __( 'Add %s', 'rodieja-contable' ), $one ),
			'edit_item'     => sprintf( __( 'Edit %s', 'rodieja-contable' ), $one ),
			'search_items'  => sprintf( __( 'Search %s', 'rodieja-contable' ), $many ),
			'not_found'     => sprintf( __( 'No %s found', 'rodieja-contable' ), $many ),
			'menu_name'     => $many,
		);
	}
}
