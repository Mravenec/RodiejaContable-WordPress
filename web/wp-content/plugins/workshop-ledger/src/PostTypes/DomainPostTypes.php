<?php
/**
 * Domain CPT slugs tracked for historial and exports.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\PostTypes;

final class DomainPostTypes {

	public const EVENTO = 'wl_evento';

	/** @return list<string> */
	public static function historial_tracked(): array {
		return array(
			'wl_marca',
			'wl_modelo',
			'wl_generacion',
			'wl_vehiculo',
			'wl_repuesto',
			'wl_empleado',
			'wl_transaccion',
			'wl_pedido',
			'wl_pedido_item',
		);
	}

	/** @return list<string> */
	public static function exportable(): array {
		return self::historial_tracked();
	}
}
