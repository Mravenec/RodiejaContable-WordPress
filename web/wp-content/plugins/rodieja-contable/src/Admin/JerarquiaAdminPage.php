<?php
/**
 * Native admin tree: marca → modelo → generación → vehículo (Read-Only parity).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\CatalogHierarchyService;

final class JerarquiaAdminPage {

	public const SLUG = 'rodieja-vehiculos-jerarquia';

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
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), '', array( 'response' => 403 ) );
		}

		$tree = $this->hierarchy->tree();
		$marca_n = count( $tree );
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-page-head">
				<h1><?php echo $this->escaper->html( __( 'View by Generations', 'rodieja-contable' ) ); ?></h1>
				<p class="rodieja-admin-lede"><?php echo $this->escaper->html( __( 'Brand → Model → Generation → Vehicle tree — same view as Rodieja Contable Read-Only, with native CPT and meta.', 'rodieja-contable' ) ); ?></p>
			</div>

			<div class="rodieja-toolbar">
				<strong><?php echo $this->escaper->html( sprintf( /* translators: %d brand count */ _n( '%d brand', '%d brands', $marca_n, 'rodieja-contable' ), $marca_n ) ); ?></strong>
				<p class="rodieja-toolbar__hint"><?php echo $this->escaper->html( __( 'Expand each level · Edit opens the matching CPT', 'rodieja-contable' ) ); ?></p>
				<a class="button button-primary" href="<?php echo $this->escaper->url( admin_url( 'edit.php?post_type=rodieja_vehiculo' ) ); ?>"><?php echo $this->escaper->html( __( 'Vehicle list', 'rodieja-contable' ) ); ?></a>
			</div>

			<?php if ( empty( $tree ) ) : ?>
				<div class="rodieja-admin-card">
					<p><?php echo $this->escaper->html( __( 'No brands yet. Create Brands, Models, Generations, and Vehicles under Vehicles.', 'rodieja-contable' ) ); ?></p>
				</div>
			<?php else : ?>
				<div class="rodieja-hierarchy" role="tree" aria-label="<?php echo $this->escaper->attr( __( 'Vehicle hierarchy', 'rodieja-contable' ) ); ?>">
					<?php foreach ( $tree as $marca ) : ?>
						<details class="rodieja-hierarchy__marca" open>
							<summary>
								<span class="dashicons dashicons-tag" aria-hidden="true"></span>
								<strong><?php echo $this->escaper->html( (string) $marca['title'] ); ?></strong>
								<span class="rodieja-hierarchy__count"><?php echo $this->escaper->html( (string) count( $marca['modelos'] ) ); ?> <?php echo $this->escaper->html( __( 'models', 'rodieja-contable' ) ); ?></span>
								<a class="rodieja-hierarchy__edit" href="<?php echo $this->escaper->url( get_edit_post_link( (int) $marca['id'], 'raw' ) ?: '#' ); ?>"><?php echo $this->escaper->html( __( 'Edit', 'rodieja-contable' ) ); ?></a>
							</summary>
							<?php foreach ( $marca['modelos'] as $modelo ) : ?>
								<details class="rodieja-hierarchy__modelo" open>
									<summary>
										<span class="dashicons dashicons-category" aria-hidden="true"></span>
										<?php echo $this->escaper->html( (string) $modelo['title'] ); ?>
										<span class="rodieja-hierarchy__count"><?php echo $this->escaper->html( (string) count( $modelo['generaciones'] ) ); ?> <?php echo $this->escaper->html( __( 'generations', 'rodieja-contable' ) ); ?></span>
										<a class="rodieja-hierarchy__edit" href="<?php echo $this->escaper->url( get_edit_post_link( (int) $modelo['id'], 'raw' ) ?: '#' ); ?>"><?php echo $this->escaper->html( __( 'Edit', 'rodieja-contable' ) ); ?></a>
									</summary>
									<?php foreach ( $modelo['generaciones'] as $gen ) : ?>
										<details class="rodieja-hierarchy__gen" open>
											<summary>
												<span class="dashicons dashicons-networking" aria-hidden="true"></span>
												<?php echo $this->escaper->html( (string) $gen['title'] ); ?>
												<?php if ( '' !== (string) $gen['anio_inicio'] || '' !== (string) $gen['anio_fin'] ) : ?>
													<span class="rodieja-hierarchy__years"><?php echo $this->escaper->html( (string) $gen['anio_inicio'] ); ?>–<?php echo $this->escaper->html( (string) $gen['anio_fin'] ); ?></span>
												<?php endif; ?>
												<span class="rodieja-hierarchy__count"><?php echo $this->escaper->html( (string) count( $gen['vehiculos'] ) ); ?> <?php echo $this->escaper->html( __( 'vehicles', 'rodieja-contable' ) ); ?></span>
												<a class="rodieja-hierarchy__edit" href="<?php echo $this->escaper->url( get_edit_post_link( (int) $gen['id'], 'raw' ) ?: '#' ); ?>"><?php echo $this->escaper->html( __( 'Edit', 'rodieja-contable' ) ); ?></a>
											</summary>
											<?php if ( empty( $gen['vehiculos'] ) ) : ?>
												<p class="rodieja-hierarchy__empty"><?php echo $this->escaper->html( __( 'No vehicles in this generation.', 'rodieja-contable' ) ); ?></p>
											<?php else : ?>
												<ul class="rodieja-hierarchy__vehiculos">
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
																<span class="rodieja-hierarchy__years"><?php echo $this->escaper->html( (string) $veh['anio'] ); ?></span>
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
