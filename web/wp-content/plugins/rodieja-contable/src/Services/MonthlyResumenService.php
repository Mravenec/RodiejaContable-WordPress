<?php
/**
 * Monthly summary email — WP_Query aggregates + wp_mail.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class MonthlyResumenService {

	public function send_to_admin(): bool {
		$to      = (string) get_option( 'admin_email' );
		$subject = sprintf(
			/* translators: %s: month year */
			__( 'Rodieja — resumen mensual %s', 'rodieja-contable' ),
			gmdate( 'Y-m' )
		);
		$body    = $this->build_body();
		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

		return (bool) wp_mail( $to, $subject, $body, $headers );
	}

	public function build_body(): string {
		$month_start = gmdate( 'Y-m-01 00:00:00' );
		$counts      = array(
			'vehiculos'      => $this->count_published( 'rodieja_vehiculo' ),
			'repuestos'      => $this->count_published( 'rodieja_repuesto' ),
			'transacciones'  => $this->count_published( 'rodieja_transaccion' ),
			'pedidos'        => $this->count_published( 'rodieja_pedido' ),
			'historial_mes'  => $this->count_since( 'rodieja_evento', $month_start ),
		);

		$ingresos = 0.0;
		$egresos  = 0.0;
		foreach ( $this->posts_since( 'rodieja_transaccion', $month_start ) as $post ) {
			$monto   = (float) get_post_meta( $post->ID, 'rodieja_monto', true );
			$sentido = (string) get_post_meta( $post->ID, 'rodieja_sentido', true );
			if ( 'egreso' === $sentido ) {
				$egresos += $monto;
			} else {
				$ingresos += $monto;
			}
		}

		$lines = array(
			__( 'Resumen Rodieja Contable (nativo WP)', 'rodieja-contable' ),
			'',
			sprintf( __( 'Vehículos publicados: %d', 'rodieja-contable' ), $counts['vehiculos'] ),
			sprintf( __( 'Repuestos publicados: %d', 'rodieja-contable' ), $counts['repuestos'] ),
			sprintf( __( 'Transacciones publicadas: %d', 'rodieja-contable' ), $counts['transacciones'] ),
			sprintf( __( 'Pedidos publicados: %d', 'rodieja-contable' ), $counts['pedidos'] ),
			sprintf( __( 'Eventos historial este mes: %d', 'rodieja-contable' ), $counts['historial_mes'] ),
			'',
			sprintf( __( 'Ingresos del mes (transacciones): %s', 'rodieja-contable' ), number_format_i18n( $ingresos, 2 ) ),
			sprintf( __( 'Egresos del mes (transacciones): %s', 'rodieja-contable' ), number_format_i18n( $egresos, 2 ) ),
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
