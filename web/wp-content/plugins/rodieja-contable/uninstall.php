<?php
/**
 * Uninstall — removes plugin options only (CPT content kept unless user deletes posts).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	exit;
}

delete_option( 'rodieja_contable_version' );
delete_site_option( 'rodieja_contable_version' );
