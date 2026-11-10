<?php
/**
 * Native meta boxes for Brand → Model → Generation → Vehicle chain.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;

final class CatalogMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_wl_modelo', array( $this, 'save_modelo' ), 10, 2 );
		add_action( 'save_post_wl_generacion', array( $this, 'save_generacion' ), 10, 2 );
		add_action( 'save_post_wl_vehiculo', array( $this, 'save_vehiculo' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_vehicle_cascade' ) );
	}

	public function add_boxes(): void {
		add_meta_box(
			'wl_modelo_links',
			__( 'Catalog · Model', 'workshop-ledger' ),
			array( $this, 'render_modelo' ),
			'wl_modelo',
			'normal',
			'high'
		);
		add_meta_box(
			'wl_generacion_links',
			__( 'Catalog · Generation', 'workshop-ledger' ),
			array( $this, 'render_generacion' ),
			'wl_generacion',
			'normal',
			'high'
		);
		add_meta_box(
			'wl_vehiculo_links',
			__( 'Catalog · Vehicle', 'workshop-ledger' ),
			array( $this, 'render_vehiculo' ),
			'wl_vehiculo',
			'normal',
			'high'
		);
	}

	public function enqueue_vehicle_cascade( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! ( $screen instanceof \WP_Screen ) || 'wl_vehiculo' !== $screen->post_type ) {
			return;
		}
		$path = WL_PLUGIN_DIR . 'assets/js/vehicle-cascade.js';
		$ver  = is_readable( $path ) ? (string) filemtime( $path ) : WL_PLUGIN_VERSION;
		wp_enqueue_script(
			'workshop-ledger-vehicle-cascade',
			WL_PLUGIN_URL . 'assets/js/vehicle-cascade.js',
			array(),
			$ver,
			true
		);
		wp_localize_script(
			'workshop-ledger-vehicle-cascade',
			'wlVehicleCascade',
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

	public function render_modelo( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_catalog_meta' );
		$marca_id = (int) get_post_meta( $post->ID, 'wl_marca_id', true );
		echo '<p><label for="wl_marca_id">' . $this->escaper->html( __( 'Brand', 'workshop-ledger' ) ) . '</label><br />';
		$this->post_select( 'wl_marca', 'wl_marca_id', $marca_id );
		echo '</p>';
	}

	public function render_generacion( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_catalog_meta' );
		$modelo_id = (int) get_post_meta( $post->ID, 'wl_modelo_id', true );
		$anio_i    = (int) get_post_meta( $post->ID, 'wl_anio_inicio', true );
		$anio_f    = (int) get_post_meta( $post->ID, 'wl_anio_fin', true );
		echo '<p><label for="wl_modelo_id">' . $this->escaper->html( __( 'Model', 'workshop-ledger' ) ) . '</label><br />';
		$this->post_select( 'wl_modelo', 'wl_modelo_id', $modelo_id );
		echo '</p>';
		echo '<p><label>' . $this->escaper->html( __( 'Start year', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="number" name="wl_anio_inicio" value="' . $this->escaper->attr( (string) $anio_i ) . '" class="small-text" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'End year', 'workshop-ledger' ) ) . '</label><br />';
		echo '<input type="number" name="wl_anio_fin" value="' . $this->escaper->attr( (string) $anio_f ) . '" class="small-text" /></p>';
	}

	public function render_vehiculo( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'wl_catalog_meta' );

		$gen_id    = (int) get_post_meta( $post->ID, 'wl_generacion_id', true );
		$modelo_id = $gen_id > 0 ? (int) get_post_meta( $gen_id, 'wl_modelo_id', true ) : 0;
		$marca_id  = $modelo_id > 0 ? (int) get_post_meta( $modelo_id, 'wl_marca_id', true ) : 0;

		echo '<div class="wl-vehicle-form">';
		echo '<p class="description">' . $this->escaper->html( __( 'Same cascade as Read-Only: Brand → Model → Generation, then costs and specs. Classic WordPress form (no block editor).', 'workshop-ledger' ) ) . '</p>';

		echo '<div class="wl-vehicle-form__grid">';
		echo '<p><label for="wl_ui_marca_id">' . $this->escaper->html( __( 'Brand', 'workshop-ledger' ) ) . '</label><br />';
		$this->post_select( 'wl_marca', 'wl_ui_marca_id', $marca_id );
		echo '</p>';

		echo '<p><label for="wl_ui_modelo_id">' . $this->escaper->html( __( 'Model', 'workshop-ledger' ) ) . '</label><br />';
		$this->post_select( 'wl_modelo', 'wl_ui_modelo_id', $modelo_id );
		echo '</p>';

		echo '<p><label for="wl_generacion_id">' . $this->escaper->html( __( 'Generation', 'workshop-ledger' ) ) . '</label><br />';
		$this->post_select( 'wl_generacion', 'wl_generacion_id', $gen_id );
		echo '</p>';

		$this->input_row( 'wl_codigo', __( 'Code / plate', 'workshop-ledger' ), (string) get_post_meta( $post->ID, 'wl_codigo', true ), 'text' );
		$this->input_row( 'wl_anio', __( 'Year', 'workshop-ledger' ), (string) get_post_meta( $post->ID, 'wl_anio', true ), 'number' );
		$this->input_row( 'wl_fecha_ingreso', __( 'Intake date', 'workshop-ledger' ), (string) get_post_meta( $post->ID, 'wl_fecha_ingreso', true ), 'date' );

		$this->input_row( 'wl_precio_compra', __( 'Purchase price', 'workshop-ledger' ), (string) get_post_meta( $post->ID, 'wl_precio_compra', true ), 'number' );
		$this->input_row( 'wl_costo_grua', __( 'Tow cost', 'workshop-ledger' ), (string) get_post_meta( $post->ID, 'wl_costo_grua', true ), 'number' );
		$this->input_row( 'wl_comisiones', __( 'Commissions', 'workshop-ledger' ), (string) get_post_meta( $post->ID, 'wl_comisiones', true ), 'number' );
		$this->input_row( 'wl_costo_recuperado', __( 'Recovered cost', 'workshop-ledger' ), (string) get_post_meta( $post->ID, 'wl_costo_recuperado', true ), 'number' );

		$this->enum_row(
			'wl_traccion',
			__( 'Drivetrain', 'workshop-ledger' ),
			(string) get_post_meta( $post->ID, 'wl_traccion', true ),
			array( '4x2' => '4x2', '4x4' => '4x4' )
		);
		$this->enum_row(
			'wl_transmision',
			__( 'Transmission', 'workshop-ledger' ),
			(string) get_post_meta( $post->ID, 'wl_transmision', true ),
			array(
				'Automatico' => __( 'Automatic', 'workshop-ledger' ),
				'Manual'     => __( 'Manual', 'workshop-ledger' ),
			)
		);
		$this->enum_row(
			'wl_combustible',
			__( 'Fuel', 'workshop-ledger' ),
			(string) get_post_meta( $post->ID, 'wl_combustible', true ),
			array(
				'Gasolina'  => __( 'Gasoline', 'workshop-ledger' ),
				'Diesel'    => __( 'Diesel', 'workshop-ledger' ),
				'Eléctrico' => __( 'Electric', 'workshop-ledger' ),
			)
		);
		$this->input_row( 'wl_cilindraje', __( 'Displacement', 'workshop-ledger' ), (string) get_post_meta( $post->ID, 'wl_cilindraje', true ), 'text' );
		echo '</div></div>';
	}

	public function save_modelo( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! $this->can_save( $post_id ) ) {
			return;
		}
		$marca = isset( $_POST['wl_marca_id'] ) ? absint( wp_unslash( $_POST['wl_marca_id'] ) ) : 0;
		update_post_meta( $post_id, 'wl_marca_id', (string) $marca );
	}

	public function save_generacion( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! $this->can_save( $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, 'wl_modelo_id', (string) ( isset( $_POST['wl_modelo_id'] ) ? absint( wp_unslash( $_POST['wl_modelo_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'wl_anio_inicio', isset( $_POST['wl_anio_inicio'] ) ? (int) wp_unslash( $_POST['wl_anio_inicio'] ) : 0 );
		update_post_meta( $post_id, 'wl_anio_fin', isset( $_POST['wl_anio_fin'] ) ? (int) wp_unslash( $_POST['wl_anio_fin'] ) : 0 );
	}

	public function save_vehiculo( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! $this->can_save( $post_id ) ) {
			return;
		}
		$string_keys = array( 'wl_codigo', 'wl_traccion', 'wl_transmision', 'wl_combustible', 'wl_fecha_ingreso', 'wl_generacion_id', 'wl_cilindraje' );
		foreach ( $string_keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) ) );
			}
		}
		$number_keys = array( 'wl_anio', 'wl_precio_compra', 'wl_costo_grua', 'wl_comisiones', 'wl_costo_recuperado' );
		foreach ( $number_keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, (float) wp_unslash( $_POST[ $key ] ) );
			}
		}
	}

	private function can_save( int $post_id ): bool {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		if ( ! $this->gate->can_manage() ) {
			return false;
		}
		if ( ! isset( $_POST['_wl_nonce'] ) ) {
			return false;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_wl_nonce'] ) ), 'wl_catalog_meta' ) ) {
			return false;
		}
		return true;
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
		echo '<p><label for="' . $this->escaper->attr( $key ) . '">' . $this->escaper->html( $label ) . '</label><br />';
		echo '<input type="' . $this->escaper->attr( $type ) . '" class="regular-text" name="' . $this->escaper->attr( $key ) . '" id="' . $this->escaper->attr( $key ) . '" value="' . $this->escaper->attr( $val ) . '" /></p>';
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

	/** @return list<array{id:int,title:string,modelo_id:int,anio_inicio:int,anio_fin:int}> */
	private function catalog_generations(): array {
		$out = array();
		foreach ( get_posts( array( 'post_type' => 'wl_generacion', 'post_status' => 'publish', 'posts_per_page' => 500 ) ) as $p ) {
			$out[] = array(
				'id'          => (int) $p->ID,
				'title'       => get_the_title( $p ),
				'modelo_id'   => (int) get_post_meta( $p->ID, 'wl_modelo_id', true ),
				'anio_inicio' => (int) get_post_meta( $p->ID, 'wl_anio_inicio', true ),
				'anio_fin'    => (int) get_post_meta( $p->ID, 'wl_anio_fin', true ),
			);
		}
		return $out;
	}
}
