<?php
/**
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Providers;

use WorkshopLedger\Admin\AdminAssets;
use WorkshopLedger\Admin\AdminChrome;
use WorkshopLedger\Admin\AdminMenu;
use WorkshopLedger\Admin\CptListChrome;
use WorkshopLedger\Admin\CatalogAdminFilters;
use WorkshopLedger\Admin\CatalogListColumns;
use WorkshopLedger\Admin\CatalogMetaBoxes;
use WorkshopLedger\Admin\CriticalStockPage;
use WorkshopLedger\Admin\FinanceDashboardPage;
use WorkshopLedger\Admin\FinanceMetaBoxes;
use WorkshopLedger\Admin\InventoryAdminFilters;
use WorkshopLedger\Admin\InventoryMetaBoxes;
use WorkshopLedger\Admin\CsvExportHandler;
use WorkshopLedger\Admin\EmpleadoWpUserMetaBox;
use WorkshopLedger\Admin\EventoListFilters;
use WorkshopLedger\Admin\GalleryMetaBox;
use WorkshopLedger\Admin\PedidoComisionListColumns;
use WorkshopLedger\Admin\AudatexInsSettingsPage;
use WorkshopLedger\Admin\CantonesSettingsPage;
use WorkshopLedger\Admin\GeoLocationMetaBoxes;
use WorkshopLedger\Admin\InventorySummaryReportPage;
use WorkshopLedger\Admin\JerarquiaAdminPage;
use WorkshopLedger\Admin\PedidoImportPage;
use WorkshopLedger\Admin\PedidoMetaBoxes;
use WorkshopLedger\Admin\ToolsAdminPage;
use WorkshopLedger\Admin\ProfitabilityReportPage;
use WorkshopLedger\Admin\ReportesAdminPage;
use WorkshopLedger\Admin\RolesDocumentationPage;
use WorkshopLedger\Admin\SalesReportPage;
use WorkshopLedger\Admin\UserWorkshopProfileFields;
use WorkshopLedger\Cron\ResumenCronRegistrar;
use WorkshopLedger\Meta\EventoMetaRegistrar;
use WorkshopLedger\PostTypes\CatalogThumbnailSupport;
use WorkshopLedger\PostTypes\EventoPostTypeRegistrar;
use WorkshopLedger\Contracts\CatalogHierarchyRepositoryInterface;
use WorkshopLedger\Repositories\CatalogHierarchyRepository;
use WorkshopLedger\Services\CatalogHierarchyService;
use WorkshopLedger\Services\CsvExportService;
use WorkshopLedger\Services\HistorialRecorder;
use WorkshopLedger\Services\MonthlyResumenService;
use WorkshopLedger\Controllers\Rest\DomainStatsController;
use WorkshopLedger\Controllers\Rest\FinanceController;
use WorkshopLedger\Controllers\Rest\HealthController;
use WorkshopLedger\Controllers\Rest\PedidoController;
use WorkshopLedger\Core\Container;
use WorkshopLedger\Front\HeaderAuthLinks;
use WorkshopLedger\Front\LoginChrome;
use WorkshopLedger\Meta\MetaRegistrar;
use WorkshopLedger\PostTypes\PostTypeRegistrar;
use WorkshopLedger\Roles\RoleRegistrar;
use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Security\RequestSanitizer;
use WorkshopLedger\Security\SecurityHeaders;
use WorkshopLedger\Services\DemoSeedService;
use WorkshopLedger\Services\DomainStatsService;
use WorkshopLedger\Services\FinanceTotalsService;
use WorkshopLedger\Services\HealthService;
use WorkshopLedger\Services\AudatexInsSettingsService;
use WorkshopLedger\Services\CostaRicaCantonesService;
use WorkshopLedger\Services\DomainExportService;
use WorkshopLedger\Services\DomainImportService;
use WorkshopLedger\Services\ExecutiveDashboardService;
use WorkshopLedger\Services\InventoryReportService;
use WorkshopLedger\Services\PedidoImportService;
use WorkshopLedger\Services\ProfitabilityReportService;
use WorkshopLedger\Services\SalesReportService;
use WorkshopLedger\Taxonomies\TaxonomyRegistrar;

final class ServiceProvider {

	public function register( Container $container ): void {
		$container->set( CapabilityGate::class, static fn() => new CapabilityGate() );
		$container->set( NonceGuard::class, static fn() => new NonceGuard() );
		$container->set( RequestSanitizer::class, static fn() => new RequestSanitizer() );
		$container->set( OutputEscaper::class, static fn() => new OutputEscaper() );
		$container->set( SecurityHeaders::class, static fn() => new SecurityHeaders() );
		$container->set( HealthService::class, static fn() => new HealthService() );
		$container->set( FinanceTotalsService::class, static fn() => new FinanceTotalsService() );
		$container->set( DomainStatsService::class, static fn() => new DomainStatsService() );
		$container->set(
			CatalogHierarchyRepositoryInterface::class,
			static fn() => new CatalogHierarchyRepository()
		);
		$container->set(
			CatalogHierarchyService::class,
			static fn( Container $c ) => new CatalogHierarchyService(
				$c->get( CatalogHierarchyRepositoryInterface::class )
			)
		);
		$container->set( DemoSeedService::class, static fn() => new DemoSeedService() );
		$container->set( RoleRegistrar::class, static fn() => new RoleRegistrar() );
		$container->set( PostTypeRegistrar::class, static fn() => new PostTypeRegistrar() );
		$container->set( TaxonomyRegistrar::class, static fn() => new TaxonomyRegistrar() );
		$container->set( MetaRegistrar::class, static fn() => new MetaRegistrar() );
		$container->set( AdminAssets::class, static fn() => new AdminAssets() );
		$container->set( AdminChrome::class, static fn() => new AdminChrome() );
		$container->set(
			CptListChrome::class,
			static fn( Container $c ) => new CptListChrome(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set( HeaderAuthLinks::class, static fn() => new HeaderAuthLinks() );
		$container->set( LoginChrome::class, static fn() => new LoginChrome() );

		$container->set(
			HealthController::class,
			static fn( Container $c ) => new HealthController( $c->get( HealthService::class ) )
		);
		$container->set(
			FinanceController::class,
			static fn( Container $c ) => new FinanceController( $c->get( FinanceTotalsService::class ), $c->get( CapabilityGate::class ) )
		);
		$container->set(
			DomainStatsController::class,
			static fn( Container $c ) => new DomainStatsController( $c->get( DomainStatsService::class ) )
		);
		$container->set(
			PedidoController::class,
			static fn( Container $c ) => new PedidoController( $c->get( CapabilityGate::class ) )
		);

		$container->set(
			CriticalStockPage::class,
			static fn( Container $c ) => new CriticalStockPage( $c->get( OutputEscaper::class ), $c->get( CapabilityGate::class ) )
		);
		$container->set(
			FinanceDashboardPage::class,
			static fn( Container $c ) => new FinanceDashboardPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( FinanceTotalsService::class )
			)
		);
		$container->set(
			SalesReportService::class,
			static fn( Container $c ) => new SalesReportService( $c->get( FinanceTotalsService::class ) )
		);
		$container->set( InventoryReportService::class, static fn() => new InventoryReportService() );
		$container->set( ProfitabilityReportService::class, static fn() => new ProfitabilityReportService() );
		$container->set( AudatexInsSettingsService::class, static fn() => new AudatexInsSettingsService() );
		$container->set( CostaRicaCantonesService::class, static fn() => new CostaRicaCantonesService() );
		$container->set(
			PedidoImportService::class,
			static fn( Container $c ) => new PedidoImportService( $c->get( AudatexInsSettingsService::class ) )
		);
		$container->set( DomainExportService::class, static fn() => new DomainExportService() );
		$container->set( DomainImportService::class, static fn() => new DomainImportService() );
		$container->set(
			ExecutiveDashboardService::class,
			static fn( Container $c ) => new ExecutiveDashboardService(
				$c->get( DomainStatsService::class ),
				$c->get( FinanceTotalsService::class ),
				$c->get( InventoryReportService::class ),
				$c->get( SalesReportService::class ),
				$c->get( ProfitabilityReportService::class )
			)
		);
		$container->set(
			SalesReportPage::class,
			static fn( Container $c ) => new SalesReportPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( SalesReportService::class )
			)
		);
		$container->set(
			InventorySummaryReportPage::class,
			static fn( Container $c ) => new InventorySummaryReportPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( InventoryReportService::class )
			)
		);
		$container->set(
			ProfitabilityReportPage::class,
			static fn( Container $c ) => new ProfitabilityReportPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( ProfitabilityReportService::class )
			)
		);
		$container->set(
			PedidoImportPage::class,
			static fn( Container $c ) => new PedidoImportPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( NonceGuard::class ),
				$c->get( PedidoImportService::class )
			)
		);
		$container->set(
			AudatexInsSettingsPage::class,
			static fn( Container $c ) => new AudatexInsSettingsPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( NonceGuard::class ),
				$c->get( AudatexInsSettingsService::class )
			)
		);
		$container->set(
			CantonesSettingsPage::class,
			static fn( Container $c ) => new CantonesSettingsPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( NonceGuard::class ),
				$c->get( CostaRicaCantonesService::class )
			)
		);
		$container->set(
			ToolsAdminPage::class,
			static fn( Container $c ) => new ToolsAdminPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( NonceGuard::class ),
				$c->get( DomainExportService::class ),
				$c->get( DomainImportService::class )
			)
		);
		$container->set(
			GeoLocationMetaBoxes::class,
			static fn( Container $c ) => new GeoLocationMetaBoxes(
				$c->get( NonceGuard::class ),
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( CostaRicaCantonesService::class )
			)
		);
		$container->set(
			JerarquiaAdminPage::class,
			static fn( Container $c ) => new JerarquiaAdminPage(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( CatalogHierarchyService::class )
			)
		);
		$container->set(
			AdminMenu::class,
			static fn( Container $c ) => new AdminMenu(
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class ),
				$c->get( CriticalStockPage::class ),
				$c->get( FinanceDashboardPage::class ),
				$c->get( SalesReportPage::class ),
				$c->get( InventorySummaryReportPage::class ),
				$c->get( ProfitabilityReportPage::class ),
				$c->get( ReportesAdminPage::class ),
				$c->get( RolesDocumentationPage::class ),
				$c->get( ExecutiveDashboardService::class ),
				$c->get( PedidoImportPage::class ),
				$c->get( AudatexInsSettingsPage::class ),
				$c->get( CantonesSettingsPage::class ),
				$c->get( ToolsAdminPage::class ),
				$c->get( JerarquiaAdminPage::class )
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
			FinanceMetaBoxes::class,
			static fn( Container $c ) => new FinanceMetaBoxes(
				$c->get( NonceGuard::class ),
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set(
			PedidoMetaBoxes::class,
			static fn( Container $c ) => new PedidoMetaBoxes(
				$c->get( NonceGuard::class ),
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class )
			)
		);

		$container->set( EventoPostTypeRegistrar::class, static fn() => new EventoPostTypeRegistrar() );
		$container->set( EventoMetaRegistrar::class, static fn() => new EventoMetaRegistrar() );
		$container->set( HistorialRecorder::class, static fn() => new HistorialRecorder() );
		$container->set(
			EventoListFilters::class,
			static fn( Container $c ) => new EventoListFilters( $c->get( OutputEscaper::class ) )
		);
		$container->set( CsvExportService::class, static fn() => new CsvExportService() );
		$container->set(
			CsvExportHandler::class,
			static fn( Container $c ) => new CsvExportHandler( $c->get( CsvExportService::class ) )
		);
		$container->set( MonthlyResumenService::class, static fn() => new MonthlyResumenService() );
		$container->set(
			ResumenCronRegistrar::class,
			static fn( Container $c ) => new ResumenCronRegistrar( $c->get( MonthlyResumenService::class ) )
		);
		$container->set(
			ReportesAdminPage::class,
			static fn( Container $c ) => new ReportesAdminPage(
				$c->get( OutputEscaper::class ),
				$c->get( NonceGuard::class ),
				$c->get( MonthlyResumenService::class )
			)
		);
		$container->set( CatalogThumbnailSupport::class, static fn() => new CatalogThumbnailSupport() );
		$container->set(
			GalleryMetaBox::class,
			static fn( Container $c ) => new GalleryMetaBox(
				$c->get( NonceGuard::class ),
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set(
			PedidoComisionListColumns::class,
			static fn( Container $c ) => new PedidoComisionListColumns( $c->get( OutputEscaper::class ) )
		);
		$container->set(
			EmpleadoWpUserMetaBox::class,
			static fn( Container $c ) => new EmpleadoWpUserMetaBox(
				$c->get( NonceGuard::class ),
				$c->get( OutputEscaper::class ),
				$c->get( CapabilityGate::class )
			)
		);
		$container->set(
			UserWorkshopProfileFields::class,
			static fn( Container $c ) => new UserWorkshopProfileFields( $c->get( OutputEscaper::class ) )
		);
		$container->set(
			RolesDocumentationPage::class,
			static fn( Container $c ) => new RolesDocumentationPage( $c->get( OutputEscaper::class ) )
		);
	}
}
