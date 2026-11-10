<?php
/**
 * Admin page: critical stock (native WP_Query meta_query).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;

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
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		$q = new \WP_Query(
			array(
				'post_type'      => 'wl_repuesto',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'meta_query'     => array(
					array(
						'key'     => 'wl_stock',
						'value'   => self::THRESHOLD,
						'compare' => '<=',
						'type'    => 'NUMERIC',
					),
				),
				'orderby'        => 'meta_value_num',
				'meta_key'       => 'wl_stock',
				'order'          => 'ASC',
			)
		);
		?>
		<div class="wrap wl-admin-wrap">
			<h1><?php echo $this->escaper->html( __( 'Critical stock', 'workshop-ledger' ) ); ?></h1>
			<p><?php echo $this->escaper->html( sprintf( __( 'Parts with stock ≤ %d (native WP_Query).', 'workshop-ledger' ), self::THRESHOLD ) ); ?></p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php echo $this->escaper->html( __( 'Part', 'workshop-ledger' ) ); ?></th>
						<th><?php echo $this->escaper->html( __( 'Stock', 'workshop-ledger' ) ); ?></th>
						<th><?php echo $this->escaper->html( __( 'Price', 'workshop-ledger' ) ); ?></th>
						<th><?php echo $this->escaper->html( __( 'Vehicle', 'workshop-ledger' ) ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( ! $q->have_posts() ) : ?>
					<tr><td colspan="4"><?php echo $this->escaper->html( __( 'No critical items.', 'workshop-ledger' ) ); ?></td></tr>
				<?php else : ?>
					<?php
					while ( $q->have_posts() ) :
						$q->the_post();
						$id    = get_the_ID();
						$stock = (int) get_post_meta( $id, 'wl_stock', true );
						$price = (float) get_post_meta( $id, 'wl_precio', true );
						$vid   = (int) get_post_meta( $id, 'wl_vehiculo_id', true );
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
