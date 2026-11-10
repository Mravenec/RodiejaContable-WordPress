<?php
/**
 * Native list-table columns for catalog CPTs.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\OutputEscaper;

final class CatalogListColumns {

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		add_filter( 'manage_wl_modelo_posts_columns', array( $this, 'modelo_cols' ) );
		add_action( 'manage_wl_modelo_posts_custom_column', array( $this, 'modelo_col' ), 10, 2 );

		add_filter( 'manage_wl_generacion_posts_columns', array( $this, 'generacion_cols' ) );
		add_action( 'manage_wl_generacion_posts_custom_column', array( $this, 'generacion_col' ), 10, 2 );

		add_filter( 'manage_wl_vehiculo_posts_columns', array( $this, 'vehiculo_cols' ) );
		add_action( 'manage_wl_vehiculo_posts_custom_column', array( $this, 'vehiculo_col' ), 10, 2 );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function modelo_cols( array $cols ): array {
		$cols['wl_marca'] = __( 'Brand', 'workshop-ledger' );
		return $cols;
	}

	public function modelo_col( string $col, int $post_id ): void {
		if ( 'wl_marca' !== $col ) {
			return;
		}
		$id = (int) get_post_meta( $post_id, 'wl_marca_id', true );
		echo $this->linked_title( $id );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function generacion_cols( array $cols ): array {
		$cols['wl_modelo'] = __( 'Model', 'workshop-ledger' );
		$cols['wl_anios']  = __( 'Years', 'workshop-ledger' );
		return $cols;
	}

	public function generacion_col( string $col, int $post_id ): void {
		if ( 'wl_modelo' === $col ) {
			echo $this->linked_title( (int) get_post_meta( $post_id, 'wl_modelo_id', true ) );
			return;
		}
		if ( 'wl_anios' === $col ) {
			$a = (int) get_post_meta( $post_id, 'wl_anio_inicio', true );
			$b = (int) get_post_meta( $post_id, 'wl_anio_fin', true );
			echo $this->escaper->html( $a && $b ? "{$a}–{$b}" : '—' );
		}
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function vehiculo_cols( array $cols ): array {
		$cols['wl_codigo']     = __( 'Code', 'workshop-ledger' );
		$cols['wl_generacion'] = __( 'Generation', 'workshop-ledger' );
		$cols['wl_anio']       = __( 'Year', 'workshop-ledger' );
		$cols['wl_precio']     = __( 'Price compra', 'workshop-ledger' );
		return $cols;
	}

	public function vehiculo_col( string $col, int $post_id ): void {
		switch ( $col ) {
			case 'wl_codigo':
				echo $this->escaper->html( (string) get_post_meta( $post_id, 'wl_codigo', true ) );
				break;
			case 'wl_generacion':
				echo $this->linked_title( (int) get_post_meta( $post_id, 'wl_generacion_id', true ) );
				break;
			case 'wl_anio':
				echo $this->escaper->html( (string) get_post_meta( $post_id, 'wl_anio', true ) );
				break;
			case 'wl_precio':
				$p = (float) get_post_meta( $post_id, 'wl_precio_compra', true );
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
