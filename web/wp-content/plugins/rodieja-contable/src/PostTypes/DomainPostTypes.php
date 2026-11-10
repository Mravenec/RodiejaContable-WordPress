<?php
/**
 * Domain CPT slugs tracked for historial and exports.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\PostTypes;

final class DomainPostTypes {

	public const EVENTO = 'rodieja_evento';

	/** @return list<string> */
	public static function historial_tracked(): array {
		return array(
			'rodieja_marca',
			'rodieja_modelo',
			'rodieja_generacion',
			'rodieja_vehiculo',
			'rodieja_repuesto',
			'rodieja_empleado',
			'rodieja_transaccion',
			'rodieja_pedido',
			'rodieja_pedido_item',
		);
	}

	/** @return list<string> */
	public static function exportable(): array {
		return self::historial_tracked();
	}
}
