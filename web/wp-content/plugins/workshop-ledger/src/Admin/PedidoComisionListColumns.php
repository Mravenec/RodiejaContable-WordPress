<?php
/**
 * Admin list columns for pedido comisión fields.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\OutputEscaper;

final class PedidoComisionListColumns {

	public function __construct( private OutputEscaper $escaper ) {}

	public function register(): void {
		add_filter( 'manage_wl_pedido_posts_columns', array( $this, 'cols' ) );
		add_action( 'manage_wl_pedido_posts_custom_column', array( $this, 'col' ), 10, 2 );
	}

	/**
	 * @param array<string, string> $cols
	 * @return array<string, string>
	 */
	public function cols( array $cols ): array {
		$cols['wl_comision_pagada'] = __( 'Commission pagada', 'workshop-ledger' );
		$cols['wl_comision_monto']  = __( 'Commission', 'workshop-ledger' );
		return $cols;
	}

	public function col( string $col, int $post_id ): void {
		if ( 'wl_comision_pagada' === $col ) {
			$paid = (string) get_post_meta( $post_id, 'wl_comision_pagada', true );
			$yes  = in_array( $paid, array( '1', 'yes', 'si', 'sí' ), true );
			echo $this->escaper->html( $yes ? __( 'Yes', 'workshop-ledger' ) : __( 'No', 'workshop-ledger' ) );
			return;
		}
		if ( 'wl_comision_monto' === $col ) {
			echo $this->escaper->html(
				number_format_i18n( (float) get_post_meta( $post_id, 'wl_comision_calculada', true ), 2 )
			);
		}
	}
}
