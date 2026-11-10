<?php
/**
 * Read-only catalog tree: marca → modelo → generación → vehículo.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Contracts;

interface CatalogHierarchyRepositoryInterface {

	/**
	 * @return list<array{
	 *   id:int,
	 *   title:string,
	 *   modelos:list<array{
	 *     id:int,
	 *     title:string,
	 *     generaciones:list<array{
	 *       id:int,
	 *       title:string,
	 *       anio_inicio:int|float|string,
	 *       anio_fin:int|float|string,
	 *       vehiculos:list<array{
	 *         id:int,
	 *         title:string,
	 *         codigo:string,
	 *         anio:int|float|string,
	 *         edit_url:string,
	 *         thumb_url:string,
	 *         estado:string,
	 *         fecha_ingreso:string,
	 *         precio_compra:float,
	 *         costo_grua:float,
	 *         comisiones:float,
	 *         costo_recuperado:float,
	 *         inversion_total:float,
	 *         costo_pendiente:float,
	 *         repuestos:list<array{id:int,title:string,precio:float,stock:int,edit_url:string}>,
	 *         transacciones:list<array{id:int,title:string,monto:float,fecha:string,sentido:string,edit_url:string}>
	 *       }>
	 *     }>
	 *   }>
	 * }>
	 */
	public function tree(): array;
}
