<?php
/**
 * Native CPTs + taxonomies (show_in_rest). No custom tables.
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
		$this->register_taxonomy_tipo_transaccion();
		$this->register_cpt(
			'rodieja_marca',
			array(
				'labels'       => $this->labels( 'Marca', 'Marcas' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'rodieja-contable',
				'show_in_rest' => true,
				'rest_base'    => 'rodieja-marcas',
				'menu_icon'    => 'dashicons-tag',
				'supports'     => array( 'title', 'revisions' ),
				'capability_type' => array( 'rodieja_vehiculo', 'rodieja_vehiculos' ),
				'map_meta_cap' => true,
			)
		);
		$this->register_cpt(
			'rodieja_modelo',
			array(
				'labels'       => $this->labels( 'Modelo', 'Modelos' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'rodieja-contable',
				'show_in_rest' => true,
				'rest_base'    => 'rodieja-modelos',
				'supports'     => array( 'title', 'revisions', 'custom-fields' ),
				'capability_type' => array( 'rodieja_vehiculo', 'rodieja_vehiculos' ),
				'map_meta_cap' => true,
			)
		);
		$this->register_cpt(
			'rodieja_generacion',
			array(
				'labels'       => $this->labels( 'Generación', 'Generaciones' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'rodieja-contable',
				'show_in_rest' => true,
				'rest_base'    => 'rodieja-generaciones',
				'supports'     => array( 'title', 'editor', 'revisions', 'custom-fields' ),
				'capability_type' => array( 'rodieja_vehiculo', 'rodieja_vehiculos' ),
				'map_meta_cap' => true,
			)
		);
		$this->register_cpt(
			'rodieja_vehiculo',
			array(
				'labels'       => $this->labels( 'Vehículo', 'Vehículos' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'rodieja-contable',
				'show_in_rest' => true,
				'rest_base'    => 'rodieja-vehiculos',
				'menu_icon'    => 'dashicons-car',
				'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions', 'custom-fields' ),
				'capability_type' => array( 'rodieja_vehiculo', 'rodieja_vehiculos' ),
				'map_meta_cap' => true,
			)
		);
		$this->register_cpt(
			'rodieja_repuesto',
			array(
				'labels'       => $this->labels( 'Repuesto', 'Repuestos' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'rodieja-contable',
				'show_in_rest' => true,
				'rest_base'    => 'rodieja-repuestos',
				'supports'     => array( 'title', 'editor', 'revisions', 'custom-fields' ),
				'capability_type' => array( 'rodieja_repuesto', 'rodieja_repuestos' ),
				'map_meta_cap' => true,
			)
		);
		$this->register_cpt(
			'rodieja_transaccion',
			array(
				'labels'       => $this->labels( 'Transacción', 'Transacciones' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'rodieja-contable',
				'show_in_rest' => true,
				'rest_base'    => 'rodieja-transacciones',
				'menu_icon'    => 'dashicons-money-alt',
				'supports'     => array( 'title', 'editor', 'revisions', 'custom-fields' ),
				'taxonomies'   => array( 'rodieja_tipo_tx' ),
				'capability_type' => array( 'rodieja_transaccion', 'rodieja_transacciones' ),
				'map_meta_cap' => true,
			)
		);
	}

	private function register_taxonomy_tipo_transaccion(): void {
		register_taxonomy(
			'rodieja_tipo_tx',
			array( 'rodieja_transaccion' ),
			array(
				'labels'            => array(
					'name'          => __( 'Tipos de transacción', 'rodieja-contable' ),
					'singular_name' => __( 'Tipo de transacción', 'rodieja-contable' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'hierarchical'      => false,
				'rewrite'           => false,
			)
		);
	}

	/**
	 * @param array<string, mixed> $args
	 */
	private function register_cpt( string $type, array $args ): void {
		register_post_type( $type, $args );
	}

	/**
	 * @return array<string, string>
	 */
	private function labels( string $singular, string $plural ): array {
		return array(
			'name'          => $plural,
			'singular_name' => $singular,
			'add_new_item'  => sprintf( /* translators: %s singular */ __( 'Añadir %s', 'rodieja-contable' ), $singular ),
			'edit_item'     => sprintf( /* translators: %s singular */ __( 'Editar %s', 'rodieja-contable' ), $singular ),
			'search_items'  => sprintf( /* translators: %s plural */ __( 'Buscar %s', 'rodieja-contable' ), $plural ),
			'not_found'     => __( 'No encontrado', 'rodieja-contable' ),
			'menu_name'     => $plural,
		);
	}
}
