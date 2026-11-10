<?php
/**
 * Native meta boxes for parts — Read-Only NuevoRepuesto field parity on CPT.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;

final class InventoryMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_wl_repuesto', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_part_cascade' ) );
		add_filter( 'manage_wl_repuesto_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_wl_repuesto_posts_custom_column', array( $this, 'column' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box(
			'wl_repuesto_inv',
			__( 'Inventory · Part', 'workshop-ledger' ),
			array( $this, 'render' ),
			'wl_repuesto',
			'normal',
			'high'
		);
	}

	public function enqueue_part_cascade( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! ( $screen instanceof \WP_Screen ) || 'wl_repuesto' !== $screen->post_type ) {
			return;
		}
		$path = WL_PLUGIN_DIR . 'assets/js/part-cascade.js';
		$ver  = is_readable( $path ) ? (string) filemtime( $path ) : WL_PLUGIN_VERSION;
		wp_enqueue_script(
			'workshop-ledger-part-cascade',
			WL_PLUGIN_URL . 'assets/js/part-cascade.js',
			array(),
			$ver,
			true
		);
		wp_localize_script(
			'workshop-ledger-part-cascade',
			'wlPartCascade',
			array(
				'models'      => $this->catalog_models(),
				'generations' => $this->catalog_generations(),
				'i18n'        => array(
					'selectModel'      => __( '— Select model —', 'workshop-ledger' ),
					'selectGeneration' => __( '— Select generation —', 'workshop-ledger' ),
				),
			)
		);
	}

	public function render( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_inventory_meta' );

		$modo       = (string) get_post_meta( $post->ID, 'wl_modo', true );
		$vehiculo   = (int) get_post_meta( $post->ID, 'wl_vehiculo_id', true );
		$gen_id     = (int) get_post_meta( $post->ID, 'wl_generacion_id', true );
		$costo      = (string) get_post_meta( $post->ID, 'wl_precio_costo', true );
		$venta      = (string) get_post_meta( $post->ID, 'wl_precio_venta', true );
		$legacy     = (string) get_post_meta( $post->ID, 'wl_precio', true );
		$stock      = (string) get_post_meta( $post->ID, 'wl_stock', true );
		$condicion  = (string) get_post_meta( $post->ID, 'wl_condicion', true );
		$original   = (string) get_post_meta( $post->ID, 'wl_originalidad', true );

		if ( '' === $modo ) {
			$modo = $vehiculo > 0 ? 'con_vehiculo' : ( $gen_id > 0 ? 'sin_vehiculo' : 'con_vehiculo' );
		}
		if ( '' === $venta && '' !== $legacy ) {
			$venta = $legacy;
		}

		$modelo_id = $gen_id > 0 ? (int) get_post_meta( $gen_id, 'wl_modelo_id', true ) : 0;
		$marca_id  = $modelo_id > 0 ? (int) get_post_meta( $modelo_id, 'wl_marca_id', true ) : 0;

		echo '<div class="wl-part-form">';
		echo '<p class="description">' . $this->escaper->html( __( 'Same logic as Read-Only Nuevo Repuesto: with vehicle, or Brand → Model → Generation without vehicle. Native WordPress meta.', 'workshop-ledger' ) ) . '</p>';

		echo '<fieldset class="wl-part-form__mode"><legend>' . $this->escaper->html( __( 'Part origin', 'workshop-ledger' ) ) . '</legend>';
		printf(
			'<label><input type="radio" name="wl_modo" value="con_vehiculo" %1$s /> %2$s</label> ',
			checked( $modo, 'con_vehiculo', false ),
			$this->escaper->html( __( 'From a vehicle', 'workshop-ledger' ) )
		);
		printf(
			'<label><input type="radio" name="wl_modo" value="sin_vehiculo" %1$s /> %2$s</label>',
			checked( $modo, 'sin_vehiculo', false ),
			$this->escaper->html( __( 'Generic (no vehicle)', 'workshop-ledger' ) )
		);
		echo '</fieldset>';

		echo '<div id="wl-part-with-vehicle" class="wl-part-form__block">';
		echo '<p><label for="wl_vehiculo_id">' . $this->escaper->html( __( 'Source vehicle', 'workshop-ledger' ) ) . '</label><br />';
		$this->post_select( 'wl_vehiculo', 'wl_vehiculo_id', $vehiculo );
		echo '</p></div>';

		echo '<div id="wl-part-without-vehicle" class="wl-part-form__block wl-part-form__grid">';
		echo '<p><label for="wl_ui_marca_id">' . $this->escaper->html( __( 'Brand', 'workshop-ledger' ) ) . '</label><br />';
		$this->post_select( 'wl_marca', 'wl_ui_marca_id', $marca_id );
		echo '</p>';
		echo '<p><label for="wl_ui_modelo_id">' . $this->escaper->html( __( 'Model', 'workshop-ledger' ) ) . '</label><br />';
		$this->post_select( 'wl_modelo', 'wl_ui_modelo_id', $modelo_id );
		echo '</p>';
		echo '<p><label for="wl_generacion_id">' . $this->escaper->html( __( 'Generation', 'workshop-ledger' ) ) . '</label><br />';
		$this->post_select( 'wl_generacion', 'wl_generacion_id', $gen_id );
		echo '</p></div>';

		echo '<div class="wl-part-form__grid">';
		$this->input_row( 'wl_precio_costo', __( 'Cost price', 'workshop-ledger' ), $costo, 'number' );
		$this->input_row( 'wl_precio_venta', __( 'Sale price', 'workshop-ledger' ), $venta, 'number' );
		$this->input_row( 'wl_stock', __( 'Stock', 'workshop-ledger' ), $stock, 'number' );
		$this->enum_row(
			'wl_condicion',
			__( 'Condition', 'workshop-ledger' ),
			$condicion,
			array(
				'NUEVO'      => __( 'New', 'workshop-ledger' ),
				'USADO'      => __( 'Used', 'workshop-ledger' ),
				'REACOND'    => __( 'Refurbished', 'workshop-ledger' ),
			)
		);
		$this->enum_row(
			'wl_originalidad',
			__( 'Originality', 'workshop-ledger' ),
			$original,
			array(
				'ORIGINAL' => __( 'OEM / Original', 'workshop-ledger' ),
				'GENERICO' => __( 'Aftermarket', 'workshop-ledger' ),
			)
		);
		echo '</div>';

		echo '<h4>' . $this->escaper->html( __( 'Physical location (optional)', 'workshop-ledger' ) ) . '</h4>';
		echo '<div class="wl-part-form__grid">';
		foreach ( array(
			'wl_bodega'    => __( 'Warehouse', 'workshop-ledger' ),
			'wl_zona'      => __( 'Zone', 'workshop-ledger' ),
			'wl_estante'   => __( 'Shelf', 'workshop-ledger' ),
			'wl_nivel'     => __( 'Level', 'workshop-ledger' ),
			'wl_posicion'  => __( 'Position', 'workshop-ledger' ),
		) as $key => $label ) {
			$val = (string) get_post_meta( $post->ID, $key, true );
			$this->input_row( $key, $label, $val, 'text' );
		}
		echo '</div>';

		echo '<p class="description">' . $this->escaper->html( __( 'Assign Part taxonomy in the side panel (native). Gallery uses the media meta box.', 'workshop-ledger' ) ) . '</p>';
		echo '</div>';
	}

	public function save( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $this->gate->can_manage() || ! $this->nonce->verify_post( 'wl_inventory_meta' ) ) {
			return;
		}

		$modo = isset( $_POST['wl_modo'] ) ? sanitize_key( wp_unslash( (string) $_POST['wl_modo'] ) ) : 'con_vehiculo';
		if ( ! in_array( $modo, array( 'con_vehiculo', 'sin_vehiculo' ), true ) ) {
			$modo = 'con_vehiculo';
		}
		update_post_meta( $post_id, 'wl_modo', $modo );

		if ( 'con_vehiculo' === $modo ) {
			$vid = isset( $_POST['wl_vehiculo_id'] ) ? absint( wp_unslash( $_POST['wl_vehiculo_id'] ) ) : 0;
			update_post_meta( $post_id, 'wl_vehiculo_id', (string) $vid );
			update_post_meta( $post_id, 'wl_generacion_id', '0' );
		} else {
			$gid = isset( $_POST['wl_generacion_id'] ) ? absint( wp_unslash( $_POST['wl_generacion_id'] ) ) : 0;
			update_post_meta( $post_id, 'wl_generacion_id', (string) $gid );
			update_post_meta( $post_id, 'wl_vehiculo_id', '0' );
		}

		$costo = isset( $_POST['wl_precio_costo'] ) ? (float) wp_unslash( $_POST['wl_precio_costo'] ) : 0.0;
		$venta = isset( $_POST['wl_precio_venta'] ) ? (float) wp_unslash( $_POST['wl_precio_venta'] ) : 0.0;
		if ( $venta <= 0 && $costo > 0 ) {
			$venta = round( $costo * 1.5, 2 );
		}
		update_post_meta( $post_id, 'wl_precio_costo', $costo );
		update_post_meta( $post_id, 'wl_precio_venta', $venta );
		update_post_meta( $post_id, 'wl_precio', $venta ); // keep list columns / legacy readers
		update_post_meta( $post_id, 'wl_stock', isset( $_POST['wl_stock'] ) ? (int) wp_unslash( $_POST['wl_stock'] ) : 0 );

		foreach ( array( 'wl_condicion', 'wl_originalidad', 'wl_bodega', 'wl_zona', 'wl_estante', 'wl_nivel', 'wl_posicion' ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) ) );
			}
		}
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function columns( array $cols ): array {
		$cols['wl_vehiculo']     = __( 'Vehicle', 'workshop-ledger' );
		$cols['wl_precio_costo'] = __( 'Cost', 'workshop-ledger' );
		$cols['wl_precio']       = __( 'Sale price', 'workshop-ledger' );
		$cols['wl_stock']        = __( 'Stock', 'workshop-ledger' );
		$cols['wl_condicion']    = __( 'Condition', 'workshop-ledger' );
		return $cols;
	}

	public function column( string $col, int $post_id ): void {
		if ( 'wl_vehiculo' === $col ) {
			$id = (int) get_post_meta( $post_id, 'wl_vehiculo_id', true );
			echo $id ? $this->escaper->html( get_the_title( $id ) ) : '—';
		} elseif ( 'wl_precio_costo' === $col ) {
			echo $this->escaper->html( number_format_i18n( (float) get_post_meta( $post_id, 'wl_precio_costo', true ), 2 ) );
		} elseif ( 'wl_precio' === $col ) {
			$venta = get_post_meta( $post_id, 'wl_precio_venta', true );
			if ( '' === $venta || null === $venta ) {
				$venta = get_post_meta( $post_id, 'wl_precio', true );
			}
			echo $this->escaper->html( number_format_i18n( (float) $venta, 2 ) );
		} elseif ( 'wl_stock' === $col ) {
			echo $this->escaper->html( (string) (int) get_post_meta( $post_id, 'wl_stock', true ) );
		} elseif ( 'wl_condicion' === $col ) {
			echo $this->escaper->html( (string) get_post_meta( $post_id, 'wl_condicion', true ) ?: '—' );
		}
	}

	private function post_select( string $type, string $name, int $selected ): void {
		$posts = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		echo '<select name="' . $this->escaper->attr( $name ) . '" id="' . $this->escaper->attr( $name ) . '" class="wl-vehicle-select">';
		echo '<option value="0">' . $this->escaper->html( __( '— Select —', 'workshop-ledger' ) ) . '</option>';
		foreach ( $posts as $p ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				(int) $p->ID,
				selected( $selected, (int) $p->ID, false ),
				$this->escaper->html( get_the_title( $p ) )
			);
		}
		echo '</select>';
	}

	private function input_row( string $key, string $label, string $val, string $type ): void {
		$step = 'number' === $type ? ' step="0.01"' : '';
		echo '<p><label for="' . $this->escaper->attr( $key ) . '">' . $this->escaper->html( $label ) . '</label><br />';
		echo '<input type="' . $this->escaper->attr( $type ) . '"' . $step . ' class="regular-text" name="' . $this->escaper->attr( $key ) . '" id="' . $this->escaper->attr( $key ) . '" value="' . $this->escaper->attr( $val ) . '" /></p>';
	}

	/**
	 * @param array<string, string> $options
	 */
	private function enum_row( string $key, string $label, string $val, array $options ): void {
		echo '<p><label for="' . $this->escaper->attr( $key ) . '">' . $this->escaper->html( $label ) . '</label><br />';
		echo '<select name="' . $this->escaper->attr( $key ) . '" id="' . $this->escaper->attr( $key ) . '" class="wl-vehicle-select">';
		echo '<option value="">' . $this->escaper->html( __( '— Select —', 'workshop-ledger' ) ) . '</option>';
		foreach ( $options as $opt_val => $opt_label ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				$this->escaper->attr( (string) $opt_val ),
				selected( $val, (string) $opt_val, false ),
				$this->escaper->html( (string) $opt_label )
			);
		}
		echo '</select></p>';
	}

	/** @return list<array{id:int,title:string,marca_id:int}> */
	private function catalog_models(): array {
		$out = array();
		foreach ( get_posts( array( 'post_type' => 'wl_modelo', 'post_status' => 'publish', 'posts_per_page' => 500 ) ) as $p ) {
			$out[] = array(
				'id'       => (int) $p->ID,
				'title'    => get_the_title( $p ),
				'marca_id' => (int) get_post_meta( $p->ID, 'wl_marca_id', true ),
			);
		}
		return $out;
	}

	/** @return list<array{id:int,title:string,modelo_id:int}> */
	private function catalog_generations(): array {
		$out = array();
		foreach ( get_posts( array( 'post_type' => 'wl_generacion', 'post_status' => 'publish', 'posts_per_page' => 500 ) ) as $p ) {
			$out[] = array(
				'id'        => (int) $p->ID,
				'title'     => get_the_title( $p ),
				'modelo_id' => (int) get_post_meta( $p->ID, 'wl_modelo_id', true ),
			);
		}
		return $out;
	}
}
