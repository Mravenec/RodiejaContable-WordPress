<?php
/**
 * Lista de cantones CR — Options API (no custom tables).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

final class CostaRicaCantonesService {

	public const OPTION_KEY = 'wl_cr_cantones';

	/**
	 * @return list<array{provincia: string, canton: string}>
	 */
	public function all(): array {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) || array() === $stored ) {
			$stored = self::defaults();
			update_option( self::OPTION_KEY, $stored, false );
		}
		/** @var list<array{provincia: string, canton: string}> $out */
		$out = array();
		foreach ( $stored as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$prov = sanitize_text_field( (string) ( $row['provincia'] ?? '' ) );
			$can  = sanitize_text_field( (string) ( $row['canton'] ?? '' ) );
			if ( '' === $prov || '' === $can ) {
				continue;
			}
			$out[] = array( 'provincia' => $prov, 'canton' => $can );
		}
		return $out;
	}

	/**
	 * @param list<array{provincia: string, canton: string}> $rows
	 */
	public function save( array $rows ): void {
		$clean = array();
		foreach ( $rows as $row ) {
			$prov = sanitize_text_field( (string) ( $row['provincia'] ?? '' ) );
			$can  = sanitize_text_field( (string) ( $row['canton'] ?? '' ) );
			if ( '' === $prov || '' === $can ) {
				continue;
			}
			$clean[] = array( 'provincia' => $prov, 'canton' => $can );
		}
		update_option( self::OPTION_KEY, $clean, false );
	}

	/** @return list<string> */
	public function provincias(): array {
		$set = array();
		foreach ( $this->all() as $row ) {
			$set[ $row['provincia'] ] = true;
		}
		$list = array_keys( $set );
		sort( $list );
		return $list;
	}

	/** @return list<string> */
	public function cantones_for( string $provincia ): array {
		$out = array();
		foreach ( $this->all() as $row ) {
			if ( $row['provincia'] === $provincia ) {
				$out[] = $row['canton'];
			}
		}
		sort( $out );
		return $out;
	}

	/**
	 * @return list<array{provincia: string, canton: string}>
	 */
	public static function defaults(): array {
		return array(
			array( 'provincia' => 'San José', 'canton' => 'Central' ),
			array( 'provincia' => 'San José', 'canton' => 'Escazú' ),
			array( 'provincia' => 'San José', 'canton' => 'Desamparados' ),
			array( 'provincia' => 'Alajuela', 'canton' => 'Central' ),
			array( 'provincia' => 'Alajuela', 'canton' => 'San Carlos' ),
			array( 'provincia' => 'Cartago', 'canton' => 'Central' ),
			array( 'provincia' => 'Heredia', 'canton' => 'Central' ),
			array( 'provincia' => 'Guanacaste', 'canton' => 'Liberia' ),
			array( 'provincia' => 'Puntarenas', 'canton' => 'Central' ),
			array( 'provincia' => 'Limón', 'canton' => 'Central' ),
		);
	}
}
