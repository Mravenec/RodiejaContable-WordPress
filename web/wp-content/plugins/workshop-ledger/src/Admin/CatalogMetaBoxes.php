<?php
/**
 * Native meta boxes for Marca → Modelo → Generación → Vehículo chain.
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
		$fields = array(
			'wl_codigo'           => array( 'label' => __( 'Code', 'workshop-ledger' ), 'type' => 'text' ),
			'wl_generacion_id'    => array( 'label' => __( 'Generation', 'workshop-ledger' ), 'type' => 'select_gen' ),
			'wl_anio'             => array( 'label' => __( 'Year', 'workshop-ledger' ), 'type' => 'number' ),
			'wl_precio_compra'    => array( 'label' => __( 'Price compra', 'workshop-ledger' ), 'type' => 'number' ),
			'wl_costo_grua'       => array( 'label' => __( 'Tow cost', 'workshop-ledger' ), 'type' => 'number' ),
			'wl_comisiones'       => array( 'label' => __( 'Comisiones', 'workshop-ledger' ), 'type' => 'number' ),
			'wl_costo_recuperado' => array( 'label' => __( 'Costo recuperado', 'workshop-ledger' ), 'type' => 'number' ),
			'wl_traccion'         => array( 'label' => __( 'Drivetrain', 'workshop-ledger' ), 'type' => 'text' ),
			'wl_transmision'      => array( 'label' => __( 'Transmission', 'workshop-ledger' ), 'type' => 'text' ),
			'wl_combustible'      => array( 'label' => __( 'Combustible', 'workshop-ledger' ), 'type' => 'text' ),
			'wl_fecha_ingreso'    => array( 'label' => __( 'Date ingreso', 'workshop-ledger' ), 'type' => 'date' ),
		);
		foreach ( $fields as $key => $cfg ) {
			$val = get_post_meta( $post->ID, $key, true );
			echo '<p><label for="' . $this->escaper->attr( $key ) . '">' . $this->escaper->html( (string) $cfg['label'] ) . '</label><br />';
			if ( 'select_gen' === $cfg['type'] ) {
				$this->post_select( 'wl_generacion', $key, (int) $val );
			} else {
				$type = 'date' === $cfg['type'] ? 'date' : ( 'number' === $cfg['type'] ? 'number' : 'text' );
				echo '<input type="' . $this->escaper->attr( $type ) . '" class="regular-text" name="' . $this->escaper->attr( $key ) . '" id="' . $this->escaper->attr( $key ) . '" value="' . $this->escaper->attr( (string) $val ) . '" />';
			}
			echo '</p>';
		}
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
		$string_keys = array( 'wl_codigo', 'wl_traccion', 'wl_transmision', 'wl_combustible', 'wl_fecha_ingreso', 'wl_generacion_id' );
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
		echo '<select name="' . $this->escaper->attr( $name ) . '" id="' . $this->escaper->attr( $name ) . '">';
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
}
