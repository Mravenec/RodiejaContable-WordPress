<?php
/**
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Providers;

use Rodieja\Contable\Admin\AdminMenu;
use Rodieja\Contable\Admin\CatalogAdminFilters;
use Rodieja\Contable\Admin\CatalogListColumns;
use Rodieja\Contable\Admin\CatalogMetaBoxes;
use Rodieja\Contable\Admin\CriticalStockPage;
use Rodieja\Contable\Admin\FinanceDashboardPage;
use Rodieja\Contable\Admin\FinanceMetaBoxes;
use Rodieja\Contable\Admin\InventoryAdminFilters;
use Rodieja\Contable\Admin\InventoryMetaBoxes;
use Rodieja\Contable\Controllers\Rest\DomainStatsController;
use Rodieja\Contable\Controllers\Rest\FinanceController;
use Rodieja\Contable\Controllers\Rest\HealthController;
use Rodieja\Contable\Core\Container;
use Rodieja\Contable\Meta\MetaRegistrar;
use Rodieja\Contable\PostTypes\PostTypeRegistrar;
use Rodieja\Contable\Roles\RoleRegistrar;
use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Security\RequestSanitizer;
use Rodieja\Contable\Security\SecurityHeaders;
use Rodieja\Contable\Services\DemoSeedService;
use Rodieja\Contable\Services\DomainStatsService;
use Rodieja\Contable\Services\FinanceTotalsService;
use Rodieja\Contable\Services\HealthService;
use Rodieja\Contable\Taxonomies\TaxonomyRegistrar;

final class ServiceProvider {

	public function register( Container $container ): void {
		$container->set( CapabilityGate::class, static fn() => new CapabilityGate() );
		$container->set( NonceGuard::class, static fn() => new NonceGuard() );
		$container->set( RequestSanitizer::class, static fn() => new RequestSanitizer() );
		$container->set( OutputEscaper::class, static fn() => new OutputEscaper() );
		$container->set( SecurityHeaders::class, static fn() => new SecurityHeaders() );
		$container->set( HealthService::class, static fn() => new HealthService() );
		$container->set( DemoSeedService::class, static fn() => new DemoSeedService() );
		$container->set( DomainStatsService::class, static fn() => new DomainStatsService() );
		$container->set( FinanceTotalsService::class, static fn() => new FinanceTotalsService() );
		$container->set( RoleRegistrar::class, static fn() => new RoleRegistrar() );
		$container->set( PostTypeRegistrar::class, static fn() => new PostTypeRegistrar() );
		$container->set( TaxonomyRegistrar::class, static fn() => new TaxonomyRegistrar() );
		$container->set( MetaRegistrar::class, static fn() => new MetaRegistrar() );
		$container->set(
			HealthController::class,
			static fn( Container $c ) => new HealthController( $c->get( HealthService::class ) )
		);
		$container->set(
			DomainStatsController::class,
			static fn( Container $c ) => new DomainStatsController( $c->get( DomainStatsService::class ) )
		);
		$container->set(
			FinanceController::class,
			static fn( Container $c ) => new FinanceController(
				$c->get( FinanceTotalsService::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set(
			AdminMenu::class,
			static fn( Container $c ) => new AdminMenu(
				$c->get( OutputEscaper::class ),
				$c->get( NonceGuard::class ),
				$c->get( DemoSeedService::class ),
				$c->get( DomainStatsService::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set(
			CatalogMetaBoxes::class,
			static fn( Container $c ) => new CatalogMetaBoxes(
				$c->get( NonceGuard::class ),
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set(
			CatalogListColumns::class,
			static fn( Container $c ) => new CatalogListColumns( $c->get( OutputEscaper::class ) )
		);
		$container->set(
			CatalogAdminFilters::class,
			static fn( Container $c ) => new CatalogAdminFilters( $c->get( OutputEscaper::class ) )
		);
		$container->set(
			InventoryMetaBoxes::class,
			static fn( Container $c ) => new InventoryMetaBoxes(
				$c->get( NonceGuard::class ),
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set(
			InventoryAdminFilters::class,
			static fn( Container $c ) => new InventoryAdminFilters( $c->get( OutputEscaper::class ) )
		);
		$container->set(
			CriticalStockPage::class,
			static fn( Container $c ) => new CriticalStockPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set(
			FinanceMetaBoxes::class,
			static fn( Container $c ) => new FinanceMetaBoxes(
				$c->get( NonceGuard::class ),
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set(
			FinanceDashboardPage::class,
			static fn( Container $c ) => new FinanceDashboardPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( FinanceTotalsService::class )
			)
		);
	}
}
