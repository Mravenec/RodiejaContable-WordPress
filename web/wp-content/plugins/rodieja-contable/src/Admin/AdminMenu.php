<?php
/**
 * Rodieja Contable admin hub — React-shaped sidebar (native WP menus).
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
			24
		);

		add_submenu_page(
			self::SLUG,
			__( 'Inicio', 'rodieja-contable' ),
			__( 'Inicio', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			self::SLUG,
			array( $this, 'render_dashboard' )
		);

		$this->add_section( __( 'Vehículos', 'rodieja-contable' ) );
		$this->add_cpt_link( __( 'Lista', 'rodieja-contable' ), 'rodieja_vehiculo' );
		$this->add_cpt_link( __( 'Marcas', 'rodieja-contable' ), 'rodieja_marca' );
		$this->add_cpt_link( __( 'Modelos', 'rodieja-contable' ), 'rodieja_modelo' );
		$this->add_cpt_link( __( 'Generaciones', 'rodieja-contable' ), 'rodieja_generacion' );
		$this->add_new_link( __( 'Nuevo vehículo', 'rodieja-contable' ), 'rodieja_vehiculo' );

		$this->add_section( __( 'Inventario', 'rodieja-contable' ) );
		$this->add_cpt_link( __( 'Lista repuestos', 'rodieja-contable' ), 'rodieja_repuesto' );
		add_submenu_page(
			self::SLUG,
			__( 'Stock crítico', 'rodieja-contable' ),
			__( 'Stock crítico', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-stock-critico',
			array( $this->critical_stock, 'render' )
		);
		$this->add_new_link( __( 'Nuevo repuesto', 'rodieja-contable' ), 'rodieja_repuesto' );

		$this->add_section( __( 'Finanzas', 'rodieja-contable' ) );
		$this->add_cpt_link( __( 'Transacciones', 'rodieja-contable' ), 'rodieja_transaccion' );
		add_submenu_page(
			self::SLUG,
			__( 'Dashboard montos', 'rodieja-contable' ),
			__( 'Dashboard montos', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-finanzas-dashboard',
			array( $this->finance_dashboard, 'render' )
		);
		$this->add_new_link( __( 'Nueva transacción', 'rodieja-contable' ), 'rodieja_transaccion' );

		$this->add_section( __( 'Reportes', 'rodieja-contable' ) );
		add_submenu_page(
			self::SLUG,
			__( 'Ventas', 'rodieja-contable' ),
			__( 'Ventas', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-reporte-ventas',
			array( $this->report_ventas, 'render' )
		);
		add_submenu_page(
			self::SLUG,
			__( 'Inventario', 'rodieja-contable' ),
			__( 'Inventario', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-reporte-inventario',
			array( $this->report_inventario, 'render' )
		);
		add_submenu_page(
			self::SLUG,
			__( 'Finanzas', 'rodieja-contable' ),
			__( 'Finanzas', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-reporte-finanzas',
			array( $this->report_finanzas, 'render' )
		);
		add_submenu_page(
			self::SLUG,
			__( 'Exportación y resumen', 'rodieja-contable' ),
			__( 'Exportación', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			ReportesAdminPage::SLUG,
			array( $this->reportes_hub, 'render' )
		);

		$this->add_section( __( 'Cotizaciones InPart', 'rodieja-contable' ) );
		$this->add_cpt_link( __( 'Pedidos', 'rodieja-contable' ), 'rodieja_pedido' );
		add_submenu_page(
			self::SLUG,
			__( 'Importar cotización', 'rodieja-contable' ),
			__( 'Importar cotización', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-inpart-import',
			array( $this->pedido_import, 'render' )
		);

		$this->add_section( __( 'Configuración', 'rodieja-contable' ) );
		add_submenu_page(
			self::SLUG,
			__( 'Perfil', 'rodieja-contable' ),
			__( 'Perfil', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-profile',
			array( $this, 'redirect_profile' )
		);
		add_submenu_page(
			self::SLUG,
			__( 'Ajustes', 'rodieja-contable' ),
			__( 'Ajustes', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-settings',
			array( $this, 'render_settings' )
		);
		add_submenu_page(
			self::SLUG,
			__( 'INS / Audatex', 'rodieja-contable' ),
			__( 'Integraciones', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-config-integrations',
			array( $this->audatex_settings, 'render' )
		);
		add_submenu_page(
			self::SLUG,
			__( 'Cantones CR', 'rodieja-contable' ),
			__( 'Cantones', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-config-cantones',
			array( $this->cantones_settings, 'render' )
		);
		add_submenu_page(
			self::SLUG,
			__( 'Tools', 'rodieja-contable' ),
			__( 'Tools', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			'rodieja-tools',
			array( $this->tools, 'render' )
		);
		add_submenu_page(
			self::SLUG,
			__( 'Roles y capacidades', 'rodieja-contable' ),
			__( 'Roles', 'rodieja-contable' ),
			CapabilityGate::MANAGE,
			RolesDocumentationPage::SLUG,
			array( $this->roles_doc, 'render' )
		);
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
			'rodieja-reporte-ventas',
			'rodieja-reporte-inventario',
			'rodieja-reporte-finanzas',
			ReportesAdminPage::SLUG,
			RolesDocumentationPage::SLUG,
			'rodieja-inpart-import',
			'rodieja-profile',
			'rodieja-settings',
			'rodieja-config-integrations',
			'rodieja-config-cantones',
			'rodieja-tools',
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
			if ( preg_match( '#^(edit|post-new)\.php\?post_type=rodieja_#', $slug ) ) {
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
		$labels = array(
			'rodieja_marca'        => __( 'Marcas', 'rodieja-contable' ),
			'rodieja_modelo'       => __( 'Modelos', 'rodieja-contable' ),
			'rodieja_generacion'   => __( 'Generaciones', 'rodieja-contable' ),
			'rodieja_vehiculo'     => __( 'Vehículos', 'rodieja-contable' ),
			'rodieja_repuesto'     => __( 'Repuestos', 'rodieja-contable' ),
			'rodieja_empleado'     => __( 'Empleados', 'rodieja-contable' ),
			'rodieja_transaccion'  => __( 'Transacciones', 'rodieja-contable' ),
			'rodieja_pedido'       => __( 'Pedidos', 'rodieja-contable' ),
		);
		?>
		<div class="wrap rodieja-admin-wrap">
			<h1><?php echo $this->escaper->html( __( 'Rodieja Contable', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Dashboard ejecutivo — conteos y montos vía WP_Query + meta.', 'rodieja-contable' ) ); ?></p>
			<div class="rodieja-admin-card">
				<h2 class="title"><?php echo $this->escaper->html( __( 'Conteos', 'rodieja-contable' ) ); ?></h2>
				<table class="widefat striped" style="max-width:520px">
					<thead>
						<tr><th><?php echo $this->escaper->html( __( 'Dominio', 'rodieja-contable' ) ); ?></th><th><?php echo $this->escaper->html( __( 'Publicados', 'rodieja-contable' ) ); ?></th></tr>
					</thead>
					<tbody>
					<?php foreach ( $counts as $type => $n ) : ?>
						<tr>
							<th><?php echo $this->escaper->html( $labels[ $type ] ?? (string) $type ); ?></th>
							<td><?php echo $this->escaper->html( (string) $n ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div class="rodieja-admin-card" style="margin-top:16px">
				<h2 class="title"><?php echo $this->escaper->html( __( 'Montos (mes actual)', 'rodieja-contable' ) ); ?></h2>
				<table class="widefat striped" style="max-width:520px">
					<tbody>
						<tr><th><?php echo $this->escaper->html( __( 'Ingresos', 'rodieja-contable' ) ); ?></th><td><?php echo $this->escaper->html( number_format_i18n( $snap['finance_month']['ingreso'], 2 ) ); ?></td></tr>
						<tr><th><?php echo $this->escaper->html( __( 'Egresos', 'rodieja-contable' ) ); ?></th><td><?php echo $this->escaper->html( number_format_i18n( $snap['finance_month']['egreso'], 2 ) ); ?></td></tr>
						<tr><th><?php echo $this->escaper->html( __( 'Balance', 'rodieja-contable' ) ); ?></th><td><?php echo $this->escaper->html( number_format_i18n( $snap['finance_month']['balance'], 2 ) ); ?></td></tr>
						<tr><th><?php echo $this->escaper->html( __( 'Valor inventario', 'rodieja-contable' ) ); ?></th><td><?php echo $this->escaper->html( number_format_i18n( (float) $snap['inventory']['valor_inventario'], 2 ) ); ?></td></tr>
						<tr><th><?php echo $this->escaper->html( __( 'Pedidos (mes)', 'rodieja-contable' ) ); ?></th><td><?php echo $this->escaper->html( number_format_i18n( (float) $snap['sales_month']['total_pedidos'], 2 ) ); ?></td></tr>
						<tr><th><?php echo $this->escaper->html( __( 'Margen bruto est.', 'rodieja-contable' ) ); ?></th><td><?php echo $this->escaper->html( number_format_i18n( (float) $snap['profitability']['margen_bruto'], 2 ) ); ?></td></tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	public function render_settings(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), '', array( 'response' => 403 ) );
		}
		$org = get_option( 'rodieja_contable_org_name', 'Rodieja Contable' );
		?>
		<div class="wrap rodieja-admin-wrap">
			<h1><?php echo $this->escaper->html( __( 'Ajustes', 'rodieja-contable' ) ); ?></h1>
			<form method="post" action="options.php" class="rodieja-admin-card">
				<?php
				settings_fields( 'rodieja_contable_settings' );
				?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="rodieja_contable_org_name"><?php echo $this->escaper->html( __( 'Nombre organización', 'rodieja-contable' ) ); ?></label></th>
						<td>
							<input type="text" class="regular-text" id="rodieja_contable_org_name" name="rodieja_contable_org_name" value="<?php echo $this->escaper->attr( (string) $org ); ?>" />
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	public function redirect_profile(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), '', array( 'response' => 403 ) );
		}
		wp_safe_redirect( admin_url( 'profile.php' ) );
		exit;
	}

	/**
	 * @param string $title Section heading in sidebar.
	 */
	private function add_section( string $title ): void {
		$slug = 'rodieja-menu-sep-' . sanitize_title( $title );
		add_submenu_page(
			self::SLUG,
			$title,
			$title,
			CapabilityGate::MANAGE,
			$slug,
			'__return_null'
		);
	}

	private function add_cpt_link( string $label, string $post_type ): void {
		add_submenu_page(
			self::SLUG,
			$label,
			$label,
			CapabilityGate::MANAGE,
			'edit.php?post_type=' . $post_type
		);
	}

	private function add_new_link( string $label, string $post_type ): void {
		add_submenu_page(
			self::SLUG,
			$label,
			$label,
			CapabilityGate::MANAGE,
			'post-new.php?post_type=' . $post_type
		);
	}

	private function add_stub( string $label, string $page_slug, ?string $message = null ): void {
		add_submenu_page(
			self::SLUG,
			$label,
			$label,
			CapabilityGate::MANAGE,
			$page_slug,
			function () use ( $label, $message ): void {
				if ( ! $this->gate->can_manage() ) {
					wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), '', array( 'response' => 403 ) );
				}
				$text = $message ?? __( 'Pantalla en construcción.', 'rodieja-contable' );
				echo '<div class="wrap rodieja-admin-wrap">';
				echo '<h1>' . $this->escaper->html( $label ) . '</h1>';
				echo '<div class="rodieja-admin-card"><p>' . $this->escaper->html( $text ) . '</p></div>';
				echo '</div>';
			}
		);
	}
}
