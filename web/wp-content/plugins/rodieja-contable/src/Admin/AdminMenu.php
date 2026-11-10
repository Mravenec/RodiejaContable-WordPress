<?php
/**
 * Rodieja Contable admin hub — few React-shaped menus, modern native screens.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\PostTypes\PostTypeRegistrar;
use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\ExecutiveDashboardService;

final class AdminMenu {

	private const SLUG = PostTypeRegistrar::MENU_PARENT;

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private CriticalStockPage $critical_stock,
		private FinanceDashboardPage $finance_dashboard,
		private SalesReportPage $report_ventas,
		private InventorySummaryReportPage $report_inventario,
		private ProfitabilityReportPage $report_finanzas,
		private ReportesAdminPage $reportes_hub,
		private RolesDocumentationPage $roles_doc,
		private ExecutiveDashboardService $executive,
		private PedidoImportPage $pedido_import,
		private AudatexInsSettingsPage $audatex_settings,
		private CantonesSettingsPage $cantones_settings,
		private ToolsAdminPage $tools
	) {}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'prune_auto_cpt_submenus' ), 999 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu(): void {
		add_menu_page(
			__( 'Rodieja Contable', 'rodieja-contable' ),
			__( 'Rodieja Contable', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			self::SLUG,
			array( $this, 'render_dashboard' ),
			'dashicons-chart-area',
			2
		);

		add_submenu_page( self::SLUG, __( 'Inicio', 'rodieja-contable' ), __( 'Inicio', 'rodieja-contable' ), CapabilityGate::MANAGE, self::SLUG, array( $this, 'render_dashboard' ) );

		$this->add_section( __( 'Vehículos', 'rodieja-contable' ) );
		$this->add_cpt_link( __( 'Lista', 'rodieja-contable' ), 'rodieja_vehiculo' );
		$this->add_cpt_link( __( 'Marcas', 'rodieja-contable' ), 'rodieja_marca' );
		$this->add_cpt_link( __( 'Modelos', 'rodieja-contable' ), 'rodieja_modelo' );
		$this->add_cpt_link( __( 'Generaciones', 'rodieja-contable' ), 'rodieja_generacion' );

		$this->add_section( __( 'Inventario', 'rodieja-contable' ) );
		$this->add_cpt_link( __( 'Repuestos', 'rodieja-contable' ), 'rodieja_repuesto' );
		add_submenu_page( self::SLUG, __( 'Stock crítico', 'rodieja-contable' ), __( 'Stock crítico', 'rodieja-contable' ), CapabilityGate::MANAGE, 'rodieja-stock-critico', array( $this->critical_stock, 'render' ) );

		$this->add_section( __( 'Finanzas', 'rodieja-contable' ) );
		$this->add_cpt_link( __( 'Transacciones', 'rodieja-contable' ), 'rodieja_transaccion' );
		add_submenu_page( self::SLUG, __( 'Montos', 'rodieja-contable' ), __( 'Montos', 'rodieja-contable' ), CapabilityGate::MANAGE, 'rodieja-finanzas-dashboard', array( $this->finance_dashboard, 'render' ) );

		$this->add_section( __( 'Operación', 'rodieja-contable' ) );
		$this->add_cpt_link( __( 'Pedidos', 'rodieja-contable' ), 'rodieja_pedido' );
		add_submenu_page( self::SLUG, __( 'Importar', 'rodieja-contable' ), __( 'Importar', 'rodieja-contable' ), CapabilityGate::MANAGE, 'rodieja-inpart-import', array( $this->pedido_import, 'render' ) );
		add_submenu_page( self::SLUG, __( 'Reportes', 'rodieja-contable' ), __( 'Reportes', 'rodieja-contable' ), CapabilityGate::MANAGE, ReportesAdminPage::SLUG, array( $this->reportes_hub, 'render' ) );
		$this->add_cpt_link( __( 'Empleados', 'rodieja-contable' ), 'rodieja_empleado' );
		$this->add_cpt_link( __( 'Historial', 'rodieja-contable' ), 'rodieja_evento' );

		$this->add_section( __( 'Sistema', 'rodieja-contable' ) );
		add_submenu_page( self::SLUG, __( 'Ajustes', 'rodieja-contable' ), __( 'Ajustes', 'rodieja-contable' ), CapabilityGate::MANAGE, 'rodieja-settings', array( $this, 'render_settings' ) );

		// Hidden routes (linked from Reportes / Ajustes — keep registered).
		add_submenu_page( null, __( 'Ventas', 'rodieja-contable' ), '', CapabilityGate::MANAGE, 'rodieja-reporte-ventas', array( $this->report_ventas, 'render' ) );
		add_submenu_page( null, __( 'Inventario', 'rodieja-contable' ), '', CapabilityGate::MANAGE, 'rodieja-reporte-inventario', array( $this->report_inventario, 'render' ) );
		add_submenu_page( null, __( 'Rentabilidad', 'rodieja-contable' ), '', CapabilityGate::MANAGE, 'rodieja-reporte-finanzas', array( $this->report_finanzas, 'render' ) );
		add_submenu_page( null, __( 'Integraciones', 'rodieja-contable' ), '', CapabilityGate::MANAGE, 'rodieja-config-integrations', array( $this->audatex_settings, 'render' ) );
		add_submenu_page( null, __( 'Cantones', 'rodieja-contable' ), '', CapabilityGate::MANAGE, 'rodieja-config-cantones', array( $this->cantones_settings, 'render' ) );
		add_submenu_page( null, __( 'Tools', 'rodieja-contable' ), '', CapabilityGate::MANAGE, 'rodieja-tools', array( $this->tools, 'render' ) );
		add_submenu_page( null, __( 'Roles', 'rodieja-contable' ), '', CapabilityGate::MANAGE, RolesDocumentationPage::SLUG, array( $this->roles_doc, 'render' ) );
	}

	public function prune_auto_cpt_submenus(): void {
		global $submenu;
		if ( ! isset( $submenu[ self::SLUG ] ) || ! is_array( $submenu[ self::SLUG ] ) ) {
			return;
		}
		$keep = array(
			self::SLUG,
			'rodieja-stock-critico',
			'rodieja-finanzas-dashboard',
			ReportesAdminPage::SLUG,
			'rodieja-inpart-import',
			'rodieja-settings',
		);
		foreach ( $submenu[ self::SLUG ] as $i => $item ) {
			if ( ! is_array( $item ) || ! isset( $item[2] ) ) {
				continue;
			}
			$slug = (string) $item[2];
			if ( in_array( $slug, $keep, true ) ) {
				continue;
			}
			if ( str_starts_with( $slug, 'rodieja-menu-sep-' ) ) {
				continue;
			}
			if ( preg_match( '#^edit\.php\?post_type=rodieja_#', $slug ) ) {
				continue;
			}
			unset( $submenu[ self::SLUG ][ $i ] );
		}
	}

	public function register_settings(): void {
		register_setting(
			'rodieja_contable_settings',
			'rodieja_contable_org_name',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'Rodieja Contable',
			)
		);
	}

	public function render_dashboard(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), '', array( 'response' => 403 ) );
		}
		$snap   = $this->executive->snapshot();
		$counts = $snap['counts'];
		$org    = (string) get_option( 'rodieja_contable_org_name', 'Rodieja Contable' );

		$tiles = array(
			array( __( 'Vehículos', 'rodieja-contable' ), __( 'Lista y catálogo', 'rodieja-contable' ), 'dashicons-car', admin_url( 'edit.php?post_type=rodieja_vehiculo' ) ),
			array( __( 'Inventario', 'rodieja-contable' ), __( 'Repuestos y stock', 'rodieja-contable' ), 'dashicons-admin-tools', admin_url( 'edit.php?post_type=rodieja_repuesto' ) ),
			array( __( 'Finanzas', 'rodieja-contable' ), __( 'Transacciones', 'rodieja-contable' ), 'dashicons-money-alt', admin_url( 'edit.php?post_type=rodieja_transaccion' ) ),
			array( __( 'Pedidos', 'rodieja-contable' ), __( 'Cotizaciones InPart', 'rodieja-contable' ), 'dashicons-cart', admin_url( 'edit.php?post_type=rodieja_pedido' ) ),
			array( __( 'Reportes', 'rodieja-contable' ), __( 'CSV y resúmenes', 'rodieja-contable' ), 'dashicons-chart-bar', admin_url( 'admin.php?page=' . ReportesAdminPage::SLUG ) ),
			array( __( 'Ajustes', 'rodieja-contable' ), __( 'Integraciones y tools', 'rodieja-contable' ), 'dashicons-admin-generic', admin_url( 'admin.php?page=rodieja-settings' ) ),
		);
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-hero">
				<h1><?php echo $this->escaper->html( $org ); ?></h1>
				<p><?php echo $this->escaper->html( __( 'Panel nativo WordPress — mismos módulos que Rodieja Contable, sin SPA.', 'rodieja-contable' ) ); ?></p>
			</div>

			<div class="rodieja-metrics">
				<div class="rodieja-metric rodieja-metric--accent">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Vehículos', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( (string) ( $counts['rodieja_vehiculo'] ?? 0 ) ); ?></span>
				</div>
				<div class="rodieja-metric">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Repuestos', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( (string) ( $counts['rodieja_repuesto'] ?? 0 ) ); ?></span>
				</div>
				<div class="rodieja-metric">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Pedidos', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( (string) ( $counts['rodieja_pedido'] ?? 0 ) ); ?></span>
				</div>
				<div class="rodieja-metric rodieja-metric--ok">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Balance mes', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['finance_month']['balance'], 0 ) ); ?></span>
				</div>
				<div class="rodieja-metric">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Inventario ₡', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['inventory']['valor_inventario'], 0 ) ); ?></span>
				</div>
				<div class="rodieja-metric">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Margen est.', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['profitability']['margen_bruto'], 0 ) ); ?></span>
				</div>
			</div>

			<p class="rodieja-admin-lede"><?php echo $this->escaper->html( __( 'Accesos rápidos', 'rodieja-contable' ) ); ?></p>
			<div class="rodieja-nav-grid">
				<?php foreach ( $tiles as $tile ) : ?>
					<a class="rodieja-nav-tile" href="<?php echo $this->escaper->url( $tile[3] ); ?>">
						<span class="dashicons <?php echo $this->escaper->attr( $tile[2] ); ?>" aria-hidden="true"></span>
						<span class="rodieja-nav-tile__title"><?php echo $this->escaper->html( $tile[0] ); ?></span>
						<span class="rodieja-nav-tile__hint"><?php echo $this->escaper->html( $tile[1] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	public function render_settings(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), '', array( 'response' => 403 ) );
		}
		$org = get_option( 'rodieja_contable_org_name', 'Rodieja Contable' );
		$links = array(
			array( __( 'Integraciones INS/Audatex', 'rodieja-contable' ), admin_url( 'admin.php?page=rodieja-config-integrations' ) ),
			array( __( 'Cantones CR', 'rodieja-contable' ), admin_url( 'admin.php?page=rodieja-config-cantones' ) ),
			array( __( 'Tools import/export', 'rodieja-contable' ), admin_url( 'admin.php?page=rodieja-tools' ) ),
			array( __( 'Roles y capacidades', 'rodieja-contable' ), admin_url( 'admin.php?page=' . RolesDocumentationPage::SLUG ) ),
			array( __( 'Mi perfil WP', 'rodieja-contable' ), admin_url( 'profile.php' ) ),
		);
		?>
		<div class="wrap rodieja-admin-wrap">
			<h1><?php echo $this->escaper->html( __( 'Ajustes', 'rodieja-contable' ) ); ?></h1>
			<p class="rodieja-admin-lede"><?php echo $this->escaper->html( __( 'Configuración nativa (Options API) y accesos de sistema.', 'rodieja-contable' ) ); ?></p>
			<form method="post" action="options.php" class="rodieja-admin-card">
				<?php settings_fields( 'rodieja_contable_settings' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="rodieja_contable_org_name"><?php echo $this->escaper->html( __( 'Nombre organización', 'rodieja-contable' ) ); ?></label></th>
						<td><input type="text" class="regular-text" id="rodieja_contable_org_name" name="rodieja_contable_org_name" value="<?php echo $this->escaper->attr( (string) $org ); ?>" /></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<div class="rodieja-admin-card">
				<h2 class="title"><?php echo $this->escaper->html( __( 'Más opciones', 'rodieja-contable' ) ); ?></h2>
				<ul>
					<?php foreach ( $links as $link ) : ?>
						<li><a href="<?php echo $this->escaper->url( $link[1] ); ?>"><?php echo $this->escaper->html( $link[0] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	private function add_section( string $title ): void {
		$slug = 'rodieja-menu-sep-' . sanitize_title( $title );
		add_submenu_page( self::SLUG, $title, $title, CapabilityGate::MANAGE, $slug, '__return_null' );
	}

	private function add_cpt_link( string $label, string $post_type ): void {
		add_submenu_page( self::SLUG, $label, $label, CapabilityGate::MANAGE, 'edit.php?post_type=' . $post_type );
	}
}
