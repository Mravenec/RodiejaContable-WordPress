<?php
/**
 * Admin page: critical stock (native WP_Query meta_query).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\OutputEscaper;

final class CriticalStockPage {

	public const THRESHOLD = 1;

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		// Rendered via AdminMenu submenu.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$q = new \WP_Query(
			array(
				'post_type'      => 'rodieja_repuesto',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'meta_query'     => array(
					array(
						'key'     => 'rodieja_stock',
						'value'   => self::THRESHOLD,
						'compare' => '<=',
						'type'    => 'NUMERIC',
					),
				),
				'orderby'        => 'meta_value_num',
				'meta_key'       => 'rodieja_stock',
				'order'          => 'ASC',
			)
		);
		?>
		<div class="wrap rodieja-admin-wrap">
			<h1><?php echo $this->escaper->html( __( 'Stock crítico', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( sprintf( __( 'Repuestos con stock ≤ %d (WP_Query nativo).', 'rodieja-contable' ), self::THRESHOLD ) ); ?></p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo $this->escaper->html( __( 'Repuesto', 'rodieja-contable' ) ); ?></th>
						<th><?php echo $this->escaper->html( __( 'Stock', 'rodieja-contable' ) ); ?></th>
						<th><?php echo $this->escaper->html( __( 'Precio', 'rodieja-contable' ) ); ?></th>
						<th><?php echo $this->escaper->html( __( 'Vehículo', 'rodieja-contable' ) ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( ! $q->have_posts() ) : ?>
					<tr><td colspan="4"><?php echo $this->escaper->html( __( 'Sin ítems críticos.', 'rodieja-contable' ) ); ?></td></tr>
				<?php else : ?>
					<?php
					while ( $q->have_posts() ) :
						$q->the_post();
						$id    = get_the_ID();
						$stock = (int) get_post_meta( $id, 'rodieja_stock', true );
						$price = (float) get_post_meta( $id, 'rodieja_precio', true );
						$vid   = (int) get_post_meta( $id, 'rodieja_vehiculo_id', true );
						?>
						<tr>
							<td><a href="<?php echo $this->escaper->attr( (string) get_edit_post_link( $id ) ); ?>"><?php echo $this->escaper->html( get_the_title() ); ?></a></td>
							<td><?php echo $this->escaper->html( (string) $stock ); ?></td>
							<td><?php echo $this->escaper->html( number_format_i18n( $price, 2 ) ); ?></td>
							<td><?php echo $vid ? $this->escaper->html( get_the_title( $vid ) ) : '—'; ?></td>
						</tr>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
