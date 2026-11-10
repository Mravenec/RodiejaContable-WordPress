<?php
/**
 * Plugin Name:       Workshop Ledger
 * Plugin URI:        https://workshop-ledger.ddev.site
 * Description:       Workshop Ledger — native WordPress (CPT/taxonomies/meta/roles/REST admin).
 * Version:           0.3.1
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            Workshop Ledger
 * License:           GPL-2.0-or-later
 * Text Domain:       workshop-ledger
 * Domain Path:       /languages
 * Update URI:        false
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WL_PLUGIN_VERSION', '0.4.0' );
define( 'WL_PLUGIN_FILE', __FILE__ );
define( 'WL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

$wl_autoload = WL_PLUGIN_DIR . 'vendor/autoload.php';
if ( ! is_readable( $wl_autoload ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Workshop Ledger: run composer install in web/wp-content/plugins/workshop-ledger.', 'workshop-ledger' );
			echo '</p></div>';
		}
	);
	return;
}

require_once $wl_autoload;

register_activation_hook( __FILE__, array( \WorkshopLedger\Core\Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \WorkshopLedger\Core\Deactivator::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		\WorkshopLedger\Core\Plugin::instance()->boot();
	},
	5
);
