<?php
/**
 * Minimal constructor DI container (no framework).
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

	/**
	 * Register a factory for an abstract type.
	 *
	 * @param string                     $id      Class or interface FQCN.
	 * @param callable(self): mixed      $factory Lazy factory.
	 */
	public function set( string $id, callable $factory ): void {
		$this->factories[ $id ] = $factory;
		unset( $this->instances[ $id ] );
	}

	/**
	 * Resolve a service.
	 *
	 * @template T
	 * @param class-string<T> $id Service id.
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

	public function has( string $id ): bool {
		return isset( $this->factories[ $id ] ) || array_key_exists( $id, $this->instances );
	}
}
