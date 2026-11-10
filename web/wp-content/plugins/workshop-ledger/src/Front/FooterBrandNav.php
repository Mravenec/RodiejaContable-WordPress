<?php
/**
 * Replace Twenty Twenty-Five footer placeholder nav (# Blog/FAQs/…) with Workshop Ledger links.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Front;

final class FooterBrandNav {

	public function register(): void {
		add_filter( 'render_block_core/template-part', array( $this, 'filter_footer' ), 10, 2 );
	}

	/**
	 * @param array<string, mixed> $block
	 */
	public function filter_footer( string $block_content, array $block ): string {
		$attrs = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
		$slug  = (string) ( $attrs['slug'] ?? '' );
		if ( 'footer' !== $slug && ! str_contains( $block_content, '>FAQs<' ) ) {
			return $block_content;
		}

		$home  = esc_url( home_url( '/' ) );
		$about = esc_url( home_url( '/about/' ) );
		$app   = is_user_logged_in()
			? esc_url( admin_url( 'admin.php?page=workshop-ledger' ) )
			: esc_url( wp_login_url( home_url( '/' ) ) );
		$app_label = is_user_logged_in()
			? esc_html__( 'Open app', 'workshop-ledger' )
			: esc_html__( 'Sign in', 'workshop-ledger' );

		$primary = '<ul class="wp-block-navigation__container is-vertical wp-block-navigation">'
			. $this->li( $home, esc_html__( 'Home', 'workshop-ledger' ) )
			. $this->li( $about, esc_html__( 'About', 'workshop-ledger' ) )
			. $this->li( $app, $app_label )
			. '</ul>';

		$secondary = '<ul class="wp-block-navigation__container is-vertical wp-block-navigation">'
			. $this->li( $home, esc_html__( 'Vehicles', 'workshop-ledger' ) )
			. $this->li( $home, esc_html__( 'Inventory', 'workshop-ledger' ) )
			. $this->li( $home, esc_html__( 'Finance', 'workshop-ledger' ) )
			. $this->li( $home, esc_html__( 'Audatex InPart', 'workshop-ledger' ) )
			. '</ul>';

		// Swap the first two vertical placeholder nav lists in the footer.
		$count = 0;
		$out   = preg_replace_callback(
			'#<ul class="wp-block-navigation__container is-vertical[^"]*">.*?</ul>#s',
			static function ( array $m ) use ( &$count, $primary, $secondary ): string {
				++$count;
				if ( 1 === $count ) {
					return $primary;
				}
				if ( 2 === $count ) {
					return $secondary;
				}
				return $m[0];
			},
			$block_content,
			2
		);

		return is_string( $out ) ? $out : $block_content;
	}

	private function li( string $url, string $label ): string {
		return '<li class="wp-block-navigation-item wp-block-navigation-link">'
			. '<a class="wp-block-navigation-item__content" href="' . $url . '">'
			. '<span class="wp-block-navigation-item__label">' . $label . '</span></a></li>';
	}
}
