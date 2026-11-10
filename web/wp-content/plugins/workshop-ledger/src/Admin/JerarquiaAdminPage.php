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

		$tree = $this->hierarchy->tree();
		$marca_n = count( $tree );
		?>
		<div class="wrap wl-admin-wrap">
			<div class="wl-page-header">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<a href="<?php echo $this->escaper->url( admin_url( 'admin.php?page=wl-vehiculos' ) ); ?>"><?php echo $this->escaper->html( __( 'Vehicles', 'workshop-ledger' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'View by Generations', 'workshop-ledger' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'View by Generations', 'workshop-ledger' ) ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( __( 'Brand → Model → Generation → Vehicle — Read-Only tree parity on native CPT/meta.', 'workshop-ledger' ) ); ?></p>
				</div>
				<div class="wl-page-header__actions">
					<a class="button button-primary" href="<?php echo $this->escaper->url( admin_url( 'edit.php?post_type=wl_vehiculo' ) ); ?>"><?php echo $this->escaper->html( __( 'Vehicle list', 'workshop-ledger' ) ); ?></a>
					<a class="button" href="<?php echo $this->escaper->url( admin_url( 'post-new.php?post_type=wl_vehiculo' ) ); ?>"><?php echo $this->escaper->html( __( 'Add Vehicle', 'workshop-ledger' ) ); ?></a>
				</div>
			</div>

			<div class="wl-toolbar">
				<strong><?php echo $this->escaper->html( sprintf( /* translators: %d brand count */ _n( '%d brand', '%d brands', $marca_n, 'workshop-ledger' ), $marca_n ) ); ?></strong>
				<p class="wl-toolbar__hint"><?php echo $this->escaper->html( __( 'Expand each level · Edit opens the matching CPT', 'workshop-ledger' ) ); ?></p>
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
														<li>
															<span class="dashicons dashicons-car" aria-hidden="true"></span>
															<a href="<?php echo $this->escaper->url( (string) $veh['edit_url'] ); ?>">
																<?php echo $this->escaper->html( (string) $veh['title'] ); ?>
															</a>
															<?php if ( '' !== (string) $veh['codigo'] ) : ?>
																<code><?php echo $this->escaper->html( (string) $veh['codigo'] ); ?></code>
															<?php endif; ?>
															<?php if ( '' !== (string) $veh['anio'] ) : ?>
																<span class="wl-hierarchy__years"><?php echo $this->escaper->html( (string) $veh['anio'] ); ?></span>
															<?php endif; ?>
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
}
