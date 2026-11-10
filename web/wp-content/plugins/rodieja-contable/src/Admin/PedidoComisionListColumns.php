<?php
/**
 * Admin list columns for pedido comisión fields.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\OutputEscaper;

final class PedidoComisionListColumns {

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		add_filter( 'manage_rodieja_pedido_posts_columns', array( $this, 'cols' ) );
		add_action( 'manage_rodieja_pedido_posts_custom_column', array( $this, 'col' ), 10, 2 );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function cols( array $cols ): array {
		$cols['rodieja_comision_pagada'] = __( 'Comisión pagada', 'rodieja-contable' );
		$cols['rodieja_comision_monto']  = __( 'Comisión', 'rodieja-contable' );
		return $cols;
	}

	public function col( string $col, int $post_id ): void {
		if ( 'rodieja_comision_pagada' === $col ) {
			$paid = (string) get_post_meta( $post_id, 'rodieja_comision_pagada', true );
			$yes  = in_array( $paid, array( '1', 'yes', 'si', 'sí' ), true );
			echo $this->escaper->html( $yes ? __( 'Sí', 'rodieja-contable' ) : __( 'No', 'rodieja-contable' ) );
			return;
		}
		if ( 'rodieja_comision_monto' === $col ) {
			echo $this->escaper->html(
				number_format_i18n( (float) get_post_meta( $post_id, 'rodieja_comision_calculada', true ), 2 )
			);
		}
	}
}
