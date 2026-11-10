<?php
/**
 * Native Login / Admin / Logout links in the block theme header navigation.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Front;

final class HeaderAuthLinks {

	private static bool $injected = false;

	public function register(): void {
		add_filter( 'render_block_core/navigation', array( $this, 'append_auth_links' ), 10, 2 );
	}

	/**
	 * @param array<string, mixed> $block
	 */
	public function append_auth_links( string $block_content, array $block ): string {
		unset( $block );
		if ( is_admin() || '' === $block_content || self::$injected ) {
			return $block_content;
		}

		// Header nav in Twenty Twenty-Five is responsive; skip plain footer link lists.
		if ( ! str_contains( $block_content, 'wp-block-navigation__responsive-container' )
			&& ! str_contains( $block_content, 'items-justified-right' ) ) {
			return $block_content;
		}

		$items = $this->auth_list_items();
		if ( '' === $items ) {
			return $block_content;
		}

		self::$injected = true;

		// Prefer injecting into the navigation list; fall back to before </nav>.
		if ( str_contains( $block_content, '</ul>' ) ) {
			return (string) preg_replace( '#</ul>#', $items . '</ul>', $block_content, 1 );
		}
		if ( str_contains( $block_content, '</nav>' ) ) {
			return (string) preg_replace( '#</nav>#', '<ul class="wp-block-navigation__container">' . $items . '</ul></nav>', $block_content, 1 );
		}

		return $block_content . $items;
	}

	private function auth_list_items(): string {
		if ( is_user_logged_in() ) {
			$admin_url = current_user_can( 'manage_rodieja_contable' ) || current_user_can( 'manage_options' )
				? admin_url( 'admin.php?page=rodieja-contable' )
				: admin_url();
			$parts     = array(
				$this->item(
					$admin_url,
					__( 'Rodieja', 'rodieja-contable' )
				),
				$this->item(
					wp_logout_url( home_url( '/' ) ),
					__( 'Cerrar sesión', 'rodieja-contable' )
				),
			);
			return implode( '', $parts );
		}

		return $this->item(
			wp_login_url( home_url( '/' ) ),
			__( 'Login', 'rodieja-contable' )
		);
	}

	private function item( string $url, string $label ): string {
		return sprintf(
			'<li class="wp-block-navigation-item rodieja-auth-link"><a class="wp-block-navigation-item__content" href="%s">%s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
}
