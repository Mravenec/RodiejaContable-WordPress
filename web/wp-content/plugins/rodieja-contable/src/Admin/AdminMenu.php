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
		private ToolsAdminPage $tools,
		private JerarquiaAdminPage $jerarquia
	) {}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menus' ), 9 );
		add_action( 'admin_menu', array( $this, 'relabel_first_items' ), 999 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menus(): void {
		$cap = CapabilityGate::MANAGE;

		// —— Home (dashboard) ——
		add_menu_page(
			__( 'Rodieja Contable', 'rodieja-contable' ),
			__( 'Home', 'rodieja-contable' ),
			$cap,
			self::SLUG_INICIO,
			array( $this, 'render_dashboard' ),
			'dashicons-admin-home',
			2
		);

		// —— Vehicles ——
		add_menu_page(
			__( 'Vehicles', 'rodieja-contable' ),
			__( 'Vehicles', 'rodieja-contable' ),
			$cap,
			self::SLUG_VEHICULOS,
			array( $this, 'redirect_vehiculos' ),
			'dashicons-car',
			3
		);
		add_submenu_page(
			self::SLUG_VEHICULOS,
			__( 'View by Generations', 'rodieja-contable' ),
			__( 'View by Generations', 'rodieja-contable' ),
			$cap,
			JerarquiaAdminPage::SLUG,
			array( $this->jerarquia, 'render' )
		);
		$this->cpt_submenu( self::SLUG_VEHICULOS, __( 'Brands', 'rodieja-contable' ), 'rodieja_marca' );
		$this->cpt_submenu( self::SLUG_VEHICULOS, __( 'Models', 'rodieja-contable' ), 'rodieja_modelo' );
		$this->cpt_submenu( self::SLUG_VEHICULOS, __( 'Generations', 'rodieja-contable' ), 'rodieja_generacion' );
		add_submenu_page(
			self::SLUG_VEHICULOS,
			__( 'Add Vehicle', 'rodieja-contable' ),
			__( 'Add Vehicle', 'rodieja-contable' ),
			$cap,
			'post-new.php?post_type=rodieja_vehiculo'
		);

		// —— Inventory ——
		add_menu_page(
			__( 'Inventory', 'rodieja-contable' ),
			__( 'Inventory', 'rodieja-contable' ),
			$cap,
			self::SLUG_INVENTARIO,
			array( $this, 'redirect_inventario' ),
			'dashicons-admin-tools',
			4
		);
		add_submenu_page(
			self::SLUG_INVENTARIO,
			__( 'Add Part', 'rodieja-contable' ),
			__( 'Add Part', 'rodieja-contable' ),
			$cap,
			'post-new.php?post_type=rodieja_repuesto'
		);
		add_submenu_page(
			self::SLUG_INVENTARIO,
			__( 'Critical stock', 'rodieja-contable' ),
			__( 'Critical stock', 'rodieja-contable' ),
			$cap,
			'rodieja-stock-critico',
			array( $this->critical_stock, 'render' )
		);

		// —— Finance ——
		add_menu_page(
			__( 'Finance', 'rodieja-contable' ),
			__( 'Finance', 'rodieja-contable' ),
			$cap,
			self::SLUG_FINANZAS,
			array( $this, 'redirect_finanzas' ),
			'dashicons-money-alt',
			5
		);
		add_submenu_page(
			self::SLUG_FINANZAS,
			__( 'Nueva Transaction', 'rodieja-contable' ),
			__( 'Nueva Transaction', 'rodieja-contable' ),
			$cap,
			'post-new.php?post_type=rodieja_transaccion'
		);
		add_submenu_page(
			self::SLUG_FINANZAS,
			__( 'Amounts', 'rodieja-contable' ),
			__( 'Amounts', 'rodieja-contable' ),
			$cap,
			'rodieja-finanzas-dashboard',
			array( $this->finance_dashboard, 'render' )
		);

		// —— Reports ——
		add_menu_page(
			__( 'Reports', 'rodieja-contable' ),
			__( 'Reports', 'rodieja-contable' ),
			$cap,
			self::SLUG_REPORTES,
			array( $this->reportes_hub, 'render' ),
			'dashicons-chart-bar',
			6
		);
		add_submenu_page(
			self::SLUG_REPORTES,
			__( 'Sales Employees', 'rodieja-contable' ),
			__( 'Sales Employees', 'rodieja-contable' ),
			$cap,
			'rodieja-reporte-ventas',
			array( $this->report_ventas, 'render' )
		);
		add_submenu_page(
			self::SLUG_REPORTES,
			__( 'Vehicles', 'rodieja-contable' ),
			__( 'Vehicles', 'rodieja-contable' ),
			$cap,
			'rodieja-reporte-finanzas',
			array( $this->report_finanzas, 'render' )
		);
		add_submenu_page(
			self::SLUG_REPORTES,
			__( 'Parts', 'rodieja-contable' ),
			__( 'Parts', 'rodieja-contable' ),
			$cap,
			'rodieja-reporte-inventario',
			array( $this->report_inventario, 'render' )
		);

		// —— InPart Quotes ——
		add_menu_page(
			__( 'InPart Quotes', 'rodieja-contable' ),
			__( 'InPart Quotes', 'rodieja-contable' ),
			$cap,
			self::SLUG_INPART,
			array( $this, 'redirect_inpart' ),
			'dashicons-email-alt',
			7
		);
		add_submenu_page(
			self::SLUG_INPART,
			__( 'INS / Import', 'rodieja-contable' ),
			__( 'INS / Import', 'rodieja-contable' ),
			$cap,
			'rodieja-inpart-import',
			array( $this->pedido_import, 'render' )
		);
		add_submenu_page(
			self::SLUG_INPART,
			__( 'Sales Employees INS', 'rodieja-contable' ),
			__( 'Sales Employees INS', 'rodieja-contable' ),
			$cap,
			'rodieja-ins-ventas',
			array( $this->report_ventas, 'render' )
		);

		// —— Settings ——
		add_menu_page(
			__( 'Settings', 'rodieja-contable' ),
			__( 'Settings', 'rodieja-contable' ),
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
		$this->cpt_submenu( self::SLUG_CONFIG, __( 'Employees', 'rodieja-contable' ), 'rodieja_empleado' );
		$this->cpt_submenu( self::SLUG_CONFIG, __( 'History', 'rodieja-contable' ), 'rodieja_evento' );
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'Integrations', 'rodieja-contable' ),
			__( 'Integrations', 'rodieja-contable' ),
			$cap,
			'rodieja-config-integrations',
			array( $this->audatex_settings, 'render' )
		);
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'CR cantons', 'rodieja-contable' ),
			__( 'CR cantons', 'rodieja-contable' ),
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
			self::SLUG_INICIO     => array( 'label' => __( 'Home', 'rodieja-contable' ), 'slug' => null ),
			self::SLUG_VEHICULOS  => array( 'label' => __( 'Vehicle list', 'rodieja-contable' ), 'slug' => 'edit.php?post_type=rodieja_vehiculo' ),
			self::SLUG_INVENTARIO => array( 'label' => __( 'Parts list', 'rodieja-contable' ), 'slug' => 'edit.php?post_type=rodieja_repuesto' ),
			self::SLUG_FINANZAS   => array( 'label' => __( 'Transactions', 'rodieja-contable' ), 'slug' => 'edit.php?post_type=rodieja_transaccion' ),
			self::SLUG_REPORTES   => array( 'label' => __( 'Overview', 'rodieja-contable' ), 'slug' => null ),
			self::SLUG_INPART     => array( 'label' => __( 'Orders', 'rodieja-contable' ), 'slug' => 'edit.php?post_type=rodieja_pedido' ),
			self::SLUG_CONFIG     => array( 'label' => __( 'Settings', 'rodieja-contable' ), 'slug' => null ),
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
			array( __( 'Vehicles', 'rodieja-contable' ), __( 'List and catalog', 'rodieja-contable' ), 'dashicons-car', admin_url( 'edit.php?post_type=rodieja_vehiculo' ) ),
			array( __( 'Inventory', 'rodieja-contable' ), __( 'Parts and stock', 'rodieja-contable' ), 'dashicons-admin-tools', admin_url( 'edit.php?post_type=rodieja_repuesto' ) ),
			array( __( 'Finance', 'rodieja-contable' ), __( 'Transactions', 'rodieja-contable' ), 'dashicons-money-alt', admin_url( 'edit.php?post_type=rodieja_transaccion' ) ),
			array( __( 'InPart Quotes', 'rodieja-contable' ), __( 'Orders and INS', 'rodieja-contable' ), 'dashicons-email-alt', admin_url( 'edit.php?post_type=rodieja_pedido' ) ),
			array( __( 'Reports', 'rodieja-contable' ), __( 'CSV and digests', 'rodieja-contable' ), 'dashicons-chart-bar', admin_url( 'admin.php?page=' . ReportesAdminPage::SLUG ) ),
			array( __( 'Settings', 'rodieja-contable' ), __( 'Profile and settings', 'rodieja-contable' ), 'dashicons-admin-generic', admin_url( 'admin.php?page=rodieja-settings' ) ),
		);
		$veh_n = (int) ( $counts['rodieja_vehiculo'] ?? 0 );
		$ord_n = (int) ( $counts['rodieja_pedido'] ?? 0 );
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-hero">
				<div class="rodieja-hero__copy">
					<p class="rodieja-hero__eyebrow"><?php echo $this->escaper->html( __( 'Rodieja Contable', 'rodieja-contable' ) ); ?></p>
					<h1><?php echo $this->escaper->html( $org ); ?></h1>
					<p><?php echo $this->escaper->html( __( 'Native WordPress operations console — same module map as Read-Only, Figma-grade shells without a SPA.', 'rodieja-contable' ) ); ?></p>
				</div>
				<div class="rodieja-hero__aside" aria-hidden="true">
					<div class="rodieja-hero-chip">
						<span class="rodieja-hero-chip__label"><?php echo $this->escaper->html( __( 'Vehicles', 'rodieja-contable' ) ); ?></span>
						<span class="rodieja-hero-chip__value"><?php echo $this->escaper->html( (string) $veh_n ); ?></span>
					</div>
					<div class="rodieja-hero-chip">
						<span class="rodieja-hero-chip__label"><?php echo $this->escaper->html( __( 'Orders', 'rodieja-contable' ) ); ?></span>
						<span class="rodieja-hero-chip__value"><?php echo $this->escaper->html( (string) $ord_n ); ?></span>
					</div>
					<div class="rodieja-hero-chip">
						<span class="rodieja-hero-chip__label"><?php echo $this->escaper->html( __( 'Month balance', 'rodieja-contable' ) ); ?></span>
						<span class="rodieja-hero-chip__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['finance_month']['balance'], 0 ) ); ?></span>
					</div>
					<div class="rodieja-hero-chip">
						<span class="rodieja-hero-chip__label"><?php echo $this->escaper->html( __( 'Est. margin', 'rodieja-contable' ) ); ?></span>
						<span class="rodieja-hero-chip__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['profitability']['margen_bruto'], 0 ) ); ?></span>
					</div>
				</div>
			</div>

			<h2 class="rodieja-section-title"><?php echo $this->escaper->html( __( 'Indicators', 'rodieja-contable' ) ); ?></h2>
			<div class="rodieja-metrics" role="group" aria-label="<?php echo $this->escaper->attr( __( 'Indicators', 'rodieja-contable' ) ); ?>">
				<div class="rodieja-metric rodieja-metric--accent">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Vehicles', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( (string) $veh_n ); ?></span>
				</div>
				<div class="rodieja-metric">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Parts', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( (string) ( $counts['rodieja_repuesto'] ?? 0 ) ); ?></span>
				</div>
				<div class="rodieja-metric">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Orders', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( (string) $ord_n ); ?></span>
				</div>
				<div class="rodieja-metric rodieja-metric--ok">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Month balance', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['finance_month']['balance'], 0 ) ); ?></span>
				</div>
				<div class="rodieja-metric">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Inventory ₡', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['inventory']['valor_inventario'], 0 ) ); ?></span>
				</div>
				<div class="rodieja-metric">
					<span class="rodieja-metric__label"><?php echo $this->escaper->html( __( 'Est. margin', 'rodieja-contable' ) ); ?></span>
					<span class="rodieja-metric__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['profitability']['margen_bruto'], 0 ) ); ?></span>
				</div>
			</div>

			<h2 class="rodieja-section-title"><?php echo $this->escaper->html( __( 'Modules', 'rodieja-contable' ) ); ?></h2>
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
			array( __( 'INS/Audatex integrations', 'rodieja-contable' ), admin_url( 'admin.php?page=rodieja-config-integrations' ) ),
			array( __( 'CR cantons', 'rodieja-contable' ), admin_url( 'admin.php?page=rodieja-config-cantones' ) ),
			array( __( 'Tools import/export', 'rodieja-contable' ), admin_url( 'admin.php?page=rodieja-tools' ) ),
			array( __( 'Roles and capabilities', 'rodieja-contable' ), admin_url( 'admin.php?page=' . RolesDocumentationPage::SLUG ) ),
			array( __( 'My WP profile', 'rodieja-contable' ), admin_url( 'profile.php' ) ),
		);
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-page-header">
				<div class="rodieja-page-header__main">
					<p class="rodieja-page-header__crumbs">
						<a href="<?php echo $this->escaper->url( admin_url( 'admin.php?page=' . self::SLUG_INICIO ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'rodieja-contable' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Settings', 'rodieja-contable' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Settings', 'rodieja-contable' ) ); ?></h1>
					<p class="rodieja-page-header__lede"><?php echo $this->escaper->html( __( 'Native Options API — Read-Only Configuration parity, Figma-grade shell.', 'rodieja-contable' ) ); ?></p>
				</div>
			</div>
			<div class="rodieja-shell-grid">
				<form method="post" action="options.php" class="rodieja-shell-panel">
					<h2 class="rodieja-shell-panel__title"><?php echo $this->escaper->html( __( 'Organization', 'rodieja-contable' ) ); ?></h2>
					<p class="rodieja-shell-panel__hint"><?php echo $this->escaper->html( __( 'Shown on Home and login branding.', 'rodieja-contable' ) ); ?></p>
					<?php settings_fields( 'rodieja_contable_settings' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="rodieja_contable_org_name"><?php echo $this->escaper->html( __( 'Organization name', 'rodieja-contable' ) ); ?></label></th>
							<td><input type="text" class="regular-text" id="rodieja_contable_org_name" name="rodieja_contable_org_name" value="<?php echo $this->escaper->attr( (string) $org ); ?>" /></td>
						</tr>
					</table>
					<?php submit_button( __( 'Save changes', 'rodieja-contable' ) ); ?>
				</form>
				<div class="rodieja-shell-panel">
					<h2 class="rodieja-shell-panel__title"><?php echo $this->escaper->html( __( 'More options', 'rodieja-contable' ) ); ?></h2>
					<p class="rodieja-shell-panel__hint"><?php echo $this->escaper->html( __( 'Integrations, geo, tools, and roles.', 'rodieja-contable' ) ); ?></p>
					<ul class="rodieja-link-list">
						<?php foreach ( $links as $link ) : ?>
							<li><a href="<?php echo $this->escaper->url( $link[1] ); ?>"><?php echo $this->escaper->html( $link[0] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	private function cpt_submenu( string $parent, string $label, string $post_type ): void {
		add_submenu_page( $parent, $label, $label, CapabilityGate::MANAGE, 'edit.php?post_type=' . $post_type );
	}
}
