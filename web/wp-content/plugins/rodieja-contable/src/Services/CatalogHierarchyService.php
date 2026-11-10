<?php
/**
 * Catalog hierarchy use-case — delegates to repository.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

use Rodieja\Contable\Contracts\CatalogHierarchyRepositoryInterface;

final class CatalogHierarchyService {

	public function __construct(
		private CatalogHierarchyRepositoryInterface $catalog
	) {}

	/**
	 * @return list<array<string, mixed>>
	 */
	public function tree(): array {
		return $this->catalog->tree();
	}
}
