<?php
/**
 * Native list-table columns for catalog CPTs.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\OutputEscaper;

final class CatalogListColumns {

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		add_filter( 'manage_rodieja_modelo_posts_columns', array( $this, 'modelo_cols' ) );
		add_action( 'manage_rodieja_modelo_posts_custom_column', array( $this, 'modelo_col' ), 10, 2 );

		add_filter( 'manage_rodieja_generacion_posts_columns', array( $this, 'generacion_cols' ) );
		add_action( 'manage_rodieja_generacion_posts_custom_column', array( $this, 'generacion_col' ), 10, 2 );

		add_filter( 'manage_rodieja_vehiculo_posts_columns', array( $this, 'vehiculo_cols' ) );
		add_action( 'manage_rodieja_vehiculo_posts_custom_column', array( $this, 'vehiculo_col' ), 10, 2 );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function modelo_cols( array $cols ): array {
		$cols['rodieja_marca'] = __( 'Brand', 'rodieja-contable' );
		return $cols;
	}

	public function modelo_col( string $col, int $post_id ): void {
		if ( 'rodieja_marca' !== $col ) {
			return;
		}
		$id = (int) get_post_meta( $post_id, 'rodieja_marca_id', true );
		echo $this->linked_title( $id );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function generacion_cols( array $cols ): array {
		$cols['rodieja_modelo'] = __( 'Model', 'rodieja-contable' );
		$cols['rodieja_anios']  = __( 'Years', 'rodieja-contable' );
		return $cols;
	}

	public function generacion_col( string $col, int $post_id ): void {
		if ( 'rodieja_modelo' === $col ) {
			echo $this->linked_title( (int) get_post_meta( $post_id, 'rodieja_modelo_id', true ) );
			return;
		}
		if ( 'rodieja_anios' === $col ) {
			$a = (int) get_post_meta( $post_id, 'rodieja_anio_inicio', true );
			$b = (int) get_post_meta( $post_id, 'rodieja_anio_fin', true );
			echo $this->escaper->html( $a && $b ? "{$a}–{$b}" : '—' );
		}
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function vehiculo_cols( array $cols ): array {
		$cols['rodieja_codigo']     = __( 'Code', 'rodieja-contable' );
		$cols['rodieja_generacion'] = __( 'Generation', 'rodieja-contable' );
		$cols['rodieja_anio']       = __( 'Year', 'rodieja-contable' );
		$cols['rodieja_precio']     = __( 'Price compra', 'rodieja-contable' );
		return $cols;
	}

	public function vehiculo_col( string $col, int $post_id ): void {
		switch ( $col ) {
			case 'rodieja_codigo':
				echo $this->escaper->html( (string) get_post_meta( $post_id, 'rodieja_codigo', true ) );
				break;
			case 'rodieja_generacion':
				echo $this->linked_title( (int) get_post_meta( $post_id, 'rodieja_generacion_id', true ) );
				break;
			case 'rodieja_anio':
				echo $this->escaper->html( (string) get_post_meta( $post_id, 'rodieja_anio', true ) );
				break;
			case 'rodieja_precio':
				$p = (float) get_post_meta( $post_id, 'rodieja_precio_compra', true );
				echo $this->escaper->html( $p ? number_format_i18n( $p, 2 ) : '—' );
				break;
		}
	}

	private function linked_title( int $id ): string {
		if ( $id <= 0 ) {
			return '—';
		}
		$title = get_the_title( $id );
		$link  = get_edit_post_link( $id );
		if ( ! $link ) {
			return $this->escaper->html( $title ?: (string) $id );
		}
		return '<a href="' . $this->escaper->attr( $link ) . '">' . $this->escaper->html( $title ?: (string) $id ) . '</a>';
	}
}
