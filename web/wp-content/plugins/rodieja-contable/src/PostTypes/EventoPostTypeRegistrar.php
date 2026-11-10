<?php
/**
 * Historial audit events — native CPT (no custom tables).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\PostTypes;

final class EventoPostTypeRegistrar {

	public function register(): void {
		add_action( 'init', array( $this, 'register_type' ) );
	}

	public function register_type(): void {
		// Same capability_type pattern as domain CPTs. Never assign CapabilityGate::MANAGE
		// to edit_post/delete_post — WP would put it in $post_type_meta_caps as delete_post
		// and every menu current_user_can( MANAGE ) would _doing_it_wrong (WP 6.1+).
		register_post_type(
			DomainPostTypes::EVENTO,
			array(
				'labels'           => array(
					'name'          => __( 'History', 'rodieja-contable' ),
					'singular_name' => __( 'Evento', 'rodieja-contable' ),
					'menu_name'     => __( 'History', 'rodieja-contable' ),
				),
				'public'           => false,
				'show_ui'          => true,
				'show_in_menu'     => false,
				'show_in_rest'     => true,
				'rest_base'        => DomainPostTypes::EVENTO,
				'has_archive'      => false,
				'hierarchical'     => false,
				'supports'         => array( 'title', 'custom-fields' ),
				'capability_type'  => DomainPostTypes::EVENTO,
				'map_meta_cap'     => true,
				'delete_with_user' => false,
			)
		);
	}
}
