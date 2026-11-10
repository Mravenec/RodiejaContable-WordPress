<?php
/**
 * Plugin bootstrap.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Core;

use Rodieja\Contable\Admin\AdminMenu;
use Rodieja\Contable\Admin\MetaBoxRegistrar;
use Rodieja\Contable\Meta\MetaRegistrar;
use Rodieja\Contable\PostTypes\PostTypeRegistrar;
use Rodieja\Contable\Providers\ServiceProvider;
use Rodieja\Contable\Roles\RoleRegistrar;
use Rodieja\Contable\Services\HealthService;

final class Plugin {

	private static ?self $instance = null;

	private Container $container;

	private function __construct() {
		$this->container = new Container();
		( new ServiceProvider() )->register( $this->container );
	}

	public static function instance(): self {
		return self::$instance ??= new self();
	}

	public function container(): Container {
		return $this->container;
	}

	public function boot(): void {
		$this->container->get( RoleRegistrar::class )->register();
		$this->container->get( PostTypeRegistrar::class )->register();
		$this->container->get( MetaRegistrar::class )->register();
		$this->container->get( AdminMenu::class )->register();
		$this->container->get( MetaBoxRegistrar::class )->register();

		add_action( 'rest_api_init', array( $this, 'register_health' ) );
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	public function register_health(): void {
		$health = $this->container->get( HealthService::class );
		register_rest_route(
			'rodieja/v1',
			'/health',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => static fn() => rest_ensure_response( $health->status() ),
				'permission_callback' => '__return_true',
			)
		);
	}
}
