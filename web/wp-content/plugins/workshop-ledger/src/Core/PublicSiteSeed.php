<?php
/**
 * Native public site seed — Home + About (professional product copy).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Core;

final class PublicSiteSeed {

	public const OPTION_FLAG = 'wl_public_site_seeded_v2';

	public static function maybe_run(): void {
		if ( get_option( self::OPTION_FLAG ) ) {
			return;
		}
		( new self() )->run();
		update_option( self::OPTION_FLAG, gmdate( 'c' ), false );
		// Supersede v1 marker if present.
		delete_option( 'wl_public_site_seeded_v1' );
	}

	private function run(): void {
		update_option( 'blogname', 'Workshop Ledger', false );
		update_option(
			'blogdescription',
			__( 'Workshop accounting for vehicles, parts, finance, and Audatex InPart.', 'workshop-ledger' ),
			false
		);

		$home_id  = $this->upsert_page(
			'home',
			__( 'Home', 'workshop-ledger' ),
			$this->home_html()
		);
		$about_id = $this->upsert_page(
			'about',
			__( 'About', 'workshop-ledger' ),
			$this->about_html()
		);

		$this->retire_post_by_path( 'sample-page', 'page' );
		$this->retire_post_by_path( 'hello-world', 'post' );

		if ( $home_id > 0 ) {
			update_option( 'show_on_front', 'page', false );
			update_option( 'page_on_front', $home_id, false );
			update_option( 'page_for_posts', 0, false );
		}

		unset( $about_id );
	}

	private function home_html(): string {
		return '<div class="wl-front-panel">'
			. '<p class="wl-front-kicker">' . esc_html__( 'Workshop operations', 'workshop-ledger' ) . '</p>'
			. '<h2 class="wl-front-lead">' . esc_html__( 'One place to run your workshop books.', 'workshop-ledger' ) . '</h2>'
			. '<p class="wl-front-lede">' . esc_html__(
				'Workshop Ledger helps teams track vehicles, parts inventory, financial movements, and Audatex InPart orders—built as a focused WordPress product for workshop accounting.',
				'workshop-ledger'
			) . '</p>'
			. '<ul class="wl-front-modules">'
			. '<li><span class="wl-front-module__name">' . esc_html__( 'Vehicles', 'workshop-ledger' ) . '</span>'
			. '<span class="wl-front-module__desc">' . esc_html__( 'Catalog brands, models, generations, and vehicle status in one hierarchy.', 'workshop-ledger' ) . '</span></li>'
			. '<li><span class="wl-front-module__name">' . esc_html__( 'Inventory', 'workshop-ledger' ) . '</span>'
			. '<span class="wl-front-module__desc">' . esc_html__( 'Manage parts with clear pricing, condition, and optional vehicle links.', 'workshop-ledger' ) . '</span></li>'
			. '<li><span class="wl-front-module__name">' . esc_html__( 'Finance', 'workshop-ledger' ) . '</span>'
			. '<span class="wl-front-module__desc">' . esc_html__( 'Record income and expenses with the fields your workshop actually uses.', 'workshop-ledger' ) . '</span></li>'
			. '<li><span class="wl-front-module__name">' . esc_html__( 'Audatex InPart', 'workshop-ledger' ) . '</span>'
			. '<span class="wl-front-module__desc">' . esc_html__( 'Handle orders and INS imports without leaving the same workspace.', 'workshop-ledger' ) . '</span></li>'
			. '</ul>'
			. '<p class="wl-front-note">' . esc_html__(
				'Use Login in the header to open the application.',
				'workshop-ledger'
			) . '</p>'
			. '</div>';
	}

	private function about_html(): string {
		return '<div class="wl-front-panel">'
			. '<p class="wl-front-kicker">' . esc_html__( 'About the product', 'workshop-ledger' ) . '</p>'
			. '<h2 class="wl-front-lead">' . esc_html__( 'Designed for workshop teams on WordPress.', 'workshop-ledger' ) . '</h2>'
			. '<p class="wl-front-lede">' . esc_html__(
				'Workshop Ledger is a professional WordPress application for workshop accounting. It keeps vehicle catalog, inventory, finance, and Audatex InPart workflows in a single admin experience your staff can learn quickly.',
				'workshop-ledger'
			) . '</p>'
			. '<p class="wl-front-lede">' . esc_html__(
				'Everything is structured for day-to-day operations: clear modules, consistent screens, and a calm visual language so the work stays readable under pressure.',
				'workshop-ledger'
			) . '</p>'
			. '<p class="wl-front-note">' . esc_html__(
				'Need an account? Ask your administrator, then sign in from the site header.',
				'workshop-ledger'
			) . '</p>'
			. '</div>';
	}

	private function upsert_page( string $slug, string $title, string $content ): int {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing instanceof \WP_Post ) {
			$id = wp_update_post(
				array(
					'ID'           => (int) $existing->ID,
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_content' => $content,
					'post_status'  => 'publish',
					'post_type'    => 'page',
				),
				true
			);
			return is_wp_error( $id ) ? (int) $existing->ID : (int) $id;
		}

		if ( 'about' === $slug ) {
			$sample = get_page_by_path( 'sample-page', OBJECT, 'page' );
			if ( $sample instanceof \WP_Post ) {
				$id = wp_update_post(
					array(
						'ID'           => (int) $sample->ID,
						'post_title'   => $title,
						'post_name'    => $slug,
						'post_content' => $content,
						'post_status'  => 'publish',
					),
					true
				);
				return is_wp_error( $id ) ? (int) $sample->ID : (int) $id;
			}
		}

		$id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
			),
			true
		);
		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	private function retire_post_by_path( string $path, string $type ): void {
		$post = get_page_by_path( $path, OBJECT, $type );
		if ( ! ( $post instanceof \WP_Post ) ) {
			return;
		}
		if ( 'sample-page' === $path && 'about' === $post->post_name ) {
			return;
		}
		wp_trash_post( (int) $post->ID );
	}
}
