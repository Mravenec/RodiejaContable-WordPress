<?php
/**
 * Native meta boxes for Marca → Modelo → Generación → Vehículo chain.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;

final class CatalogMetaBoxes {

	public function __construct(
		private NonceGuard $nonce,
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_rodieja_modelo', array( $this, 'save_modelo' ), 10, 2 );
		add_action( 'save_post_rodieja_generacion', array( $this, 'save_generacion' ), 10, 2 );
		add_action( 'save_post_rodieja_vehiculo', array( $this, 'save_vehiculo' ), 10, 2 );
	}

	public function add_boxes(): void {
		add_meta_box(
			'rodieja_modelo_links',
			__( 'Catálogo · Modelo', 'rodieja-contable' ),
			array( $this, 'render_modelo' ),
			'rodieja_modelo',
			'normal',
			'high'
		);
		add_meta_box(
			'rodieja_generacion_links',
			__( 'Catálogo · Generación', 'rodieja-contable' ),
			array( $this, 'render_generacion' ),
			'rodieja_generacion',
			'normal',
			'high'
		);
		add_meta_box(
			'rodieja_vehiculo_links',
			__( 'Catálogo · Vehículo', 'rodieja-contable' ),
			array( $this, 'render_vehiculo' ),
			'rodieja_vehiculo',
			'normal',
			'high'
		);
	}

	public function render_modelo( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_catalog_meta' );
		$marca_id = (int) get_post_meta( $post->ID, 'rodieja_marca_id', true );
		echo '<p><label for="rodieja_marca_id">' . $this->escaper->html( __( 'Marca', 'rodieja-contable' ) ) . '</label><br />';
		$this->post_select( 'rodieja_marca', 'rodieja_marca_id', $marca_id );
		echo '</p>';
	}

	public function render_generacion( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_catalog_meta' );
		$modelo_id = (int) get_post_meta( $post->ID, 'rodieja_modelo_id', true );
		$anio_i    = (int) get_post_meta( $post->ID, 'rodieja_anio_inicio', true );
		$anio_f    = (int) get_post_meta( $post->ID, 'rodieja_anio_fin', true );
		echo '<p><label for="rodieja_modelo_id">' . $this->escaper->html( __( 'Modelo', 'rodieja-contable' ) ) . '</label><br />';
		$this->post_select( 'rodieja_modelo', 'rodieja_modelo_id', $modelo_id );
		echo '</p>';
		echo '<p><label>' . $this->escaper->html( __( 'Año inicio', 'rodieja-contable' ) ) . '</label><br />';
		echo '<input type="number" name="rodieja_anio_inicio" value="' . $this->escaper->attr( (string) $anio_i ) . '" class="small-text" /></p>';
		echo '<p><label>' . $this->escaper->html( __( 'Año fin', 'rodieja-contable' ) ) . '</label><br />';
		echo '<input type="number" name="rodieja_anio_fin" value="' . $this->escaper->attr( (string) $anio_f ) . '" class="small-text" /></p>';
	}

	public function render_vehiculo( \WP_Post $post ): void {
		if ( ! $this->gate->can_manage() ) {
			return;
		}
		$this->nonce->field( 'rodieja_catalog_meta' );
		$fields = array(
			'rodieja_codigo'           => array( 'label' => __( 'Código', 'rodieja-contable' ), 'type' => 'text' ),
			'rodieja_generacion_id'    => array( 'label' => __( 'Generación', 'rodieja-contable' ), 'type' => 'select_gen' ),
			'rodieja_anio'             => array( 'label' => __( 'Año', 'rodieja-contable' ), 'type' => 'number' ),
			'rodieja_precio_compra'    => array( 'label' => __( 'Precio compra', 'rodieja-contable' ), 'type' => 'number' ),
			'rodieja_costo_grua'       => array( 'label' => __( 'Costo grúa', 'rodieja-contable' ), 'type' => 'number' ),
			'rodieja_comisiones'       => array( 'label' => __( 'Comisiones', 'rodieja-contable' ), 'type' => 'number' ),
			'rodieja_costo_recuperado' => array( 'label' => __( 'Costo recuperado', 'rodieja-contable' ), 'type' => 'number' ),
			'rodieja_traccion'         => array( 'label' => __( 'Tracción', 'rodieja-contable' ), 'type' => 'text' ),
			'rodieja_transmision'      => array( 'label' => __( 'Transmisión', 'rodieja-contable' ), 'type' => 'text' ),
			'rodieja_combustible'      => array( 'label' => __( 'Combustible', 'rodieja-contable' ), 'type' => 'text' ),
			'rodieja_fecha_ingreso'    => array( 'label' => __( 'Fecha ingreso', 'rodieja-contable' ), 'type' => 'date' ),
		);
		foreach ( $fields as $key => $cfg ) {
			$val = get_post_meta( $post->ID, $key, true );
			echo '<p><label for="' . $this->escaper->attr( $key ) . '">' . $this->escaper->html( (string) $cfg['label'] ) . '</label><br />';
			if ( 'select_gen' === $cfg['type'] ) {
				$this->post_select( 'rodieja_generacion', $key, (int) $val );
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
		$marca = isset( $_POST['rodieja_marca_id'] ) ? absint( wp_unslash( $_POST['rodieja_marca_id'] ) ) : 0;
		update_post_meta( $post_id, 'rodieja_marca_id', (string) $marca );
	}

	public function save_generacion( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! $this->can_save( $post_id ) ) {
			return;
		}
		update_post_meta( $post_id, 'rodieja_modelo_id', (string) ( isset( $_POST['rodieja_modelo_id'] ) ? absint( wp_unslash( $_POST['rodieja_modelo_id'] ) ) : 0 ) );
		update_post_meta( $post_id, 'rodieja_anio_inicio', isset( $_POST['rodieja_anio_inicio'] ) ? (int) wp_unslash( $_POST['rodieja_anio_inicio'] ) : 0 );
		update_post_meta( $post_id, 'rodieja_anio_fin', isset( $_POST['rodieja_anio_fin'] ) ? (int) wp_unslash( $_POST['rodieja_anio_fin'] ) : 0 );
	}

	public function save_vehiculo( int $post_id, \WP_Post $post ): void {
		unset( $post );
		if ( ! $this->can_save( $post_id ) ) {
			return;
		}
		$string_keys = array( 'rodieja_codigo', 'rodieja_traccion', 'rodieja_transmision', 'rodieja_combustible', 'rodieja_fecha_ingreso', 'rodieja_generacion_id' );
		foreach ( $string_keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) ) );
			}
		}
		$number_keys = array( 'rodieja_anio', 'rodieja_precio_compra', 'rodieja_costo_grua', 'rodieja_comisiones', 'rodieja_costo_recuperado' );
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
		if ( ! isset( $_POST['_rodieja_nonce'] ) ) {
			return false;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['_rodieja_nonce'] ) ), 'rodieja_catalog_meta' ) ) {
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
		echo '<option value="0">' . $this->escaper->html( __( '— Seleccionar —', 'rodieja-contable' ) ) . '</option>';
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
