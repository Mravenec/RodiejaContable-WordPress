<?php
/**
 * Item persistence contract.
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Contracts;

interface ItemRepositoryInterface {

	/**
	 * @return array{id:int,title:string,created_at:string}|null
	 */
	public function find_by_id( int $id ): ?array;

	/**
	 * @return list<array{id:int,title:string,created_at:string}>
	 */
	public function find_all( int $limit = 20, int $offset = 0 ): array;

	/**
	 * @return array{id:int,title:string,created_at:string}|null
	 */
	public function create( string $title ): ?array;

	public function delete( int $id ): bool;
}
