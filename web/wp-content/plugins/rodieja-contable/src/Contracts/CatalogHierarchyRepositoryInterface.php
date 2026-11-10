<?php
/**
 * Read-only catalog tree: marca → modelo → generación → vehículo.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Contracts;

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
	 *         edit_url:string
	 *       }>
	 *     }>
	 *   }>
	 * }>
	 */
	public function tree(): array;
}
