<?php
/**
 * Native CPT registration — maps sistema_vehicular entities to posts.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\PostTypes;

final class PostTypeRegistrar {

	public const MENU_PARENT = 'workshop-ledger';

	public function register(): void {
		add_action( 'init', array( $this, 'register_types' ) );
	}

	public function register_types(): void {
		// Menus are owned by AdminMenu (React-shaped top-levels) — hide CPT auto-menus.
		$hidden = array( 'show_in_menu' => false );

		$this->cpt(
			'wl_marca',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Brands', 'workshop-ledger' ),
					'labels' => $this->labels( __( 'Brand', 'workshop-ledger' ), __( 'Brands', 'workshop-ledger' ) ),
				)
			)
		);
		$this->cpt(
			'wl_modelo',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Models', 'workshop-ledger' ),
					'labels' => $this->labels( __( 'Model', 'workshop-ledger' ), __( 'Models', 'workshop-ledger' ) ),
				)
			)
		);
		$this->cpt(
			'wl_generacion',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Generations', 'workshop-ledger' ),
					'labels' => $this->labels( __( 'Generation', 'workshop-ledger' ), __( 'Generations', 'workshop-ledger' ) ),
				)
			)
		);
		$this->cpt(
			'wl_vehiculo',
			array_merge(
				$hidden,
				array(
					'label'    => __( 'Vehicles', 'workshop-ledger' ),
					'labels'   => $this->labels( __( 'Vehicle', 'workshop-ledger' ), __( 'Vehicles', 'workshop-ledger' ) ),
					// Classic meta-box form (Read-Only parity) — no Gutenberg canvas.
					'supports' => array( 'title', 'thumbnail' ),
				)
			)
		);
		$this->cpt(
			'wl_repuesto',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Parts', 'workshop-ledger' ),
					'labels' => $this->labels( __( 'Part', 'workshop-ledger' ), __( 'Parts', 'workshop-ledger' ) ),
				)
			)
		);
		$this->cpt(
			'wl_empleado',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Employees', 'workshop-ledger' ),
					'labels' => $this->labels( __( 'Employee', 'workshop-ledger' ), __( 'Employees', 'workshop-ledger' ) ),
				)
			)
		);
		$this->cpt(
			'wl_transaccion',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Transactions', 'workshop-ledger' ),
					'labels' => $this->labels( __( 'Transaction', 'workshop-ledger' ), __( 'Transactions', 'workshop-ledger' ) ),
				)
			)
		);
		$this->cpt(
			'wl_pedido',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Orders', 'workshop-ledger' ),
					'labels' => $this->labels( __( 'Order', 'workshop-ledger' ), __( 'Orders', 'workshop-ledger' ) ),
				)
			)
		);
		$this->cpt(
			'wl_pedido_item',
			array_merge(
				$hidden,
				array(
					'label'  => __( 'Order items', 'workshop-ledger' ),
					'labels' => $this->labels( __( 'Order item', 'workshop-ledger' ), __( 'Order items', 'workshop-ledger' ) ),
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
			'add_new_item'  => sprintf( __( 'Add %s', 'workshop-ledger' ), $one ),
			'edit_item'     => sprintf( __( 'Edit %s', 'workshop-ledger' ), $one ),
			'search_items'  => sprintf( __( 'Search %s', 'workshop-ledger' ), $many ),
			'not_found'     => sprintf( __( 'No %s found', 'workshop-ledger' ), $many ),
			'menu_name'     => $many,
		);
	}
}
