<?php
/**
 * Export domain CPTs to JSON/CSV (native posts + meta).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

use WorkshopLedger\PostTypes\DomainPostTypes;

final class DomainExportService {

	/** @return list<string> */
	public function types(): array {
		return DomainPostTypes::historial_tracked();
	}

	/**
	 * @return array<string, list<array<string, mixed>>>
	 */
	public function to_json_structure(): array {
		$out = array();
		foreach ( $this->types() as $type ) {
			$out[ $type ] = $this->posts_for_type( $type );
		}
		return $out;
	}

	public function to_json_string(): string {
		$encoded = wp_json_encode( $this->to_json_structure(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		return is_string( $encoded ) ? $encoded : '{}';
	}

	public function to_csv_string(): string {
		$lines   = array( 'post_type,id,title,status,meta_json' );
		foreach ( $this->types() as $type ) {
			foreach ( $this->posts_for_type( $type ) as $row ) {
				$meta = wp_json_encode( $row['meta'] ?? array() ) ?: '{}';
				$lines[] = sprintf(
					'%s,%d,"%s",%s,"%s"',
					$type,
					(int) $row['id'],
					str_replace( '"', '""', (string) $row['title'] ),
					(string) $row['status'],
					str_replace( '"', '""', $meta )
				);
			}
		}
		return implode( "\n", $lines );
	}

	/**
	 * @return list<array{id: int, title: string, status: string, meta: array<string, mixed>}>
	 */
	private function posts_for_type( string $type ): array {
		$ids = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);
		$rows = array();
		foreach ( $ids as $id ) {
			$post = get_post( (int) $id );
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}
			$meta = get_post_meta( (int) $id );
			$flat = array();
			foreach ( $meta as $key => $vals ) {
				if ( ! is_string( $key ) || ! str_starts_with( $key, 'wl_' ) ) {
					continue;
				}
				$flat[ $key ] = is_array( $vals ) ? ( $vals[0] ?? '' ) : $vals;
			}
			$rows[] = array(
				'id'     => (int) $id,
				'title'  => $post->post_title,
				'status' => $post->post_status,
				'meta'   => $flat,
			);
		}
		return $rows;
	}
}
