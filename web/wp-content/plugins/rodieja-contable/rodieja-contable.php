<?php
/**
 * Plugin Name:       Rodieja Contable
 * Plugin URI:        https://rodieja-wp.ddev.site
 * Description:       Rodieja Contable — native WordPress (CPT/taxonomies/meta/roles/REST admin).
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            Rodieja
 * License:           GPL-2.0-or-later
 * Text Domain:       rodieja-contable
 * Domain Path:       /languages
 * Update URI:        false
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RODIEJA_PLUGIN_VERSION', '0.1.0' );
define( 'RODIEJA_PLUGIN_FILE', __FILE__ );
define( 'RODIEJA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RODIEJA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RODIEJA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

$rodieja_autoload = RODIEJA_PLUGIN_DIR . 'vendor/autoload.php';
if ( ! is_readable( $rodieja_autoload ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Rodieja Contable: run composer install in web/wp-content/plugins/rodieja-contable.', 'rodieja-contable' );
			echo '</p></div>';
		}
	);
	return;
}

require_once $rodieja_autoload;

register_activation_hook( __FILE__, array( \Rodieja\Contable\Core\Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \Rodieja\Contable\Core\Deactivator::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		\Rodieja\Contable\Core\Plugin::instance()->boot();
	},
	5
);
