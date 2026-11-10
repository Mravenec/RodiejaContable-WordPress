<?php
/**
 * Plugin bootstrap — DI + native registrars.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Core;

use Rodieja\Contable\Admin\AdminAssets;
use Rodieja\Contable\Admin\AdminChrome;
use Rodieja\Contable\Admin\AdminMenu;
use Rodieja\Contable\Admin\CatalogAdminFilters;
use Rodieja\Contable\Admin\CatalogListColumns;
use Rodieja\Contable\Admin\CatalogMetaBoxes;
use Rodieja\Contable\Admin\CriticalStockPage;
use Rodieja\Contable\Admin\FinanceDashboardPage;
use Rodieja\Contable\Admin\FinanceMetaBoxes;
use Rodieja\Contable\Admin\InventoryAdminFilters;
use Rodieja\Contable\Admin\InventoryMetaBoxes;
use Rodieja\Contable\Admin\CsvExportHandler;
use Rodieja\Contable\Admin\EmpleadoWpUserMetaBox;
use Rodieja\Contable\Admin\EventoListFilters;
use Rodieja\Contable\Admin\GalleryMetaBox;
use Rodieja\Contable\Admin\GeoLocationMetaBoxes;
use Rodieja\Contable\Admin\InventorySummaryReportPage;
use Rodieja\Contable\Admin\PedidoImportPage;
use Rodieja\Contable\Admin\ToolsAdminPage;
use Rodieja\Contable\Admin\PedidoComisionListColumns;
use Rodieja\Contable\Admin\PedidoMetaBoxes;
use Rodieja\Contable\Admin\ProfitabilityReportPage;
use Rodieja\Contable\Admin\ReportesAdminPage;
use Rodieja\Contable\Admin\SalesReportPage;
use Rodieja\Contable\Admin\RolesDocumentationPage;
use Rodieja\Contable\Admin\UserRodiejaProfileFields;
use Rodieja\Contable\Cron\ResumenCronRegistrar;
use Rodieja\Contable\Meta\EventoMetaRegistrar;
use Rodieja\Contable\PostTypes\CatalogThumbnailSupport;
use Rodieja\Contable\PostTypes\EventoPostTypeRegistrar;
use Rodieja\Contable\Services\HistorialRecorder;
use Rodieja\Contable\Controllers\Rest\DomainStatsController;
use Rodieja\Contable\Controllers\Rest\FinanceController;
use Rodieja\Contable\Controllers\Rest\HealthController;
use Rodieja\Contable\Controllers\Rest\PedidoController;
use Rodieja\Contable\Front\HeaderAuthLinks;
use Rodieja\Contable\Front\LoginChrome;
use Rodieja\Contable\Meta\MetaRegistrar;
use Rodieja\Contable\PostTypes\PostTypeRegistrar;
use Rodieja\Contable\Providers\ServiceProvider;
use Rodieja\Contable\Roles\RoleRegistrar;
use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\SecurityHeaders;
use Rodieja\Contable\Taxonomies\TaxonomyRegistrar;

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
		$this->container->get( TaxonomyRegistrar::class )->register();
		$this->container->get( MetaRegistrar::class )->register();
		$this->container->get( AdminAssets::class )->register();
		$this->container->get( AdminChrome::class )->register();
		$this->container->get( HeaderAuthLinks::class )->register();
		$this->container->get( LoginChrome::class )->register();
		$this->container->get( AdminMenu::class )->register();
		$this->container->get( CatalogMetaBoxes::class )->register();
		$this->container->get( CatalogListColumns::class )->register();
		$this->container->get( CatalogAdminFilters::class )->register();
		$this->container->get( InventoryMetaBoxes::class )->register();
		$this->container->get( InventoryAdminFilters::class )->register();
		$this->container->get( FinanceMetaBoxes::class )->register();
		$this->container->get( CriticalStockPage::class )->register();
		$this->container->get( FinanceDashboardPage::class )->register();
		$this->container->get( PedidoMetaBoxes::class )->register();
		$this->container->get( EventoPostTypeRegistrar::class )->register();
		$this->container->get( EventoMetaRegistrar::class )->register();
		$this->container->get( HistorialRecorder::class )->register();
		$this->container->get( EventoListFilters::class )->register();
		$this->container->get( CsvExportHandler::class )->register();
		$this->container->get( ReportesAdminPage::class )->register();
		$this->container->get( SalesReportPage::class )->register();
		$this->container->get( InventorySummaryReportPage::class )->register();
		$this->container->get( ProfitabilityReportPage::class )->register();
		$this->container->get( ResumenCronRegistrar::class )->register();
		$this->container->get( CatalogThumbnailSupport::class )->register();
		$this->container->get( GalleryMetaBox::class )->register();
		$this->container->get( PedidoComisionListColumns::class )->register();
		$this->container->get( EmpleadoWpUserMetaBox::class )->register();
		$this->container->get( UserRodiejaProfileFields::class )->register();
		$this->container->get( RolesDocumentationPage::class )->register();
		$this->container->get( GeoLocationMetaBoxes::class )->register();
		$this->container->get( PedidoImportPage::class )->register();
		$this->container->get( ToolsAdminPage::class )->register();
		$this->container->get( SecurityHeaders::class )->register();

		add_action( 'rest_api_init', array( $this, 'register_rest' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'maybe_upgrade_caps' ) );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	public function register_rest(): void {
		$this->container->get( HealthController::class )->register_routes();
		$this->container->get( FinanceController::class )->register_routes();
		$this->container->get( DomainStatsController::class )->register_routes();
		$this->container->get( PedidoController::class )->register_routes();
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'rodieja-contable',
			false,
			dirname( RODIEJA_PLUGIN_BASENAME ) . '/languages'
		);
	}

	public function maybe_upgrade_caps(): void {
		$role = get_role( 'administrator' );
		if ( $role instanceof \WP_Role && ! $role->has_cap( CapabilityGate::MANAGE ) ) {
			$role->add_cap( CapabilityGate::MANAGE );
		}
		$registrar = $this->container->get( RoleRegistrar::class );
		$admin     = get_role( 'administrator' );
		if ( $admin instanceof \WP_Role ) {
			foreach ( $registrar->post_caps() as $cap ) {
				if ( ! $admin->has_cap( $cap ) ) {
					$admin->add_cap( $cap );
				}
			}
		}
	}

	/**
	 * @param string[] $caps
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
