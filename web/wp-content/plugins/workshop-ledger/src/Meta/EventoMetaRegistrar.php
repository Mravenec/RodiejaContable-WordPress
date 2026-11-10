<?php
/**
 * Meta for wl_evento historial rows.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Meta;

use WorkshopLedger\PostTypes\DomainPostTypes;
use WorkshopLedger\Security\CapabilityGate;

final class EventoMetaRegistrar {

	public function register(): void {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_meta(): void {
		$type = DomainPostTypes::EVENTO;
		$cb   = array( $this, 'can_manage' );

		register_post_meta(
			$type,
			'origen_cpt',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $cb,
				'sanitize_callback' => 'sanitize_key',
			)
		);
		register_post_meta(
			$type,
			'origen_id',
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $cb,
				'sanitize_callback' => 'absint',
			)
		);
		register_post_meta(
			$type,
			'accion',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $cb,
				'sanitize_callback' => 'sanitize_key',
			)
		);
		register_post_meta(
			$type,
			'user_id',
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => $cb,
				'sanitize_callback' => 'absint',
			)
		);
	}

	public function can_manage(): bool {
		return current_user_can( CapabilityGate::MANAGE ) || current_user_can( 'manage_options' );
	}
}
