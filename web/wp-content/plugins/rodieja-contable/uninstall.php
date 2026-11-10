<?php
declare(strict_types=1);
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }
if ( ! current_user_can( 'activate_plugins' ) ) { exit; }
foreach ( array( 'rodieja_contable_version', 'rodieja_contable_schema_version' ) as $key ) {
	delete_option( $key );
	delete_site_option( $key );
}
