<?php
/**
 * Inventario resumen — WP_Query + post meta (native).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class InventoryReportService {

	public const CRITICAL_STOCK = 1;

	/**
	 * @return array{repuestos: int, unidades: int, valor_inventario: float, criticos: int, vehiculos: int}
	 */
	public function summary(): array {
		$ids = get_posts(
			array(
				'post_type'      => 'rodieja_repuesto',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$unidades = 0;
		$valor    = 0.0;
		$criticos = 0;
		foreach ( $ids as $id ) {
			$stock  = (int) get_post_meta( (int) $id, 'rodieja_stock', true );
			$precio = (float) get_post_meta( (int) $id, 'rodieja_precio', true );
			$unidades += $stock;
			$valor    += $stock * $precio;
			if ( $stock <= self::CRITICAL_STOCK ) {
				++$criticos;
			}
		}
		$vehiculos = (int) ( wp_count_posts( 'rodieja_vehiculo' )->publish ?? 0 );
		return array(
			'repuestos'         => count( $ids ),
			'unidades'          => $unidades,
			'valor_inventario'  => $valor,
			'criticos'          => $criticos,
			'vehiculos'         => $vehiculos,
		);
	}
}
