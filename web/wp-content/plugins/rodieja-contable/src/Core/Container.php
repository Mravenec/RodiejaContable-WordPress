<?php
/**
 * Minimal constructor DI container.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Core;

use RuntimeException;

final class Container {

	/** @var array<string, callable(self): mixed> */
	private array $factories = array();

	/** @var array<string, mixed> */
	private array $instances = array();

	public function set( string $id, callable $factory ): void {
		$this->factories[ $id ] = $factory;
		unset( $this->instances[ $id ] );
	}

	/**
	 * @template T
	 * @param class-string<T> $id
	 * @return T
	 */
	public function get( string $id ): mixed {
		if ( array_key_exists( $id, $this->instances ) ) {
			return $this->instances[ $id ];
		}
		if ( ! isset( $this->factories[ $id ] ) ) {
			throw new RuntimeException( sprintf( 'Service not registered: %s', $id ) );
		}
		$instance               = ( $this->factories[ $id ] )( $this );
		$this->instances[ $id ] = $instance;
		return $instance;
	}
}
