<?php
/**
 * Workshop Ledger admin — React Read-Only shaped top-level menus + native submenus.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\PostTypes\PostTypeRegistrar;
use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\ExecutiveDashboardService;

final class AdminMenu {

	public const SLUG_INICIO   = PostTypeRegistrar::MENU_PARENT;
	public const SLUG_VEHICULOS = 'wl-vehiculos';
	public const SLUG_INVENTARIO = 'wl-inventario';
	public const SLUG_FINANZAS = 'wl-finanzas';
	public const SLUG_REPORTES = ReportesAdminPage::SLUG;
	public const SLUG_INPART   = 'wl-inpart';
	public const SLUG_CONFIG   = 'wl-settings';

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
			__( 'Workshop Ledger', 'workshop-ledger' ),
			__( 'Home', 'workshop-ledger' ),
			$cap,
			self::SLUG_INICIO,
			array( $this, 'render_dashboard' ),
			'dashicons-admin-home',
			2
		);

		// —— Vehicles ——
		add_menu_page(
			__( 'Vehicles', 'workshop-ledger' ),
			__( 'Vehicles', 'workshop-ledger' ),
			$cap,
			self::SLUG_VEHICULOS,
			array( $this, 'redirect_vehiculos' ),
			'dashicons-car',
			3
		);
		add_submenu_page(
			self::SLUG_VEHICULOS,
			__( 'View by Generations', 'workshop-ledger' ),
			__( 'View by Generations', 'workshop-ledger' ),
			$cap,
			JerarquiaAdminPage::SLUG,
			array( $this->jerarquia, 'render' )
		);
		$this->cpt_submenu( self::SLUG_VEHICULOS, __( 'Brands', 'workshop-ledger' ), 'wl_marca' );
		$this->cpt_submenu( self::SLUG_VEHICULOS, __( 'Models', 'workshop-ledger' ), 'wl_modelo' );
		$this->cpt_submenu( self::SLUG_VEHICULOS, __( 'Generations', 'workshop-ledger' ), 'wl_generacion' );
		add_submenu_page(
			self::SLUG_VEHICULOS,
			__( 'Add Vehicle', 'workshop-ledger' ),
			__( 'Add Vehicle', 'workshop-ledger' ),
			$cap,
			'post-new.php?post_type=wl_vehiculo'
		);

		// —— Inventory ——
		add_menu_page(
			__( 'Inventory', 'workshop-ledger' ),
			__( 'Inventory', 'workshop-ledger' ),
			$cap,
			self::SLUG_INVENTARIO,
			array( $this, 'redirect_inventario' ),
			'dashicons-admin-tools',
			4
		);
		add_submenu_page(
			self::SLUG_INVENTARIO,
			__( 'Add Part', 'workshop-ledger' ),
			__( 'Add Part', 'workshop-ledger' ),
			$cap,
			'post-new.php?post_type=wl_repuesto'
		);
		add_submenu_page(
			self::SLUG_INVENTARIO,
			__( 'Critical stock', 'workshop-ledger' ),
			__( 'Critical stock', 'workshop-ledger' ),
			$cap,
			'wl-stock-critico',
			array( $this->critical_stock, 'render' )
		);

		// —— Finance ——
		add_menu_page(
			__( 'Finance', 'workshop-ledger' ),
			__( 'Finance', 'workshop-ledger' ),
			$cap,
			self::SLUG_FINANZAS,
			array( $this, 'redirect_finanzas' ),
			'dashicons-money-alt',
			5
		);
		add_submenu_page(
			self::SLUG_FINANZAS,
			__( 'Add Transaction', 'workshop-ledger' ),
			__( 'Add Transaction', 'workshop-ledger' ),
			$cap,
			'post-new.php?post_type=wl_transaccion'
		);
		add_submenu_page(
			self::SLUG_FINANZAS,
			__( 'Amounts', 'workshop-ledger' ),
			__( 'Amounts', 'workshop-ledger' ),
			$cap,
			'wl-finanzas-dashboard',
			array( $this->finance_dashboard, 'render' )
		);

		// —— Reports ——
		add_menu_page(
			__( 'Reports', 'workshop-ledger' ),
			__( 'Reports', 'workshop-ledger' ),
			$cap,
			self::SLUG_REPORTES,
			array( $this->reportes_hub, 'render' ),
			'dashicons-chart-bar',
			6
		);
		add_submenu_page(
			self::SLUG_REPORTES,
			__( 'Sales Employees', 'workshop-ledger' ),
			__( 'Sales Employees', 'workshop-ledger' ),
			$cap,
			'wl-reporte-ventas',
			array( $this->report_ventas, 'render' )
		);
		add_submenu_page(
			self::SLUG_REPORTES,
			__( 'Vehicles', 'workshop-ledger' ),
			__( 'Vehicles', 'workshop-ledger' ),
			$cap,
			'wl-reporte-finanzas',
			array( $this->report_finanzas, 'render' )
		);
		add_submenu_page(
			self::SLUG_REPORTES,
			__( 'Parts', 'workshop-ledger' ),
			__( 'Parts', 'workshop-ledger' ),
			$cap,
			'wl-reporte-inventario',
			array( $this->report_inventario, 'render' )
		);

		// —— Audatex InPart (Read-Only sider label) ——
		add_menu_page(
			__( 'Audatex InPart', 'workshop-ledger' ),
			__( 'Audatex InPart', 'workshop-ledger' ),
			$cap,
			self::SLUG_INPART,
			array( $this, 'redirect_inpart' ),
			'dashicons-email-alt',
			7
		);
		add_submenu_page(
			self::SLUG_INPART,
			__( 'INS / Import', 'workshop-ledger' ),
			__( 'INS / Import', 'workshop-ledger' ),
			$cap,
			'wl-inpart-import',
			array( $this->pedido_import, 'render' )
		);
		add_submenu_page(
			self::SLUG_INPART,
			__( 'Sales Employees INS', 'workshop-ledger' ),
			__( 'Sales Employees INS', 'workshop-ledger' ),
			$cap,
			'wl-ins-ventas',
			array( $this->report_ventas, 'render' )
		);

		// —— Settings ——
		add_menu_page(
			__( 'Settings', 'workshop-ledger' ),
			__( 'Settings', 'workshop-ledger' ),
			$cap,
			self::SLUG_CONFIG,
			array( $this, 'render_settings' ),
			'dashicons-admin-generic',
			8
		);
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'My Profile', 'workshop-ledger' ),
			__( 'My Profile', 'workshop-ledger' ),
			'read',
			'profile.php'
		);
		$this->cpt_submenu( self::SLUG_CONFIG, __( 'Employees', 'workshop-ledger' ), 'wl_empleado' );
		$this->cpt_submenu( self::SLUG_CONFIG, __( 'History', 'workshop-ledger' ), 'wl_evento' );
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'Integrations', 'workshop-ledger' ),
			__( 'Integrations', 'workshop-ledger' ),
			$cap,
			'wl-config-integrations',
			array( $this->audatex_settings, 'render' )
		);
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'CR cantons', 'workshop-ledger' ),
			__( 'CR cantons', 'workshop-ledger' ),
			$cap,
			'wl-config-cantones',
			array( $this->cantones_settings, 'render' )
		);
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'Tools', 'workshop-ledger' ),
			__( 'Tools', 'workshop-ledger' ),
			$cap,
			'wl-tools',
			array( $this->tools, 'render' )
		);
		add_submenu_page(
			self::SLUG_CONFIG,
			__( 'Roles', 'workshop-ledger' ),
			__( 'Roles', 'workshop-ledger' ),
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
			self::SLUG_INICIO     => array( 'label' => __( 'Home', 'workshop-ledger' ), 'slug' => null ),
			self::SLUG_VEHICULOS  => array( 'label' => __( 'Vehicle list', 'workshop-ledger' ), 'slug' => 'edit.php?post_type=wl_vehiculo' ),
			self::SLUG_INVENTARIO => array( 'label' => __( 'Parts list', 'workshop-ledger' ), 'slug' => 'edit.php?post_type=wl_repuesto' ),
			self::SLUG_FINANZAS   => array( 'label' => __( 'Transactions', 'workshop-ledger' ), 'slug' => 'edit.php?post_type=wl_transaccion' ),
			self::SLUG_REPORTES   => array( 'label' => __( 'Overview', 'workshop-ledger' ), 'slug' => null ),
			self::SLUG_INPART     => array( 'label' => __( 'Orders', 'workshop-ledger' ), 'slug' => 'edit.php?post_type=wl_pedido' ),
			self::SLUG_CONFIG     => array( 'label' => __( 'Settings', 'workshop-ledger' ), 'slug' => null ),
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
		wp_safe_redirect( admin_url( 'edit.php?post_type=wl_vehiculo' ) );
		exit;
	}

	public function redirect_inventario(): void {
		wp_safe_redirect( admin_url( 'edit.php?post_type=wl_repuesto' ) );
		exit;
	}

	public function redirect_finanzas(): void {
		wp_safe_redirect( admin_url( 'edit.php?post_type=wl_transaccion' ) );
		exit;
	}

	public function redirect_inpart(): void {
		wp_safe_redirect( admin_url( 'edit.php?post_type=wl_pedido' ) );
		exit;
	}

	public function register_settings(): void {
		register_setting(
			'wl_settings',
			'wl_org_name',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'Workshop Ledger',
			)
		);
	}

	public function render_dashboard(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), '', array( 'response' => 403 ) );
		}
		$snap   = $this->executive->snapshot();
		$counts = $snap['counts'];
		$org    = (string) get_option( 'wl_org_name', 'Workshop Ledger' );

		$tiles = array(
			array( __( 'Vehicles', 'workshop-ledger' ), __( 'List and catalog', 'workshop-ledger' ), 'dashicons-car', admin_url( 'edit.php?post_type=wl_vehiculo' ) ),
			array( __( 'Inventory', 'workshop-ledger' ), __( 'Parts and stock', 'workshop-ledger' ), 'dashicons-admin-tools', admin_url( 'edit.php?post_type=wl_repuesto' ) ),
			array( __( 'Finance', 'workshop-ledger' ), __( 'Transactions', 'workshop-ledger' ), 'dashicons-money-alt', admin_url( 'edit.php?post_type=wl_transaccion' ) ),
			array( __( 'Audatex InPart', 'workshop-ledger' ), __( 'Orders and INS import', 'workshop-ledger' ), 'dashicons-email-alt', admin_url( 'edit.php?post_type=wl_pedido' ) ),
			array( __( 'Reports', 'workshop-ledger' ), __( 'CSV and digests', 'workshop-ledger' ), 'dashicons-chart-bar', admin_url( 'admin.php?page=' . ReportesAdminPage::SLUG ) ),
			array( __( 'Settings', 'workshop-ledger' ), __( 'Profile and settings', 'workshop-ledger' ), 'dashicons-admin-generic', admin_url( 'admin.php?page=wl-settings' ) ),
		);
		$veh_n = (int) ( $counts['wl_vehiculo'] ?? 0 );
		$ord_n = (int) ( $counts['wl_pedido'] ?? 0 );
		?>
		<div class="wrap wl-admin-wrap">
			<div class="wl-hero">
				<div class="wl-hero__copy">
					<p class="wl-hero__eyebrow"><?php echo $this->escaper->html( __( 'Workshop Ledger', 'workshop-ledger' ) ); ?></p>
					<h1><?php echo $this->escaper->html( $org ); ?></h1>
					<p><?php echo $this->escaper->html( __( 'Native WordPress operations console — same module map as Read-Only, Figma-grade shells without a SPA.', 'workshop-ledger' ) ); ?></p>
				</div>
				<div class="wl-hero__aside" aria-hidden="true">
					<div class="wl-hero-chip">
						<span class="wl-hero-chip__label"><?php echo $this->escaper->html( __( 'Vehicles', 'workshop-ledger' ) ); ?></span>
						<span class="wl-hero-chip__value"><?php echo $this->escaper->html( (string) $veh_n ); ?></span>
					</div>
					<div class="wl-hero-chip">
						<span class="wl-hero-chip__label"><?php echo $this->escaper->html( __( 'Orders', 'workshop-ledger' ) ); ?></span>
						<span class="wl-hero-chip__value"><?php echo $this->escaper->html( (string) $ord_n ); ?></span>
					</div>
					<div class="wl-hero-chip">
						<span class="wl-hero-chip__label"><?php echo $this->escaper->html( __( 'Month balance', 'workshop-ledger' ) ); ?></span>
						<span class="wl-hero-chip__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['finance_month']['balance'], 0 ) ); ?></span>
					</div>
					<div class="wl-hero-chip">
						<span class="wl-hero-chip__label"><?php echo $this->escaper->html( __( 'Est. margin', 'workshop-ledger' ) ); ?></span>
						<span class="wl-hero-chip__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['profitability']['margen_bruto'], 0 ) ); ?></span>
					</div>
				</div>
			</div>

			<h2 class="wl-section-title"><?php echo $this->escaper->html( __( 'Indicators', 'workshop-ledger' ) ); ?></h2>
			<div class="wl-metrics" role="group" aria-label="<?php echo $this->escaper->attr( __( 'Indicators', 'workshop-ledger' ) ); ?>">
				<div class="wl-metric wl-metric--accent">
					<span class="wl-metric__label"><?php echo $this->escaper->html( __( 'Vehicles', 'workshop-ledger' ) ); ?></span>
					<span class="wl-metric__value"><?php echo $this->escaper->html( (string) $veh_n ); ?></span>
				</div>
				<div class="wl-metric">
					<span class="wl-metric__label"><?php echo $this->escaper->html( __( 'Parts', 'workshop-ledger' ) ); ?></span>
					<span class="wl-metric__value"><?php echo $this->escaper->html( (string) ( $counts['wl_repuesto'] ?? 0 ) ); ?></span>
				</div>
				<div class="wl-metric">
					<span class="wl-metric__label"><?php echo $this->escaper->html( __( 'Orders', 'workshop-ledger' ) ); ?></span>
					<span class="wl-metric__value"><?php echo $this->escaper->html( (string) $ord_n ); ?></span>
				</div>
				<div class="wl-metric wl-metric--ok">
					<span class="wl-metric__label"><?php echo $this->escaper->html( __( 'Month balance', 'workshop-ledger' ) ); ?></span>
					<span class="wl-metric__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['finance_month']['balance'], 0 ) ); ?></span>
				</div>
				<div class="wl-metric">
					<span class="wl-metric__label"><?php echo $this->escaper->html( __( 'Inventory ₡', 'workshop-ledger' ) ); ?></span>
					<span class="wl-metric__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['inventory']['valor_inventario'], 0 ) ); ?></span>
				</div>
				<div class="wl-metric">
					<span class="wl-metric__label"><?php echo $this->escaper->html( __( 'Est. margin', 'workshop-ledger' ) ); ?></span>
					<span class="wl-metric__value"><?php echo $this->escaper->html( number_format_i18n( (float) $snap['profitability']['margen_bruto'], 0 ) ); ?></span>
				</div>
			</div>

			<h2 class="wl-section-title"><?php echo $this->escaper->html( __( 'Modules', 'workshop-ledger' ) ); ?></h2>
			<div class="wl-nav-grid">
				<?php foreach ( $tiles as $tile ) : ?>
					<a class="wl-nav-tile" href="<?php echo $this->escaper->url( $tile[3] ); ?>">
						<span class="dashicons <?php echo $this->escaper->attr( $tile[2] ); ?>" aria-hidden="true"></span>
						<span class="wl-nav-tile__title"><?php echo $this->escaper->html( $tile[0] ); ?></span>
						<span class="wl-nav-tile__hint"><?php echo $this->escaper->html( $tile[1] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	public function render_settings(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), '', array( 'response' => 403 ) );
		}
		$org   = get_option( 'wl_org_name', 'Workshop Ledger' );
		$links = array(
			array( __( 'INS/Audatex integrations', 'workshop-ledger' ), admin_url( 'admin.php?page=wl-config-integrations' ) ),
			array( __( 'CR cantons', 'workshop-ledger' ), admin_url( 'admin.php?page=wl-config-cantones' ) ),
			array( __( 'Tools import/export', 'workshop-ledger' ), admin_url( 'admin.php?page=wl-tools' ) ),
			array( __( 'Roles and capabilities', 'workshop-ledger' ), admin_url( 'admin.php?page=' . RolesDocumentationPage::SLUG ) ),
			array( __( 'My WP profile', 'workshop-ledger' ), admin_url( 'profile.php' ) ),
		);
		?>
		<div class="wrap wl-admin-wrap">
			<div class="wl-page-header">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<a href="<?php echo $this->escaper->url( admin_url( 'admin.php?page=' . self::SLUG_INICIO ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'workshop-ledger' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Settings', 'workshop-ledger' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Settings', 'workshop-ledger' ) ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( __( 'Native Options API — Read-Only Configuration parity, Figma-grade shell.', 'workshop-ledger' ) ); ?></p>
				</div>
			</div>
			<div class="wl-shell-grid">
				<form method="post" action="options.php" class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'Organization', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Shown on Home and login branding.', 'workshop-ledger' ) ); ?></p>
					<?php settings_fields( 'wl_settings' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="wl_org_name"><?php echo $this->escaper->html( __( 'Organization name', 'workshop-ledger' ) ); ?></label></th>
							<td><input type="text" class="regular-text" id="wl_org_name" name="wl_org_name" value="<?php echo $this->escaper->attr( (string) $org ); ?>" /></td>
						</tr>
					</table>
					<?php submit_button( __( 'Save changes', 'workshop-ledger' ) ); ?>
				</form>
				<div class="wl-shell-panel">
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'More options', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Integrations, geo, tools, and roles.', 'workshop-ledger' ) ); ?></p>
					<ul class="wl-link-list">
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
