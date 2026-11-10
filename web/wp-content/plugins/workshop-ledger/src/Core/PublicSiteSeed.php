<?php
/**
 * One-time native public site seed — Home + About pages, static front (no Sample/Hello World).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Core;

final class PublicSiteSeed {

	public const OPTION_FLAG = 'wl_public_site_seeded_v1';

	public static function maybe_run(): void {
		if ( get_option( self::OPTION_FLAG ) ) {
			return;
		}
		( new self() )->run();
		update_option( self::OPTION_FLAG, gmdate( 'c' ), false );
	}

	private function run(): void {
		update_option( 'blogname', 'Workshop Ledger', false );
		update_option(
			'blogdescription',
			__( 'Vehicles · parts inventory · finance · Audatex InPart — WordPress native.', 'workshop-ledger' ),
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

		// Retire WordPress sample filler from navigation / front.
		$this->retire_post_by_path( 'sample-page', 'page' );
		$this->retire_post_by_path( 'hello-world', 'post' );

		if ( $home_id > 0 ) {
			update_option( 'show_on_front', 'page', false );
			update_option( 'page_on_front', $home_id, false );
			if ( $about_id > 0 ) {
				update_option( 'page_for_posts', 0, false );
			}
		}

		flush_rewrite_rules( false );
	}

	private function home_html(): string {
		$login = esc_url( wp_login_url( admin_url() ) );
		$admin = esc_url( admin_url( 'admin.php?page=workshop-ledger' ) );
		return '<p>' . esc_html__(
			'Workshop Ledger is the WordPress-native operations desk for workshop accounting: vehicle catalog, parts inventory, finance transactions, and Audatex InPart orders.',
			'workshop-ledger'
		) . '</p>'
		. '<p>' . esc_html__(
			'Day-to-day work happens in the admin app (CPT screens with Brand → Model → Generation cascades, hierarchy views, and reports). This public site is the branded entry point.',
			'workshop-ledger'
		) . '</p>'
		. '<ul>'
		. '<li><strong>' . esc_html__( 'Vehicles', 'workshop-ledger' ) . '</strong> — ' . esc_html__( 'brands, models, generations, vehicle status.', 'workshop-ledger' ) . '</li>'
		. '<li><strong>' . esc_html__( 'Inventory', 'workshop-ledger' ) . '</strong> — ' . esc_html__( 'parts with or without a linked vehicle.', 'workshop-ledger' ) . '</li>'
		. '<li><strong>' . esc_html__( 'Finance', 'workshop-ledger' ) . '</strong> — ' . esc_html__( 'income and expense transactions.', 'workshop-ledger' ) . '</li>'
		. '<li><strong>' . esc_html__( 'Audatex InPart', 'workshop-ledger' ) . '</strong> — ' . esc_html__( 'orders and INS import.', 'workshop-ledger' ) . '</li>'
		. '</ul>'
		. '<p><a class="wp-element-button" href="' . $login . '">' . esc_html__( 'Sign in', 'workshop-ledger' ) . '</a> '
		. '<a href="' . $admin . '">' . esc_html__( 'Open admin (after sign-in)', 'workshop-ledger' ) . '</a></p>';
	}

	private function about_html(): string {
		return '<p>' . esc_html__(
			'Workshop Ledger rewrites the Read-Only workshop product on WordPress core: custom post types, post meta, taxonomies, caps, and nonces — not a Spring schema clone.',
			'workshop-ledger'
		) . '</p>'
		. '<p>' . esc_html__(
			'Operators manage catalog, inventory, finance, and Audatex workflows inside wp-admin with the Ant-inspired Workshop Ledger chrome. Public pages stay thin and on-brand.',
			'workshop-ledger'
		) . '</p>'
		. '<p>' . esc_html__(
			'Need access? Ask an administrator for a Workshop Ledger account, then use Sign in from the site header.',
			'workshop-ledger'
		) . '</p>';
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

		// Reuse Sample Page if present (same slot in page-list nav).
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
		// About may have been renamed from sample-page already.
		if ( 'sample-page' === $path && 'about' === $post->post_name ) {
			return;
		}
		wp_trash_post( (int) $post->ID );
	}
}
