<?php
/**
 * Native public SEO head: title parts, description, Open Graph, Twitter, JSON-LD.
 *
 * Canonical links stay on core rel_canonical (one link rel=canonical on singular views,
 * including a static front page). Robots stay on core wp_robots. The core sitemap
 * stays at /wp-sitemap.xml. This class does not print a second canonical tag.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Front;

final class PublicSeo {

	public function register(): void {
		add_filter( 'document_title_parts', array( $this, 'document_title_parts' ) );
		add_action( 'wp_head', array( $this, 'render_head' ), 1 );
	}

	/**
	 * Front page only. Other views keep WordPress defaults.
	 *
	 * @param array<string, string> $parts
	 * @return array<string, string>
	 */
	public function document_title_parts( array $parts ): array {
		if ( ! is_front_page() ) {
			return $parts;
		}

		$parts['title']   = $this->site_name();
		$parts['tagline'] = __( 'Workshop accounting for vehicles, parts, and orders', 'workshop-ledger' );

		return $parts;
	}

	public function render_head(): void {
		if ( is_admin() || is_feed() ) {
			return;
		}

		$description = $this->description();
		$title       = $this->social_title();
		$site_name   = $this->site_name();
		$locale      = str_replace( '-', '_', (string) get_locale() );

		$this->print_meta( 'name', 'description', $description );
		$this->print_meta( 'property', 'og:title', $title );
		$this->print_meta( 'property', 'og:description', $description );

		$url = $this->current_url();
		if ( '' !== $url ) {
			$this->print_meta( 'property', 'og:url', $url, true );
		}

		$this->print_meta( 'property', 'og:site_name', $site_name );
		$this->print_meta( 'property', 'og:type', 'website' );
		$this->print_meta( 'property', 'og:locale', $locale );
		$this->print_meta( 'name', 'twitter:card', 'summary' );
		$this->print_meta( 'name', 'twitter:title', $title );
		$this->print_meta( 'name', 'twitter:description', $description );
		$this->print_json_ld( $site_name );
	}

	private function description(): string {
		$text = '';

		if ( is_front_page() ) {
			$tagline = trim( (string) get_bloginfo( 'description' ) );
			$text    = '' !== $tagline ? $tagline : $this->product_description();
		} elseif ( is_singular() ) {
			$post = get_post();
			if ( $post instanceof \WP_Post && has_excerpt( $post ) ) {
				$text = wp_trim_words( wp_strip_all_tags( (string) $post->post_excerpt ), 28, '' );
			}
		}

		if ( '' === trim( wp_strip_all_tags( $text ) ) ) {
			$text = $this->product_description();
		}

		return trim( wp_strip_all_tags( $text ) );
	}

	/**
	 * Document title as plain text so esc_attr runs once in the attribute.
	 */
	private function social_title(): string {
		$decoded = html_entity_decode( (string) wp_get_document_title(), ENT_QUOTES, 'UTF-8' );
		$title   = trim( wp_strip_all_tags( $decoded ) );
		if ( '' === $title ) {
			return $this->site_name();
		}
		return $title;
	}

	/**
	 * Front page uses the home URL. Singular views use the permalink.
	 * Other views omit og:url. Core rel_canonical still prints the canonical link.
	 */
	private function current_url(): string {
		if ( is_front_page() ) {
			return (string) home_url( '/' );
		}
		if ( is_singular() ) {
			$permalink = get_permalink();
			return is_string( $permalink ) ? $permalink : '';
		}
		return '';
	}

	private function site_name(): string {
		return trim( wp_strip_all_tags( (string) get_bloginfo( 'name' ) ) );
	}

	private function product_description(): string {
		return __( 'Workshop Ledger keeps vehicles, parts, finance, and Audatex InPart orders in one WordPress application.', 'workshop-ledger' );
	}

	private function print_meta( string $attribute, string $name, string $content, bool $url = false ): void {
		$value = $url ? esc_url( $content ) : esc_attr( $content );
		echo '<meta ' . esc_attr( $attribute ) . '="' . esc_attr( $name ) . '" content="' . $value . '" />' . "\n";
	}

	private function print_json_ld( string $site_name ): void {
		$encoded = wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => array(
					array(
						'@type' => 'Organization',
						'name'  => $site_name,
						'url'   => home_url( '/' ),
					),
					array(
						'@type' => 'WebSite',
						'name'  => $site_name,
						'url'   => home_url( '/' ),
					),
				),
			)
		);
		if ( ! is_string( $encoded ) || '' === $encoded ) {
			return;
		}

		// wp_json_encode hex-escapes <, >, &, and quotes. Do not append post HTML.
		echo '<script type="' . esc_attr( 'application/ld+json' ) . '">' . $encoded . '</script>' . "\n";
	}
}
