<?php
/**
 * Capability checks — never trust "we're in admin".
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Security;

final class CapabilityGate {

	public const MANAGE = 'manage_workshop_ledger';

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
				'wl_rest_unauthorized',
				__( 'Authentication required.', 'workshop-ledger' ),
				array( 'status' => 401 )
			);
		}

		if ( ! $this->can_manage() ) {
			return new \WP_Error(
				'wl_rest_forbidden',
				__( 'Sorry, you are not allowed to do that.', 'workshop-ledger' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}
}
