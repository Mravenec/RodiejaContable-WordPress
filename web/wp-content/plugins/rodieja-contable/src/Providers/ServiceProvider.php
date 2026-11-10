<?php
/**
 * DI wiring.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Providers;

use Rodieja\Contable\Admin\AdminMenu;
use Rodieja\Contable\Admin\MetaBoxRegistrar;
use Rodieja\Contable\Core\Container;
use Rodieja\Contable\Meta\MetaRegistrar;
use Rodieja\Contable\PostTypes\PostTypeRegistrar;
use Rodieja\Contable\Roles\RoleRegistrar;
use Rodieja\Contable\Services\DemoSeedService;
use Rodieja\Contable\Services\HealthService;

final class ServiceProvider {

	public function register( Container $container ): void {
		$container->set( RoleRegistrar::class, static fn() => new RoleRegistrar() );
		$container->set( PostTypeRegistrar::class, static fn() => new PostTypeRegistrar() );
		$container->set( MetaRegistrar::class, static fn() => new MetaRegistrar() );
		$container->set( AdminMenu::class, static fn() => new AdminMenu() );
		$container->set( MetaBoxRegistrar::class, static fn() => new MetaBoxRegistrar() );
		$container->set( HealthService::class, static fn() => new HealthService() );
		$container->set( DemoSeedService::class, static fn() => new DemoSeedService() );
	}
}
