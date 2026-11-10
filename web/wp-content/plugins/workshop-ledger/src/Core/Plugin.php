<?php
/**
 * Plugin bootstrap — DI + native registrars.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Core;

use WorkshopLedger\Admin\AdminAssets;
use WorkshopLedger\Admin\AdminChrome;
use WorkshopLedger\Admin\AdminMenu;
use WorkshopLedger\Admin\CptListChrome;
use WorkshopLedger\Admin\CatalogAdminFilters;
use WorkshopLedger\Admin\CatalogListColumns;
use WorkshopLedger\Admin\CatalogMetaBoxes;
use WorkshopLedger\Admin\CriticalStockPage;
use WorkshopLedger\Admin\JerarquiaAdminPage;
use WorkshopLedger\Admin\FinanceDashboardPage;
use WorkshopLedger\Admin\FinanceMetaBoxes;
use WorkshopLedger\Admin\InventoryAdminFilters;
use WorkshopLedger\Admin\InventoryMetaBoxes;
use WorkshopLedger\Admin\CsvExportHandler;
use WorkshopLedger\Admin\EmpleadoWpUserMetaBox;
use WorkshopLedger\Admin\EventoListFilters;
use WorkshopLedger\Admin\GalleryMetaBox;
use WorkshopLedger\Admin\GeoLocationMetaBoxes;
use WorkshopLedger\Admin\InventorySummaryReportPage;
use WorkshopLedger\Admin\PedidoImportPage;
use WorkshopLedger\Admin\ToolsAdminPage;
use WorkshopLedger\Admin\PedidoComisionListColumns;
use WorkshopLedger\Admin\PedidoMetaBoxes;
use WorkshopLedger\Admin\ProfitabilityReportPage;
use WorkshopLedger\Admin\ReportesAdminPage;
use WorkshopLedger\Admin\SalesReportPage;
use WorkshopLedger\Admin\RolesDocumentationPage;
use WorkshopLedger\Admin\UserWorkshopProfileFields;
use WorkshopLedger\Cron\ResumenCronRegistrar;
use WorkshopLedger\Meta\EventoMetaRegistrar;
use WorkshopLedger\PostTypes\CatalogThumbnailSupport;
use WorkshopLedger\PostTypes\EventoPostTypeRegistrar;
use WorkshopLedger\Services\HistorialRecorder;
use WorkshopLedger\Controllers\Rest\DomainStatsController;
use WorkshopLedger\Controllers\Rest\FinanceController;
use WorkshopLedger\Controllers\Rest\HealthController;
use WorkshopLedger\Controllers\Rest\PedidoController;
use WorkshopLedger\Front\HeaderAuthLinks;
use WorkshopLedger\Front\LoginChrome;
use WorkshopLedger\Meta\MetaRegistrar;
use WorkshopLedger\PostTypes\PostTypeRegistrar;
use WorkshopLedger\Providers\ServiceProvider;
use WorkshopLedger\Roles\RoleRegistrar;
use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\SecurityHeaders;
use WorkshopLedger\Taxonomies\TaxonomyRegistrar;

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
		BrandMigrate::maybe_run();
		$this->container->get( RoleRegistrar::class )->register();
		$this->container->get( PostTypeRegistrar::class )->register();
		$this->container->get( TaxonomyRegistrar::class )->register();
		$this->container->get( MetaRegistrar::class )->register();
		$this->container->get( AdminAssets::class )->register();
		$this->container->get( AdminChrome::class )->register();
		$this->container->get( CptListChrome::class )->register();
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
		$this->container->get( JerarquiaAdminPage::class )->register();
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
		$this->container->get( UserWorkshopProfileFields::class )->register();
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
			'workshop-ledger',
			false,
			dirname( WL_PLUGIN_BASENAME ) . '/languages'
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
