<?php
/**
 * Fired when the plugin is uninstalled from WordPress admin.
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	exit;
}

global $wpdb;

$option_keys = array(
	'harness_plugin_version',
	'harness_plugin_schema_version',
);

foreach ( $option_keys as $option_key ) {
	delete_option( $option_key );
	delete_site_option( $option_key );
}

$table = $wpdb->prefix . 'harness_items';
// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name uses trusted prefix only.
$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
