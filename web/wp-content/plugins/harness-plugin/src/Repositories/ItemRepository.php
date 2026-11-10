<?php
/**
 * $wpdb repository with prepared statements only.
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Repositories;

use Harness\Plugin\Contracts\ItemRepositoryInterface;

final class ItemRepository implements ItemRepositoryInterface {

	public const SCHEMA_VERSION = 1;
	public const TABLE_SUFFIX   = 'harness_items';

	private \wpdb $db;

	public function __construct( ?\wpdb $db = null ) {
		global $wpdb;
		$this->db = $db ?? $wpdb;
	}

	public function table_name(): string {
		return $this->db->prefix . self::TABLE_SUFFIX;
	}

	public static function install_schema(): void {
		global $wpdb;

		$table           = $wpdb->prefix . self::TABLE_SUFFIX;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(191) NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY title (title)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * @return array{id:int,title:string,created_at:string}|null
	 */
	public function find_by_id( int $id ): ?array {
		if ( $id <= 0 ) {
			return null;
		}

		$table = $this->table_name();
		$sql   = $this->db->prepare(
			"SELECT id, title, created_at FROM {$table} WHERE id = %d LIMIT 1",
			$id
		);

		$row = $this->db->get_row( $sql, ARRAY_A );
		return is_array( $row ) ? $this->normalize( $row ) : null;
	}

	/**
	 * @return list<array{id:int,title:string,created_at:string}>
	 */
	public function find_all( int $limit = 20, int $offset = 0 ): array {
		$limit  = max( 1, min( 100, $limit ) );
		$offset = max( 0, $offset );
		$table  = $this->table_name();

		$sql = $this->db->prepare(
			"SELECT id, title, created_at FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d",
			$limit,
			$offset
		);

		$rows = $this->db->get_results( $sql, ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			if ( is_array( $row ) ) {
				$out[] = $this->normalize( $row );
			}
		}
		return $out;
	}

	/**
	 * @return array{id:int,title:string,created_at:string}|null
	 */
	public function create( string $title ): ?array {
		$title = sanitize_text_field( $title );
		if ( '' === $title ) {
			return null;
		}

		$inserted = $this->db->insert(
			$this->table_name(),
			array(
				'title'      => $title,
				'created_at' => current_time( 'mysql', true ),
			),
			array( '%s', '%s' )
		);

		if ( false === $inserted ) {
			return null;
		}

		return $this->find_by_id( (int) $this->db->insert_id );
	}

	public function delete( int $id ): bool {
		if ( $id <= 0 ) {
			return false;
		}

		$deleted = $this->db->delete(
			$this->table_name(),
			array( 'id' => $id ),
			array( '%d' )
		);

		return false !== $deleted && $deleted > 0;
	}

	/**
	 * @param array<string, mixed> $row
	 * @return array{id:int,title:string,created_at:string}
	 */
	private function normalize( array $row ): array {
		return array(
			'id'         => absint( $row['id'] ?? 0 ),
			'title'      => sanitize_text_field( (string) ( $row['title'] ?? '' ) ),
			'created_at' => sanitize_text_field( (string) ( $row['created_at'] ?? '' ) ),
		);
	}
}
