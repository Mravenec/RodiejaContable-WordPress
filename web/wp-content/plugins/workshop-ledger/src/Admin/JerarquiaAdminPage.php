<?php
/**
 * Native admin tree: marca → modelo → generación → vehículo (Read-Only parity).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\CatalogHierarchyService;

final class JerarquiaAdminPage {

	public const SLUG = 'wl-vehiculos-jerarquia';

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private CatalogHierarchyService $hierarchy
	) {}

	public function register(): void {
		// Rendered via AdminMenu submenu.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), '', array( 'response' => 403 ) );
		}

		$tree     = $this->hierarchy->tree();
		$marca_n  = count( $tree );
		?>
		<div class="wrap wl-admin-wrap">
			<div class="wl-page-header">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<a href="<?php echo $this->escaper->url( admin_url( 'admin.php?page=wl-vehiculos' ) ); ?>"><?php echo $this->escaper->html( __( 'Vehicles', 'workshop-ledger' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'View by Generations', 'workshop-ledger' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'View by Generations', 'workshop-ledger' ) ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( __( 'Brand → Model → Generation → Vehicle — expand a vehicle for costs, parts, and transactions (native details).', 'workshop-ledger' ) ); ?></p>
				</div>
				<div class="wl-page-header__actions">
					<a class="button button-primary" href="<?php echo $this->escaper->url( admin_url( 'edit.php?post_type=wl_vehiculo' ) ); ?>"><?php echo $this->escaper->html( __( 'Vehicle list', 'workshop-ledger' ) ); ?></a>
					<a class="button" href="<?php echo $this->escaper->url( admin_url( 'post-new.php?post_type=wl_vehiculo' ) ); ?>"><?php echo $this->escaper->html( __( 'Add Vehicle', 'workshop-ledger' ) ); ?></a>
				</div>
			</div>

			<div class="wl-toolbar">
				<strong><?php echo $this->escaper->html( sprintf( /* translators: %d brand count */ _n( '%d brand', '%d brands', $marca_n, 'workshop-ledger' ), $marca_n ) ); ?></strong>
				<p class="wl-toolbar__hint"><?php echo $this->escaper->html( __( 'Expand each level · Expand a vehicle for detail · Edit opens the matching CPT', 'workshop-ledger' ) ); ?></p>
			</div>

			<?php if ( empty( $tree ) ) : ?>
				<div class="wl-admin-card">
					<p><?php echo $this->escaper->html( __( 'No brands yet. Create Brands, Models, Generations, and Vehicles under Vehicles.', 'workshop-ledger' ) ); ?></p>
				</div>
			<?php else : ?>
				<div class="wl-hierarchy" role="tree" aria-label="<?php echo $this->escaper->attr( __( 'Vehicle hierarchy', 'workshop-ledger' ) ); ?>">
					<?php foreach ( $tree as $marca ) : ?>
						<details class="wl-hierarchy__marca" open>
							<summary>
								<span class="dashicons dashicons-tag" aria-hidden="true"></span>
								<strong><?php echo $this->escaper->html( (string) $marca['title'] ); ?></strong>
								<span class="wl-hierarchy__count"><?php echo $this->escaper->html( (string) count( $marca['modelos'] ) ); ?> <?php echo $this->escaper->html( __( 'models', 'workshop-ledger' ) ); ?></span>
								<a class="wl-hierarchy__edit" href="<?php echo $this->escaper->url( get_edit_post_link( (int) $marca['id'], 'raw' ) ?: '#' ); ?>"><?php echo $this->escaper->html( __( 'Edit', 'workshop-ledger' ) ); ?></a>
							</summary>
							<?php foreach ( $marca['modelos'] as $modelo ) : ?>
								<details class="wl-hierarchy__modelo" open>
									<summary>
										<span class="dashicons dashicons-category" aria-hidden="true"></span>
										<?php echo $this->escaper->html( (string) $modelo['title'] ); ?>
										<span class="wl-hierarchy__count"><?php echo $this->escaper->html( (string) count( $modelo['generaciones'] ) ); ?> <?php echo $this->escaper->html( __( 'generations', 'workshop-ledger' ) ); ?></span>
										<a class="wl-hierarchy__edit" href="<?php echo $this->escaper->url( get_edit_post_link( (int) $modelo['id'], 'raw' ) ?: '#' ); ?>"><?php echo $this->escaper->html( __( 'Edit', 'workshop-ledger' ) ); ?></a>
									</summary>
									<?php foreach ( $modelo['generaciones'] as $gen ) : ?>
										<details class="wl-hierarchy__gen" open>
											<summary>
												<span class="dashicons dashicons-networking" aria-hidden="true"></span>
												<?php echo $this->escaper->html( (string) $gen['title'] ); ?>
												<?php if ( '' !== (string) $gen['anio_inicio'] || '' !== (string) $gen['anio_fin'] ) : ?>
													<span class="wl-hierarchy__years"><?php echo $this->escaper->html( (string) $gen['anio_inicio'] ); ?>–<?php echo $this->escaper->html( (string) $gen['anio_fin'] ); ?></span>
												<?php endif; ?>
												<span class="wl-hierarchy__count"><?php echo $this->escaper->html( (string) count( $gen['vehiculos'] ) ); ?> <?php echo $this->escaper->html( __( 'vehicles', 'workshop-ledger' ) ); ?></span>
												<a class="wl-hierarchy__edit" href="<?php echo $this->escaper->url( get_edit_post_link( (int) $gen['id'], 'raw' ) ?: '#' ); ?>"><?php echo $this->escaper->html( __( 'Edit', 'workshop-ledger' ) ); ?></a>
											</summary>
											<?php if ( empty( $gen['vehiculos'] ) ) : ?>
												<p class="wl-hierarchy__empty"><?php echo $this->escaper->html( __( 'No vehicles in this generation.', 'workshop-ledger' ) ); ?></p>
											<?php else : ?>
												<ul class="wl-hierarchy__vehiculos">
													<?php foreach ( $gen['vehiculos'] as $veh ) : ?>
														<li class="wl-hierarchy__vehiculo">
															<?php $this->render_vehicle( $veh ); ?>
														</li>
													<?php endforeach; ?>
												</ul>
											<?php endif; ?>
										</details>
									<?php endforeach; ?>
								</details>
							<?php endforeach; ?>
						</details>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed> $veh
	 */
	private function render_vehicle( array $veh ): void {
		$codigo   = (string) ( $veh['codigo'] ?? '' );
		$label    = '' !== $codigo ? $codigo : (string) ( $veh['title'] ?? '' );
		$anio     = (string) ( $veh['anio'] ?? '' );
		$estado   = (string) ( $veh['estado'] ?? '' );
		$thumb    = (string) ( $veh['thumb_url'] ?? '' );
		$edit     = (string) ( $veh['edit_url'] ?? '#' );
		$parts    = is_array( $veh['repuestos'] ?? null ) ? $veh['repuestos'] : array();
		$txs      = is_array( $veh['transacciones'] ?? null ) ? $veh['transacciones'] : array();
		?>
		<details class="wl-hierarchy__veh-detail">
			<summary class="wl-hierarchy__veh-summary">
				<?php if ( '' !== $thumb ) : ?>
					<img class="wl-hierarchy__thumb" src="<?php echo $this->escaper->url( $thumb ); ?>" alt="" width="40" height="40" />
				<?php else : ?>
					<span class="wl-hierarchy__thumb wl-hierarchy__thumb--empty dashicons dashicons-car" aria-hidden="true"></span>
				<?php endif; ?>
				<span class="wl-hierarchy__veh-main">
					<strong><?php echo $this->escaper->html( $label ); ?></strong>
					<?php if ( '' !== $anio ) : ?>
						<span class="wl-hierarchy__years"><?php echo $this->escaper->html( $anio ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $estado ) : ?>
						<span class="wl-hierarchy__estado"><?php echo $this->escaper->html( $estado ); ?></span>
					<?php endif; ?>
				</span>
				<span class="wl-hierarchy__veh-money">
					<span class="wl-hierarchy__money-label"><?php echo $this->escaper->html( __( 'Investment', 'workshop-ledger' ) ); ?></span>
					<strong><?php echo $this->escaper->html( $this->money( (float) ( $veh['inversion_total'] ?? 0 ) ) ); ?></strong>
				</span>
				<a class="wl-hierarchy__edit" href="<?php echo $this->escaper->url( $edit ); ?>" onclick="event.stopPropagation();"><?php echo $this->escaper->html( __( 'Edit', 'workshop-ledger' ) ); ?></a>
			</summary>

			<div class="wl-hierarchy__veh-body">
				<div class="wl-hierarchy__metrics">
					<div>
						<span class="wl-hierarchy__money-label"><?php echo $this->escaper->html( __( 'Intake date', 'workshop-ledger' ) ); ?></span>
						<strong><?php echo $this->escaper->html( (string) ( $veh['fecha_ingreso'] ?: '—' ) ); ?></strong>
					</div>
					<div>
						<span class="wl-hierarchy__money-label"><?php echo $this->escaper->html( __( 'Purchase', 'workshop-ledger' ) ); ?></span>
						<strong><?php echo $this->escaper->html( $this->money( (float) ( $veh['precio_compra'] ?? 0 ) ) ); ?></strong>
					</div>
					<div>
						<span class="wl-hierarchy__money-label"><?php echo $this->escaper->html( __( 'Tow', 'workshop-ledger' ) ); ?></span>
						<strong><?php echo $this->escaper->html( $this->money( (float) ( $veh['costo_grua'] ?? 0 ) ) ); ?></strong>
					</div>
					<div>
						<span class="wl-hierarchy__money-label"><?php echo $this->escaper->html( __( 'Commissions', 'workshop-ledger' ) ); ?></span>
						<strong><?php echo $this->escaper->html( $this->money( (float) ( $veh['comisiones'] ?? 0 ) ) ); ?></strong>
					</div>
					<div>
						<span class="wl-hierarchy__money-label"><?php echo $this->escaper->html( __( 'Recovered', 'workshop-ledger' ) ); ?></span>
						<strong><?php echo $this->escaper->html( $this->money( (float) ( $veh['costo_recuperado'] ?? 0 ) ) ); ?></strong>
					</div>
					<div>
						<span class="wl-hierarchy__money-label"><?php echo $this->escaper->html( __( 'Pending', 'workshop-ledger' ) ); ?></span>
						<strong class="<?php echo ( (float) ( $veh['costo_pendiente'] ?? 0 ) ) > 0 ? 'wl-hierarchy__pending' : 'wl-hierarchy__ok'; ?>">
							<?php echo $this->escaper->html( $this->money( (float) ( $veh['costo_pendiente'] ?? 0 ) ) ); ?>
						</strong>
					</div>
				</div>

				<div class="wl-hierarchy__related">
					<section>
						<h3><?php echo $this->escaper->html( sprintf( /* translators: %d count */ __( 'Transactions (%d)', 'workshop-ledger' ), count( $txs ) ) ); ?></h3>
						<?php if ( empty( $txs ) ) : ?>
							<p class="wl-hierarchy__empty"><?php echo $this->escaper->html( __( 'No transactions linked.', 'workshop-ledger' ) ); ?></p>
						<?php else : ?>
							<ul>
								<?php foreach ( $txs as $tx ) : ?>
									<li>
										<a href="<?php echo $this->escaper->url( (string) $tx['edit_url'] ); ?>"><?php echo $this->escaper->html( (string) $tx['title'] ); ?></a>
										<span class="wl-hierarchy__years"><?php echo $this->escaper->html( (string) $tx['fecha'] ); ?></span>
										<span class="wl-hierarchy__estado"><?php echo $this->escaper->html( (string) $tx['sentido'] ); ?></span>
										<strong><?php echo $this->escaper->html( $this->money( (float) $tx['monto'] ) ); ?></strong>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</section>
					<section>
						<h3><?php echo $this->escaper->html( sprintf( /* translators: %d count */ __( 'Parts (%d)', 'workshop-ledger' ), count( $parts ) ) ); ?></h3>
						<?php if ( empty( $parts ) ) : ?>
							<p class="wl-hierarchy__empty"><?php echo $this->escaper->html( __( 'No parts linked.', 'workshop-ledger' ) ); ?></p>
						<?php else : ?>
							<ul>
								<?php foreach ( $parts as $part ) : ?>
									<li>
										<a href="<?php echo $this->escaper->url( (string) $part['edit_url'] ); ?>"><?php echo $this->escaper->html( (string) $part['title'] ); ?></a>
										<span class="wl-hierarchy__years"><?php echo $this->escaper->html( sprintf( /* translators: %d stock */ __( 'Stock %d', 'workshop-ledger' ), (int) $part['stock'] ) ); ?></span>
										<strong><?php echo $this->escaper->html( $this->money( (float) $part['precio'] ) ); ?></strong>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</section>
				</div>
			</div>
		</details>
		<?php
	}

	private function money( float $amount ): string {
		return number_format_i18n( $amount, 2 );
	}
}
