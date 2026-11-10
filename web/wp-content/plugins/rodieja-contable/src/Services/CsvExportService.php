<?php
/**
 * CSV export via WP_Query (native posts + meta).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class CsvExportService {

	/**
	 * @return resource|null
	 */
	public function open_stream() {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		return fopen( 'php://output', 'w' );
	}

	public function export_vehiculos( $stream ): void {
		fputcsv( $stream, array( 'id', 'title', 'codigo', 'anio', 'precio_compra', 'fecha_ingreso' ) );
		foreach ( $this->posts( 'rodieja_vehiculo' ) as $post ) {
			fputcsv(
				$stream,
				array(
					$post->ID,
					$post->post_title,
					get_post_meta( $post->ID, 'rodieja_codigo', true ),
					get_post_meta( $post->ID, 'rodieja_anio', true ),
					get_post_meta( $post->ID, 'rodieja_precio_compra', true ),
					get_post_meta( $post->ID, 'rodieja_fecha_ingreso', true ),
				)
			);
		}
	}

	public function export_repuestos( $stream ): void {
		fputcsv( $stream, array( 'id', 'title', 'vehiculo_id', 'precio', 'stock' ) );
		foreach ( $this->posts( 'rodieja_repuesto' ) as $post ) {
			fputcsv(
				$stream,
				array(
					$post->ID,
					$post->post_title,
					get_post_meta( $post->ID, 'rodieja_vehiculo_id', true ),
					get_post_meta( $post->ID, 'rodieja_precio', true ),
					get_post_meta( $post->ID, 'rodieja_stock', true ),
				)
			);
		}
	}

	public function export_transacciones( $stream ): void {
		fputcsv( $stream, array( 'id', 'title', 'monto', 'fecha', 'sentido', 'vehiculo_id', 'empleado_id' ) );
		foreach ( $this->posts( 'rodieja_transaccion' ) as $post ) {
			fputcsv(
				$stream,
				array(
					$post->ID,
					$post->post_title,
					get_post_meta( $post->ID, 'rodieja_monto', true ),
					get_post_meta( $post->ID, 'rodieja_fecha', true ),
					get_post_meta( $post->ID, 'rodieja_sentido', true ),
					get_post_meta( $post->ID, 'rodieja_vehiculo_id', true ),
					get_post_meta( $post->ID, 'rodieja_empleado_id', true ),
				)
			);
		}
	}

	/** @return \WP_Post[] */
	private function posts( string $type ): array {
		$q = new \WP_Query(
			array(
				'post_type'      => $type,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		return $q->posts;
	}
}
