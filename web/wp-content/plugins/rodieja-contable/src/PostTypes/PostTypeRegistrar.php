<?php
/**
 * Native CPT registration — maps sistema_vehicular entities to posts.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\PostTypes;

final class PostTypeRegistrar {

	public function register(): void {
		add_action( 'init', array( $this, 'register_types' ) );
	}

	public function register_types(): void {
		$this->cpt(
			'rodieja_marca',
			array(
				'label' => __( 'Marcas', 'rodieja-contable' ),
				'labels' => $this->labels( __( 'Marca', 'rodieja-contable' ), __( 'Marcas', 'rodieja-contable' ) ),
				'menu_icon' => 'dashicons-tag',
				'menu_position' => 26,
			)
		);
		$this->cpt(
			'rodieja_modelo',
			array(
				'label' => __( 'Modelos', 'rodieja-contable' ),
				'labels' => $this->labels( __( 'Modelo', 'rodieja-contable' ), __( 'Modelos', 'rodieja-contable' ) ),
				'menu_icon' => 'dashicons-tag',
				'menu_position' => 27,
			)
		);
		$this->cpt(
			'rodieja_generacion',
			array(
				'label' => __( 'Generaciones', 'rodieja-contable' ),
				'labels' => $this->labels( __( 'Generación', 'rodieja-contable' ), __( 'Generaciones', 'rodieja-contable' ) ),
				'menu_icon' => 'dashicons-backup',
				'menu_position' => 28,
			)
		);
		$this->cpt(
			'rodieja_vehiculo',
			array(
				'label' => __( 'Vehículos', 'rodieja-contable' ),
				'labels' => $this->labels( __( 'Vehículo', 'rodieja-contable' ), __( 'Vehículos', 'rodieja-contable' ) ),
				'menu_icon' => 'dashicons-car',
				'menu_position' => 25,
				'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			)
		);
		$this->cpt(
			'rodieja_repuesto',
			array(
				'label' => __( 'Repuestos', 'rodieja-contable' ),
				'labels' => $this->labels( __( 'Repuesto', 'rodieja-contable' ), __( 'Repuestos', 'rodieja-contable' ) ),
				'menu_icon' => 'dashicons-admin-tools',
				'menu_position' => 29,
			)
		);
		$this->cpt(
			'rodieja_empleado',
			array(
				'label' => __( 'Empleados', 'rodieja-contable' ),
				'labels' => $this->labels( __( 'Empleado', 'rodieja-contable' ), __( 'Empleados', 'rodieja-contable' ) ),
				'menu_icon' => 'dashicons-groups',
				'menu_position' => 30,
			)
		);
		$this->cpt(
			'rodieja_transaccion',
			array(
				'label' => __( 'Transacciones', 'rodieja-contable' ),
				'labels' => $this->labels( __( 'Transacción', 'rodieja-contable' ), __( 'Transacciones', 'rodieja-contable' ) ),
				'menu_icon' => 'dashicons-money-alt',
				'menu_position' => 31,
			)
		);
		$this->cpt(
			'rodieja_pedido',
			array(
				'label' => __( 'Pedidos', 'rodieja-contable' ),
				'labels' => $this->labels( __( 'Pedido', 'rodieja-contable' ), __( 'Pedidos', 'rodieja-contable' ) ),
				'menu_icon' => 'dashicons-cart',
				'menu_position' => 32,
			)
		);
	}

	/**
	 * @param array<string, mixed> $args
	 */
	private function cpt( string $type, array $args ): void {
		$defaults = array(
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'rest_base'           => $type,
			'has_archive'         => false,
			'hierarchical'        => false,
			'capability_type'     => $type,
			'map_meta_cap'        => true,
			'supports'            => array( 'title', 'custom-fields' ),
			'delete_with_user'    => false,
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
