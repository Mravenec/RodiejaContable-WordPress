<?php
/**
 * Product page-header above native CPT / taxonomy list tables.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\OutputEscaper;

final class CptListChrome {

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate
	) {}

	public function register(): void {
		add_action( 'all_admin_notices', array( $this, 'render_header' ), 1 );
	}

	public function render_header(): void {
		if ( ! $this->gate->can_manage() && ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! ( $screen instanceof \WP_Screen ) ) {
			return;
		}

		$title = '';
		$lede  = '';
		$add   = null;

		if ( 'edit' === $screen->base && is_string( $screen->post_type ) && str_starts_with( $screen->post_type, 'wl_' ) ) {
			$obj = get_post_type_object( $screen->post_type );
			if ( ! $obj ) {
				return;
			}
			$title = (string) $obj->labels->name;
			$lede  = sprintf(
				/* translators: %s: post type singular name */
				__( 'Native WordPress list · manage %s with the same product shell as Read-Only.', 'workshop-ledger' ),
				(string) $obj->labels->singular_name
			);
			if ( current_user_can( $obj->cap->create_posts ) ) {
				$add = array(
					'url'   => admin_url( 'post-new.php?post_type=' . $screen->post_type ),
					'label' => (string) $obj->labels->add_new_item,
				);
			}
		} elseif ( in_array( $screen->base, array( 'edit-tags', 'term' ), true ) && is_string( $screen->taxonomy ) && str_starts_with( $screen->taxonomy, 'wl_' ) ) {
			$tax = get_taxonomy( $screen->taxonomy );
			if ( ! $tax ) {
				return;
			}
			$title = (string) $tax->labels->name;
			$lede  = __( 'Native taxonomy terms · Figma-grade shell on WordPress core screens.', 'workshop-ledger' );
		} else {
			return;
		}

		$crumbs = array(
			array(
				'label' => __( 'Home', 'workshop-ledger' ),
				'url'   => admin_url( 'admin.php?page=workshop-ledger' ),
			),
			array(
				'label' => $title,
				'url'   => null,
			),
		);
		?>
		<div class="wl-list-chrome" role="region" aria-label="<?php echo $this->escaper->attr( $title ); ?>">
			<div class="wl-page-header wl-page-header--list">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<?php foreach ( $crumbs as $i => $crumb ) : ?>
							<?php if ( $i > 0 ) : ?>
								<span class="wl-page-header__sep">·</span>
							<?php endif; ?>
							<?php if ( ! empty( $crumb['url'] ) ) : ?>
								<a href="<?php echo $this->escaper->url( (string) $crumb['url'] ); ?>"><?php echo $this->escaper->html( (string) $crumb['label'] ); ?></a>
							<?php else : ?>
								<span><?php echo $this->escaper->html( (string) $crumb['label'] ); ?></span>
							<?php endif; ?>
						<?php endforeach; ?>
					</p>
					<h1><?php echo $this->escaper->html( $title ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( $lede ); ?></p>
				</div>
				<?php if ( is_array( $add ) ) : ?>
					<div class="wl-page-header__actions">
						<a class="button button-primary" href="<?php echo $this->escaper->url( (string) $add['url'] ); ?>">
							<?php echo $this->escaper->html( (string) $add['label'] ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
