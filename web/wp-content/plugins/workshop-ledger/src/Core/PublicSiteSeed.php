<?php
/**
 * Native public site seed — Home + About (professional product copy).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Core;

final class PublicSiteSeed {

	public const OPTION_FLAG = 'wl_public_site_seeded_v3';

	public static function maybe_run(): void {
		if ( get_option( self::OPTION_FLAG ) ) {
			return;
		}
		( new self() )->run();
		update_option( self::OPTION_FLAG, gmdate( 'c' ), false );
		delete_option( 'wl_public_site_seeded_v1' );
		delete_option( 'wl_public_site_seeded_v2' );
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
		return '<article class="wl-landing">'
			. '<header class="wl-hero">'
			. '<p class="wl-front-kicker">' . esc_html__( 'Workshop Ledger', 'workshop-ledger' ) . '</p>'
			. '<h2 class="wl-front-lead">' . esc_html__( 'The books of a workshop, explained before you sign in.', 'workshop-ledger' ) . '</h2>'
			. '<p class="wl-front-lede">' . esc_html__(
				'Workshop Ledger is the operations desk for a vehicle workshop: what is in the yard, what is on the shelf, what money moved, and which Audatex InPart orders are open. This page is the introduction. The daily work waits behind Login.',
				'workshop-ledger'
			) . '</p>'
			. '</header>'
			. '<section class="wl-story">'
			. '<h3>' . esc_html__( 'Why this site exists', 'workshop-ledger' ) . '</h3>'
			. '<p>' . esc_html__(
				'Staff should not meet a blank form first. The public site says what the product is for. Operators then use the header to open the application and work in the four modules below.',
				'workshop-ledger'
			) . '</p>'
			. '</section>'
			. '<ol class="wl-front-modules">'
			. '<li><span class="wl-front-module__index">01</span><span class="wl-front-module__name">' . esc_html__( 'Vehicles', 'workshop-ledger' ) . '</span>'
			. '<span class="wl-front-module__desc">' . esc_html__( 'Catalog brands, models, generations, and the status of each vehicle.', 'workshop-ledger' ) . '</span></li>'
			. '<li><span class="wl-front-module__index">02</span><span class="wl-front-module__name">' . esc_html__( 'Inventory', 'workshop-ledger' ) . '</span>'
			. '<span class="wl-front-module__desc">' . esc_html__( 'Parts with price, condition, and an optional link to a vehicle.', 'workshop-ledger' ) . '</span></li>'
			. '<li><span class="wl-front-module__index">03</span><span class="wl-front-module__name">' . esc_html__( 'Finance', 'workshop-ledger' ) . '</span>'
			. '<span class="wl-front-module__desc">' . esc_html__( 'Income and expenses recorded with the fields the workshop actually uses.', 'workshop-ledger' ) . '</span></li>'
			. '<li><span class="wl-front-module__index">04</span><span class="wl-front-module__name">' . esc_html__( 'Audatex InPart', 'workshop-ledger' ) . '</span>'
			. '<span class="wl-front-module__desc">' . esc_html__( 'Orders and INS imports in the same workspace as the rest of the books.', 'workshop-ledger' ) . '</span></li>'
			. '</ol>'
			. '<p class="wl-front-note">' . esc_html__(
				'When you are ready, use Login in the header. There is no form on this page.',
				'workshop-ledger'
			) . '</p>'
			. '</article>';
	}

	private function about_html(): string {
		return '<article class="wl-landing">'
			. '<header class="wl-hero">'
			. '<p class="wl-front-kicker">' . esc_html__( 'About the product', 'workshop-ledger' ) . '</p>'
			. '<h2 class="wl-front-lead">' . esc_html__( 'A workshop desk your staff can learn in a day.', 'workshop-ledger' ) . '</h2>'
			. '<p class="wl-front-lede">' . esc_html__(
				'Workshop Ledger keeps the vehicle catalog, parts inventory, finance, and Audatex InPart orders in one WordPress application. The public pages introduce that desk. The work itself happens after Login.',
				'workshop-ledger'
			) . '</p>'
			. '</header>'
			. '<section class="wl-story">'
			. '<h3>' . esc_html__( 'How a visit goes', 'workshop-ledger' ) . '</h3>'
			. '<p>' . esc_html__(
				'Read what the four modules cover. Ask an administrator for an account. Sign in from the header and open the application. Screens stay consistent so the books stay readable when the shop is busy.',
				'workshop-ledger'
			) . '</p>'
			. '</section>'
			. '<p class="wl-front-note">' . esc_html__(
				'Need an account? Ask your administrator, then use Login in the header.',
				'workshop-ledger'
			) . '</p>'
			. '</article>';
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
