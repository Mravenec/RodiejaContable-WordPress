<?php
/**
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Providers;

use Rodieja\Contable\Admin\AdminMenu;
use Rodieja\Contable\Controllers\Rest\DomainStatsController;
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
			AdminMenu::class,
			static fn( Container $c ) => new AdminMenu(
				$c->get( OutputEscaper::class ),
				$c->get( NonceGuard::class ),
				$c->get( DemoSeedService::class ),
				$c->get( DomainStatsService::class ),
				$c->get( CapabilityGate::class )
			)
		);
	}
}
