<?php
/**
 * Rodieja Contable admin — React Read-Only shaped top-level menus + native submenus.
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

	public const SLUG_INICIO   = PostTypeRegistrar::MENU_PARENT;
	public const SLUG_VEHICULOS = 'rodieja-vehiculos';
	public const SLUG_INVENTARIO = 'rodieja-inventario';
	public const SLUG_FINANZAS = 'rodieja-finanzas';
	public const SLUG_REPORTES = ReportesAdminPage::SLUG;
	public const SLUG_INPART   = 'rodieja-inpart';
	public const SLUG_CONFIG   = 'rodieja-settings';

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
		add_action( 'admin_menu', array( $this, 'add_menus' ), 9 );
		add_action( 'admin_menu', array( $this, 'relabel_first_items' ), 999 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menus(): void {
		$cap = CapabilityGate::MANAGE;

		// —— Inicio (dashboard) ——
		add_menu_page(
			__( 'Rodieja Contable', 'rodieja-contable' ),
			__( 'Inicio', 'rodieja-contable' ),
			$cap,
			self::SLUG_INICIO,
			array( $this, 'render_dashboard' ),
			'dashicons-admin-home',
			2
		);

		// —— Vehículos ——
		add_menu_page(
			__( 'Vehículos', 'rodieja-contable' ),
			__( 'Vehículos', 'rodieja-contable' ),
			$cap,
			self::SLUG_VEHICULOS,
			array( $this, 'redirect_vehiculos' ),
			'dashicons-car',
			3
		);
		$this->cpt_submenu( self::SLUG_VEHICULOS, __( 'Ver por Generaciones', 'rodieja-contable' ), 'rodieja_generacion' );
		$this->cpt_submenu( self::SLUG_VEHICULOS, __( 'Marcas', 'rodieja-contable' ), 'rodieja_marca' );
		$this->cpt_submenu( self::SLUG_VEHICULOS, __( 'Modelos', 'rodieja-contable' ), 'rodieja_modelo' );
		add_submenu_page(
			self::SLUG_VEHICULOS,
			__( 'Nuevo Vehículo', 'rodieja-contable' ),
			__( 'Nuevo Vehículo', 'rodieja-contable' ),
			$cap,
			'post-new.php?post_type=rodieja_vehiculo'
		);

		// —— Inventario ——
		add_menu_page(
			__( 'Inventario', 'rodieja-contable' ),
			__( 'Inventario', 'rodieja-contable' ),
			$cap,
			self::SLUG_INVENTARIO,
			array( $this, 'redirect_inventario' ),
			'dashicons-admin-tools',
			4
		);
		add_submenu_page(
			self::SLUG_INVENTARIO,
			__( 'Nuevo Repuesto', 'rodieja-contable' ),
			__( 'Nuevo Repuesto', 'rodieja-contable' ),
			$cap,
			'post-new.php?post_type=rodieja_repuesto'
		);
		add_submenu_page(
			self::SLUG_INVENTARIO,
			__( 'Stock crítico', 'rodieja-contable' ),
			__( 'Stock crítico', 'rodieja-contable' ),
			$cap,
			'rodieja-stock-critico',
			array( $this->critical_stock, 'render' )
		);

		// —— Finanzas ——
		add_menu_page(
			__( 'Finanzas', 'rodieja-contable' ),
			__( 'Finanzas', 'rodieja-contable' ),
			$cap,
			self::SLUG_FINANZAS,
			array( $this, 'redirect_finanzas' ),
			'dashicons-money-alt',
			5
		);
		add_submenu_page(
			self::SLUG_FINANZAS,
			__( 'Nueva Transacción', 'rodieja-contable' ),
			__( 'Nueva Transacción', 'rodieja-contable' ),
			$cap,
			'post-new.php?post_type=rodieja_transaccion'
		);
		add_submenu_page(
			self::SLUG_FINANZAS,
			__( 'Montos', 'rodieja-contable' ),
			__( 'Montos', 'rodieja-contable' ),
			$cap,
			'rodieja-finanzas-dashboard',
			array( $this->finance_dashboard, 'render' )
		);

		// —— Reportes ——
		add_menu_page(
			__( 'Reportes', 'rodieja-contable' ),
			__( 'Reportes', 'rodieja-contable' ),
			$cap,
			self::SLUG_REPORTES,
			array( $this->reportes_hub, 'render' ),
			'dashicons-chart-bar',
			6
		);
		add_submenu_page(
			self::SLUG_REPORTES,
			__( 'Ventas Empleados', 'rodieja-contable' ),
			__( 'Ventas Empleados', 'rodieja-contable' ),
			$cap,
			'rodieja-reporte-ventas',
			array( $this->report_ventas, 'render' )
		);
		add_submenu_page(
			self::SLUG_REPORTES,
			__( 'Vehículos', 'rodieja-contable' ),
			__( 'Vehículos', 'rodieja-contable' ),
			$cap,
			'rodieja-reporte-finanzas',
			array( $this->report_finanzas, 'render' )
		);
		add_submenu_page(
			self::SLUG_REPORTES,
			__( 'Repuestos', 'rodieja-contable' ),
			__( 'Repuestos', 'rodieja-contable' ),
			$cap,
			'rodieja-reporte-inventario',
			array( $this->report_inventario, 'render' )
		);

		// —— Cotizaciones InPart ——
		add_menu_page(
			__( 'Cotizaciones InPart', 'rodieja-contable' ),
			__( 'Cotizaciones InPart', 'rodieja-contable' ),
			$cap,
			self::SLUG_INPART,
			array( $this, 'redirect_inpart' ),
			'dashicons-email-alt',
			7
		);
		add_submenu_page(
			self::SLUG_INPART,
			__( 'INS / Importar', 'rodieja-contable' ),
			__( 'INS / Importar', 'rodieja-contable' ),
			$cap,
			'rodieja-inpart-import',
			array( $this->pedido_import, 'render' )
		);
		add_submenu_page(
			self::SLUG_INPART,
			__( 'Ventas Empleados INS', 'rodieja-contable' ),
			__( 'Ventas Empleados INS', 'rodieja-contable' ),
			$cap,
			'rodieja-ins-ventas',
			array( $this->report_ventas, 'render' )
		);

		// —— Configuración ——
		add_menu_page(
			__( 'Configuración', 'rodieja-contable' ),
			__( 'Configuración', 'rodieja-contable' ),
			$cap,
			self::SLUG_CONFIG,
			array( $this, 'render_settings' ),
			'dashicons-admin-generic',
			8
		);
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'Mi Perfil', 'rodieja-contable' ),
			__( 'Mi Perfil', 'rodieja-contable' ),
			'read',
			'profile.php'
		);
		$this->cpt_submenu( self::SLUG_CONFIG, __( 'Empleados', 'rodieja-contable' ), 'rodieja_empleado' );
		$this->cpt_submenu( self::SLUG_CONFIG, __( 'Historial', 'rodieja-contable' ), 'rodieja_evento' );
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'Integraciones', 'rodieja-contable' ),
			__( 'Integraciones', 'rodieja-contable' ),
			$cap,
			'rodieja-config-integrations',
			array( $this->audatex_settings, 'render' )
		);
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'Cantones CR', 'rodieja-contable' ),
			__( 'Cantones CR', 'rodieja-contable' ),
			$cap,
			'rodieja-config-cantones',
			array( $this->cantones_settings, 'render' )
		);
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'Tools', 'rodieja-contable' ),
			__( 'Tools', 'rodieja-contable' ),
			$cap,
			'rodieja-tools',
			array( $this->tools, 'render' )
		);
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'Roles', 'rodieja-contable' ),
			__( 'Roles', 'rodieja-contable' ),
			$cap,
			RolesDocumentationPage::SLUG,
			array( $this->roles_doc, 'render' )
		);
	}

	/**
	 * Rename the auto-added parent submenu and point list parents at CPT screens.
	 */
	public function relabel_first_items(): void {
		global $submenu;
		$map = array(
			self::SLUG_INICIO     => array( 'label' => __( 'Inicio', 'rodieja-contable' ), 'slug' => null ),
			self::SLUG_VEHICULOS  => array( 'label' => __( 'Lista de Vehículos', 'rodieja-contable' ), 'slug' => 'edit.php?post_type=rodieja_vehiculo' ),
			self::SLUG_INVENTARIO => array( 'label' => __( 'Lista de Repuestos', 'rodieja-contable' ), 'slug' => 'edit.php?post_type=rodieja_repuesto' ),
			self::SLUG_FINANZAS   => array( 'label' => __( 'Transacciones', 'rodieja-contable' ), 'slug' => 'edit.php?post_type=rodieja_transaccion' ),
			self::SLUG_REPORTES   => array( 'label' => __( 'General', 'rodieja-contable' ), 'slug' => null ),
			self::SLUG_INPART     => array( 'label' => __( 'Pedidos', 'rodieja-contable' ), 'slug' => 'edit.php?post_type=rodieja_pedido' ),
			self::SLUG_CONFIG     => array( 'label' => __( 'Ajustes', 'rodieja-contable' ), 'slug' => null ),
		);
		foreach ( $map as $parent => $cfg ) {
			if ( empty( $submenu[ $parent ][0] ) || ! is_array( $submenu[ $parent ][0] ) ) {
				continue;
			}
			$submenu[ $parent ][0][0] = $cfg['label'];
			if ( ! empty( $cfg['slug'] ) ) {
				$submenu[ $parent ][0][2] = $cfg['slug'];
			}
		}
	}

	public function redirect_vehiculos(): void {
		wp_safe_redirect( admin_url( 'edit.php?post_type=rodieja_vehiculo' ) );
		exit;
	}

	public function redirect_inventario(): void {
		wp_safe_redirect( admin_url( 'edit.php?post_type=rodieja_repuesto' ) );
		exit;
	}

	public function redirect_finanzas(): void {
		wp_safe_redirect( admin_url( 'edit.php?post_type=rodieja_transaccion' ) );
		exit;
	}

	public function redirect_inpart(): void {
		wp_safe_redirect( admin_url( 'edit.php?post_type=rodieja_pedido' ) );
		exit;
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
			array( __( 'Cotizaciones InPart', 'rodieja-contable' ), __( 'Pedidos e INS', 'rodieja-contable' ), 'dashicons-email-alt', admin_url( 'edit.php?post_type=rodieja_pedido' ) ),
			array( __( 'Reportes', 'rodieja-contable' ), __( 'CSV y resúmenes', 'rodieja-contable' ), 'dashicons-chart-bar', admin_url( 'admin.php?page=' . ReportesAdminPage::SLUG ) ),
			array( __( 'Configuración', 'rodieja-contable' ), __( 'Perfil y ajustes', 'rodieja-contable' ), 'dashicons-admin-generic', admin_url( 'admin.php?page=rodieja-settings' ) ),
		);
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-hero">
				<p class="rodieja-hero__eyebrow"><?php echo $this->escaper->html( __( 'Rodieja Contable', 'rodieja-contable' ) ); ?></p>
				<h1><?php echo $this->escaper->html( $org ); ?></h1>
				<p><?php echo $this->escaper->html( __( 'Panel nativo WordPress — misma estructura de menús que Rodieja Contable Read-Only.', 'rodieja-contable' ) ); ?></p>
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
		$org   = get_option( 'rodieja_contable_org_name', 'Rodieja Contable' );
		$links = array(
			array( __( 'Integraciones INS/Audatex', 'rodieja-contable' ), admin_url( 'admin.php?page=rodieja-config-integrations' ) ),
			array( __( 'Cantones CR', 'rodieja-contable' ), admin_url( 'admin.php?page=rodieja-config-cantones' ) ),
			array( __( 'Tools import/export', 'rodieja-contable' ), admin_url( 'admin.php?page=rodieja-tools' ) ),
			array( __( 'Roles y capacidades', 'rodieja-contable' ), admin_url( 'admin.php?page=' . RolesDocumentationPage::SLUG ) ),
			array( __( 'Mi perfil WP', 'rodieja-contable' ), admin_url( 'profile.php' ) ),
		);
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-page-head">
				<h1><?php echo $this->escaper->html( __( 'Configuración', 'rodieja-contable' ) ); ?></h1>
				<p class="rodieja-admin-lede"><?php echo $this->escaper->html( __( 'Ajustes nativos (Options API) — equivalente a Configuración del Read-Only.', 'rodieja-contable' ) ); ?></p>
			</div>
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
				<ul class="rodieja-link-list">
					<?php foreach ( $links as $link ) : ?>
						<li><a href="<?php echo $this->escaper->url( $link[1] ); ?>"><?php echo $this->escaper->html( $link[0] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	private function cpt_submenu( string $parent, string $label, string $post_type ): void {
		add_submenu_page( $parent, $label, $label, CapabilityGate::MANAGE, 'edit.php?post_type=' . $post_type );
	}
}
