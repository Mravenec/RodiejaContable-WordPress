<?php
/**
 * Rentabilidad vehicular — costos vs recuperado + valor repuestos ligados.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class ProfitabilityReportService {

	/**
	 * @return array{
	 *   vehiculos: int,
	 *   inversion: float,
	 *   recuperado: float,
	 *   margen_bruto: float,
	 *   valor_repuestos_stock: float
	 * }
	 */
	public function summary(): array {
		$vehicle_ids = get_posts(
			array(
				'post_type'      => 'rodieja_vehiculo',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$inversion  = 0.0;
		$recuperado = 0.0;
		foreach ( $vehicle_ids as $vid ) {
			$inversion += (float) get_post_meta( (int) $vid, 'rodieja_precio_compra', true );
			$inversion += (float) get_post_meta( (int) $vid, 'rodieja_costo_grua', true );
			$inversion += (float) get_post_meta( (int) $vid, 'rodieja_comisiones', true );
			$recuperado += (float) get_post_meta( (int) $vid, 'rodieja_costo_recuperado', true );
		}
		$repuesto_ids = get_posts(
			array(
				'post_type'      => 'rodieja_repuesto',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$valor_stock = 0.0;
		foreach ( $repuesto_ids as $rid ) {
			$stock  = (int) get_post_meta( (int) $rid, 'rodieja_stock', true );
			$precio = (float) get_post_meta( (int) $rid, 'rodieja_precio', true );
			$valor_stock += $stock * $precio;
		}
		return array(
			'vehiculos'             => count( $vehicle_ids ),
			'inversion'             => $inversion,
			'recuperado'            => $recuperado,
			'margen_bruto'          => $recuperado + $valor_stock - $inversion,
			'valor_repuestos_stock' => $valor_stock,
		);
	}
}
