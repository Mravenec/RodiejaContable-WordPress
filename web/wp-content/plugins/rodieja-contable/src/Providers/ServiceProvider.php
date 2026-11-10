<?php
/**
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Providers;

use Rodieja\Contable\Admin\AdminAssets;
use Rodieja\Contable\Admin\AdminChrome;
use Rodieja\Contable\Admin\AdminMenu;
use Rodieja\Contable\Admin\CptListChrome;
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
use Rodieja\Contable\Admin\PedidoComisionListColumns;
use Rodieja\Contable\Admin\AudatexInsSettingsPage;
use Rodieja\Contable\Admin\CantonesSettingsPage;
use Rodieja\Contable\Admin\GeoLocationMetaBoxes;
use Rodieja\Contable\Admin\InventorySummaryReportPage;
use Rodieja\Contable\Admin\JerarquiaAdminPage;
use Rodieja\Contable\Admin\PedidoImportPage;
use Rodieja\Contable\Admin\PedidoMetaBoxes;
use Rodieja\Contable\Admin\ToolsAdminPage;
use Rodieja\Contable\Admin\ProfitabilityReportPage;
use Rodieja\Contable\Admin\ReportesAdminPage;
use Rodieja\Contable\Admin\RolesDocumentationPage;
use Rodieja\Contable\Admin\SalesReportPage;
use Rodieja\Contable\Admin\UserRodiejaProfileFields;
use Rodieja\Contable\Cron\ResumenCronRegistrar;
use Rodieja\Contable\Meta\EventoMetaRegistrar;
use Rodieja\Contable\PostTypes\CatalogThumbnailSupport;
use Rodieja\Contable\PostTypes\EventoPostTypeRegistrar;
use Rodieja\Contable\Contracts\CatalogHierarchyRepositoryInterface;
use Rodieja\Contable\Repositories\CatalogHierarchyRepository;
use Rodieja\Contable\Services\CatalogHierarchyService;
use Rodieja\Contable\Services\CsvExportService;
use Rodieja\Contable\Services\HistorialRecorder;
use Rodieja\Contable\Services\MonthlyResumenService;
use Rodieja\Contable\Controllers\Rest\DomainStatsController;
use Rodieja\Contable\Controllers\Rest\FinanceController;
use Rodieja\Contable\Controllers\Rest\HealthController;
use Rodieja\Contable\Controllers\Rest\PedidoController;
use Rodieja\Contable\Core\Container;
use Rodieja\Contable\Front\HeaderAuthLinks;
use Rodieja\Contable\Front\LoginChrome;
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
use Rodieja\Contable\Services\AudatexInsSettingsService;
use Rodieja\Contable\Services\CostaRicaCantonesService;
use Rodieja\Contable\Services\DomainExportService;
use Rodieja\Contable\Services\DomainImportService;
use Rodieja\Contable\Services\ExecutiveDashboardService;
use Rodieja\Contable\Services\InventoryReportService;
use Rodieja\Contable\Services\PedidoImportService;
use Rodieja\Contable\Services\ProfitabilityReportService;
use Rodieja\Contable\Services\SalesReportService;
use Rodieja\Contable\Taxonomies\TaxonomyRegistrar;

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
			UserRodiejaProfileFields::class,
			static fn( Container $c ) => new UserRodiejaProfileFields( $c->get( OutputEscaper::class ) )
		);
		$container->set(
			RolesDocumentationPage::class,
			static fn( Container $c ) => new RolesDocumentationPage( $c->get( OutputEscaper::class ) )
		);
	}
}
