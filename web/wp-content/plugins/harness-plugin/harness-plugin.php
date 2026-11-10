<?php
/**
 * Plugin Name:       Harness Plugin
 * Plugin URI:        https://harness-wp.ddev.site
 * Description:       Neutral WordPress harness plugin — native OOP, DI, SOLID, security-first.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            Harness
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       harness-plugin
 * Domain Path:       /languages
 * Update URI:        false
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HARNESS_PLUGIN_VERSION', '0.1.0' );
define( 'HARNESS_PLUGIN_FILE', __FILE__ );
define( 'HARNESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HARNESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HARNESS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

$harness_autoload = HARNESS_PLUGIN_DIR . 'vendor/autoload.php';
if ( ! is_readable( $harness_autoload ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Harness Plugin: run composer install inside 1.plugin/harness-plugin.', 'harness-plugin' );
			echo '</p></div>';
		}
	);
	return;
}

require_once $harness_autoload;

register_activation_hook( __FILE__, array( \Harness\Plugin\Core\Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \Harness\Plugin\Core\Deactivator::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		\Harness\Plugin\Core\Plugin::instance()->boot();
	},
	5
);
