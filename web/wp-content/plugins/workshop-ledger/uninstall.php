<?php
/**
 * Uninstall Workshop Ledger — remove options only (posts kept unless site deletes).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
if ( ! current_user_can( 'activate_plugins' ) ) {
	exit;
}

foreach ( array( 'wl_version', 'wl_org_name', 'wl_brand_migrated_v1', 'wl_audatex_ins_settings', 'wl_cr_cantones' ) as $key ) {
	delete_option( $key );
	delete_site_option( $key );
}
