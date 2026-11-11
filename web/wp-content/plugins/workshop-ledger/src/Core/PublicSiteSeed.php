<?php
/**
 * Native public site seed — Home + About as WordPress pages.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Core;

final class PublicSiteSeed {

	public const OPTION_FLAG = 'wl_public_site_seeded_v4';

	public static function maybe_run(): void {
		if ( get_option( self::OPTION_FLAG ) ) {
			return;
		}
		( new self() )->run();
		update_option( self::OPTION_FLAG, gmdate( 'c' ), false );
		delete_option( 'wl_public_site_seeded_v1' );
		delete_option( 'wl_public_site_seeded_v2' );
		delete_option( 'wl_public_site_seeded_v3' );
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
			. '<h2 class="wl-front-lead">' . esc_html__( 'One desk for the whole shop.', 'workshop-ledger' ) . '</h2>'
			. '<p class="wl-front-lede">' . esc_html__(
				'Vehicles in the yard, parts off the shelf, money recorded, and Audatex InPart orders that stay open until the job is done.',
				'workshop-ledger'
			) . '</p>'
			. '</header>'
			. '<section class="wl-day">'
			. '<h2>' . esc_html__( 'A day on the floor', 'workshop-ledger' ) . '</h2>'
			. '<ol>'
			. '<li class="wl-day__step">'
			. '<p class="wl-day__when">' . esc_html__( 'Morning', 'workshop-ledger' ) . '</p>'
			. '<p class="wl-day__what">' . esc_html__( 'A vehicle arrives and is written into the yard with its brand, model, and the reason it came in.', 'workshop-ledger' ) . '</p>'
			. '</li>'
			. '<li class="wl-day__step">'
			. '<p class="wl-day__when">' . esc_html__( 'Late morning', 'workshop-ledger' ) . '</p>'
			. '<p class="wl-day__what">' . esc_html__( 'A part leaves the shelf and is tied to that vehicle, with its price and condition.', 'workshop-ledger' ) . '</p>'
			. '</li>'
			. '<li class="wl-day__step">'
			. '<p class="wl-day__when">' . esc_html__( 'Afternoon', 'workshop-ledger' ) . '</p>'
			. '<p class="wl-day__what">' . esc_html__( 'The money is recorded: income for the job, or the expense for the part.', 'workshop-ledger' ) . '</p>'
			. '</li>'
			. '<li class="wl-day__step">'
			. '<p class="wl-day__when">' . esc_html__( 'Before close', 'workshop-ledger' ) . '</p>'
			. '<p class="wl-day__what">' . esc_html__( 'The Audatex InPart order stays open in the same books until the parts and the paperwork match.', 'workshop-ledger' ) . '</p>'
			. '</li>'
			. '</ol>'
			. '</section>'
			. '<section class="wl-modules">'
			. '<h2>' . esc_html__( 'What opens after login', 'workshop-ledger' ) . '</h2>'
			. '<article class="wl-module">'
			. '<p class="wl-module__index">' . esc_html__( '01', 'workshop-ledger' ) . '</p>'
			. '<h3 class="wl-module__name">' . esc_html__( 'Vehicles', 'workshop-ledger' ) . '</h3>'
			. '<p class="wl-module__line">' . esc_html__( 'Open a vehicle and see its brand, model, generation, and whether it is still on the floor.', 'workshop-ledger' ) . '</p>'
			. '</article>'
			. '<article class="wl-module">'
			. '<p class="wl-module__index">' . esc_html__( '02', 'workshop-ledger' ) . '</p>'
			. '<h3 class="wl-module__name">' . esc_html__( 'Inventory', 'workshop-ledger' ) . '</h3>'
			. '<p class="wl-module__line">' . esc_html__( 'See which part left the shelf, what it costs, and which vehicle it belongs to.', 'workshop-ledger' ) . '</p>'
			. '</article>'
			. '<article class="wl-module">'
			. '<p class="wl-module__index">' . esc_html__( '03', 'workshop-ledger' ) . '</p>'
			. '<h3 class="wl-module__name">' . esc_html__( 'Finance', 'workshop-ledger' ) . '</h3>'
			. '<p class="wl-module__line">' . esc_html__( 'Record income and expenses with the fields the workshop actually uses.', 'workshop-ledger' ) . '</p>'
			. '</article>'
			. '<article class="wl-module">'
			. '<p class="wl-module__index">' . esc_html__( '04', 'workshop-ledger' ) . '</p>'
			. '<h3 class="wl-module__name">' . esc_html__( 'Audatex InPart', 'workshop-ledger' ) . '</h3>'
			. '<p class="wl-module__line">' . esc_html__( 'Keep the order and the INS import visible until the job is closed.', 'workshop-ledger' ) . '</p>'
			. '</article>'
			. '</section>'
			. '<p class="wl-front-note">' . esc_html__( 'Login stays in the header. There is no form on this page.', 'workshop-ledger' ) . '</p>'
			. '</article>';
	}

	private function about_html(): string {
		return '<article class="wl-landing">'
			. '<header class="wl-hero">'
			. '<p class="wl-front-kicker">' . esc_html__( 'Workshop Ledger', 'workshop-ledger' ) . '</p>'
			. '<h2 class="wl-front-lead">' . esc_html__( 'Accounts for the people who run the shop.', 'workshop-ledger' ) . '</h2>'
			. '<p class="wl-front-lede">' . esc_html__(
				'Workshop Ledger is the desk for vehicles, parts, finance, and Audatex InPart orders.',
				'workshop-ledger'
			) . '</p>'
			. '</header>'
			. '<section class="wl-audience">'
			. '<h2>' . esc_html__( 'Who it is for', 'workshop-ledger' ) . '</h2>'
			. '<p>' . esc_html__(
				'Workshop staff record the vehicle, the part, the money, and the Audatex InPart order. The administrator is the person who issues those accounts.',
				'workshop-ledger'
			) . '</p>'
			. '</section>'
			. '<p class="wl-front-note">' . esc_html__( 'Login stays in the header. There is no form on this page.', 'workshop-ledger' ) . '</p>'
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
