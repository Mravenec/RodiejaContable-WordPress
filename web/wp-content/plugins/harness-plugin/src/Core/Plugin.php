<?php
/**
 * Plugin bootstrap — registers hooks after DI wiring.
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Core;

use Harness\Plugin\Controllers\AdminController;
use Harness\Plugin\Controllers\Rest\HealthController;
use Harness\Plugin\Controllers\Rest\ItemController;
use Harness\Plugin\Providers\ServiceProvider;
use Harness\Plugin\Security\CapabilityGate;
use Harness\Plugin\Security\SecurityHeaders;

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
		add_action( 'rest_api_init', array( $this, 'register_rest' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'maybe_upgrade_caps' ) );

		$this->container->get( AdminController::class )->register();
		$this->container->get( SecurityHeaders::class )->register();

		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	public function register_rest(): void {
		$this->container->get( HealthController::class )->register_routes();
		$this->container->get( ItemController::class )->register_routes();
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'harness-plugin',
			false,
			dirname( HARNESS_PLUGIN_BASENAME ) . '/languages'
		);
	}

	public function maybe_upgrade_caps(): void {
		$role = get_role( 'administrator' );
		if ( $role instanceof \WP_Role && ! $role->has_cap( CapabilityGate::MANAGE ) ) {
			$role->add_cap( CapabilityGate::MANAGE );
		}
	}

	/**
	 * Allow manage_options holders to satisfy manage_harness_plugin.
	 *
	 * @param string[] $caps
	 * @param string   $cap
	 * @param int      $user_id
	 * @param array    $args
	 * @return string[]
	 */
	public function map_meta_cap( array $caps, string $cap, int $user_id, array $args ): array {
		unset( $user_id, $args );
		if ( CapabilityGate::MANAGE === $cap ) {
			return array( 'manage_options' );
		}
		return $caps;
	}
}
