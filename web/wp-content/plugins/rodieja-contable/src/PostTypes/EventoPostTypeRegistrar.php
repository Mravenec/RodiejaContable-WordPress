<?php
/**
 * Historial audit events — native CPT (no custom tables).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\PostTypes;

use Rodieja\Contable\Security\CapabilityGate;

final class EventoPostTypeRegistrar {

	public function register(): void {
		add_action( 'init', array( $this, 'register_type' ) );
	}

	public function register_type(): void {
		$manage = CapabilityGate::MANAGE;

		register_post_type(
			DomainPostTypes::EVENTO,
			array(
				'labels'              => array(
					'name'          => __( 'Historial', 'rodieja-contable' ),
					'singular_name' => __( 'Evento', 'rodieja-contable' ),
					'menu_name'     => __( 'Historial', 'rodieja-contable' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'rodieja-contable',
				'show_in_rest'        => true,
				'rest_base'           => DomainPostTypes::EVENTO,
				'has_archive'         => false,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'custom-fields' ),
				'capability_type'     => DomainPostTypes::EVENTO,
				'map_meta_cap'        => true,
				'capabilities'        => array(
					'edit_post'          => $manage,
					'read_post'          => $manage,
					'delete_post'        => $manage,
					'edit_posts'         => $manage,
					'edit_others_posts'  => $manage,
					'publish_posts'      => $manage,
					'read_private_posts' => $manage,
					'create_posts'       => $manage,
					'delete_posts'       => $manage,
				),
				'delete_with_user'    => false,
			)
		);
	}
}
