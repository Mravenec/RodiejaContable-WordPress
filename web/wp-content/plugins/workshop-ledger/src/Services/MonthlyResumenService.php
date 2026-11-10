<?php
/**
 * Monthly summary email — WP_Query aggregates + wp_mail.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

final class MonthlyResumenService {

	public function send_to_admin(): bool {
		$to      = (string) get_option( 'admin_email' );
		$subject = sprintf(
			/* translators: %s: month year */
			__( 'WorkshopLedger — monthly digest %s', 'workshop-ledger' ),
			gmdate( 'Y-m' )
		);
		$body    = $this->build_body();
		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		return (bool) wp_mail( $to, $subject, $body, $headers );
	}

	public function build_body(): string {
		$month_start = gmdate( 'Y-m-01 00:00:00' );
		$counts      = array(
			'vehiculos'      => $this->count_published( 'wl_vehiculo' ),
			'repuestos'      => $this->count_published( 'wl_repuesto' ),
			'transacciones'  => $this->count_published( 'wl_transaccion' ),
			'pedidos'        => $this->count_published( 'wl_pedido' ),
			'historial_mes'  => $this->count_since( 'wl_evento', $month_start ),
		);

		$ingresos = 0.0;
		$egresos  = 0.0;
		foreach ( $this->posts_since( 'wl_transaccion', $month_start ) as $post ) {
			$monto   = (float) get_post_meta( $post->ID, 'wl_monto', true );
			$sentido = (string) get_post_meta( $post->ID, 'wl_sentido', true );
			if ( 'egreso' === $sentido ) {
				$egresos += $monto;
			} else {
				$ingresos += $monto;
			}
		}

		$lines = array(
			__( 'Workshop Ledger digest (native WP)', 'workshop-ledger' ),
			'',
			sprintf( __( 'Published vehicles: %d', 'workshop-ledger' ), $counts['vehiculos'] ),
			sprintf( __( 'Published parts: %d', 'workshop-ledger' ), $counts['repuestos'] ),
			sprintf( __( 'Published transactions: %d', 'workshop-ledger' ), $counts['transacciones'] ),
			sprintf( __( 'Published orders: %d', 'workshop-ledger' ), $counts['pedidos'] ),
			sprintf( __( 'History events this month: %d', 'workshop-ledger' ), $counts['historial_mes'] ),
			'',
			sprintf( __( 'Month income (transactions): %s', 'workshop-ledger' ), number_format_i18n( $ingresos, 2 ) ),
			sprintf( __( 'Month expenses (transactions): %s', 'workshop-ledger' ), number_format_i18n( $egresos, 2 ) ),
		);

		return implode( "\n", $lines );
	}

	private function count_published( string $type ): int {
		$c = wp_count_posts( $type );
		return isset( $c->publish ) ? (int) $c->publish : 0;
	}

	private function count_since( string $type, string $since_gmt ): int {
		$q = new \WP_Query(
			array(
				'post_type'      => $type,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'date_query'     => array(
					array(
						'column' => 'post_date_gmt',
						'after'  => $since_gmt,
					),
				),
				'fields'         => 'ids',
			)
		);
		return (int) $q->found_posts;
	}

	/** @return \WP_Post[] */
	private function posts_since( string $type, string $since_gmt ): array {
		$q = new \WP_Query(
			array(
				'post_type'      => $type,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'date_query'     => array(
					array(
						'column' => 'post_date_gmt',
						'after'  => $since_gmt,
					),
				),
				'no_found_rows'  => true,
			)
		);
		return $q->posts;
	}
}
