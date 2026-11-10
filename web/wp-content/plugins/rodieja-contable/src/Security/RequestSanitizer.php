<?php
/**
 * Sanitize early — typed helpers over raw superglobals.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Security;

final class RequestSanitizer {

	public function text( mixed $value ): string {
		return sanitize_text_field( wp_unslash( (string) $value ) );
	}

	public function textarea( mixed $value ): string {
		return sanitize_textarea_field( wp_unslash( (string) $value ) );
	}

	public function email( mixed $value ): string {
		return sanitize_email( wp_unslash( (string) $value ) );
	}

	public function url( mixed $value ): string {
		return esc_url_raw( wp_unslash( (string) $value ) );
	}

	public function absint_value( mixed $value ): int {
		return absint( $value );
	}

	/**
	 * @param array<string, mixed> $source
	 * @param list<string>         $keys
	 * @return array<string, string>
	 */
	public function text_map( array $source, array $keys ): array {
		$out = array();
		foreach ( $keys as $key ) {
			if ( isset( $source[ $key ] ) ) {
				$out[ $key ] = $this->text( $source[ $key ] );
			}
		}
		return $out;
	}
}
