<?php
/**
 * Capability checks — never trust "we're in admin".
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Security;

final class CapabilityGate {

	public const MANAGE = 'manage_harness_plugin';

	public function can_manage(): bool {
		return current_user_can( self::MANAGE ) || current_user_can( 'manage_options' );
	}

	public function assert_manage(): bool {
		return $this->can_manage();
	}

	/**
	 * REST permission callback: authenticated + capability.
	 */
	public function rest_can_manage(): bool|\WP_Error {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error(
				'harness_rest_unauthorized',
				__( 'Authentication required.', 'harness-plugin' ),
				array( 'status' => 401 )
			);
		}

		if ( ! $this->can_manage() ) {
			return new \WP_Error(
				'harness_rest_forbidden',
				__( 'Sorry, you are not allowed to do that.', 'harness-plugin' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}
}
