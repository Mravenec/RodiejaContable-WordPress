<?php
/**
 * Escape late — context-aware output helpers.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Security;

final class OutputEscaper {

	public function html( string $value ): string {
		return esc_html( $value );
	}

	public function attr( string $value ): string {
		return esc_attr( $value );
	}

	public function url( string $value ): string {
		return esc_url( $value );
	}

	public function js( string $value ): string {
		return esc_js( $value );
	}

	public function textarea( string $value ): string {
		return esc_textarea( $value );
	}
}
