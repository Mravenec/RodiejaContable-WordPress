<?php
/**
 * Wires constructor DI for the plugin graph.
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Providers;

use Harness\Plugin\Contracts\ItemRepositoryInterface;
use Harness\Plugin\Controllers\AdminController;
use Harness\Plugin\Controllers\Rest\HealthController;
use Harness\Plugin\Controllers\Rest\ItemController;
use Harness\Plugin\Core\Container;
use Harness\Plugin\Repositories\ItemRepository;
use Harness\Plugin\Security\CapabilityGate;
use Harness\Plugin\Security\NonceGuard;
use Harness\Plugin\Security\OutputEscaper;
use Harness\Plugin\Security\RequestSanitizer;
use Harness\Plugin\Security\SecurityHeaders;
use Harness\Plugin\Services\HealthService;
use Harness\Plugin\Services\ItemService;

final class ServiceProvider {

	public function register( Container $container ): void {
		$container->set( CapabilityGate::class, static fn() => new CapabilityGate() );
		$container->set( NonceGuard::class, static fn() => new NonceGuard() );
		$container->set( RequestSanitizer::class, static fn() => new RequestSanitizer() );
		$container->set( OutputEscaper::class, static fn() => new OutputEscaper() );
		$container->set( SecurityHeaders::class, static fn() => new SecurityHeaders() );

		$container->set(
			ItemRepositoryInterface::class,
			static fn() => new ItemRepository()
		);

		$container->set(
			ItemService::class,
			static fn( Container $c ) => new ItemService(
				$c->get( ItemRepositoryInterface::class ),
				$c->get( RequestSanitizer::class )
			)
		);

		$container->set( HealthService::class, static fn() => new HealthService() );

		$container->set(
			HealthController::class,
			static fn( Container $c ) => new HealthController( $c->get( HealthService::class ) )
		);

		$container->set(
			ItemController::class,
			static fn( Container $c ) => new ItemController(
				$c->get( ItemService::class ),
				$c->get( CapabilityGate::class )
			)
		);

		$container->set(
			AdminController::class,
			static fn( Container $c ) => new AdminController(
				$c->get( ItemService::class ),
				$c->get( CapabilityGate::class ),
				$c->get( NonceGuard::class ),
				$c->get( RequestSanitizer::class ),
				$c->get( OutputEscaper::class )
			)
		);
	}
}
