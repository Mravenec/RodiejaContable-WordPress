<?php
/**
 * Business rules for harness items (domain-neutral demo).
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Services;

use Harness\Plugin\Contracts\ItemRepositoryInterface;
use Harness\Plugin\Security\RequestSanitizer;
use WP_Error;

final class ItemService {

	public function __construct(
		private ItemRepositoryInterface $repository,
		private RequestSanitizer $sanitizer
	) {}

	/**
	 * @return list<array{id:int,title:string,created_at:string}>
	 */
	public function list_items( int $limit = 20, int $offset = 0 ): array {
		return $this->repository->find_all( $limit, $offset );
	}

	/**
	 * @return array{id:int,title:string,created_at:string}|WP_Error
	 */
	public function get_item( int $id ): array|WP_Error {
		$id = $this->sanitizer->absint_value( $id );
		$item = $this->repository->find_by_id( $id );
		if ( null === $item ) {
			return new WP_Error(
				'harness_item_not_found',
				__( 'Item not found.', 'harness-plugin' ),
				array( 'status' => 404 )
			);
		}
		return $item;
	}

	/**
	 * @return array{id:int,title:string,created_at:string}|WP_Error
	 */
	public function create_item( mixed $raw_title ): array|WP_Error {
		$title = $this->sanitizer->text( $raw_title );
		if ( '' === $title ) {
			return new WP_Error(
				'harness_item_invalid',
				__( 'Title is required.', 'harness-plugin' ),
				array( 'status' => 400 )
			);
		}

		if ( strlen( $title ) > 191 ) {
			return new WP_Error(
				'harness_item_invalid',
				__( 'Title is too long.', 'harness-plugin' ),
				array( 'status' => 400 )
			);
		}

		$item = $this->repository->create( $title );
		if ( null === $item ) {
			return new WP_Error(
				'harness_item_create_failed',
				__( 'Could not create item.', 'harness-plugin' ),
				array( 'status' => 500 )
			);
		}

		return $item;
	}

	/**
	 * @return true|WP_Error
	 */
	public function delete_item( int $id ): bool|WP_Error {
		$id = $this->sanitizer->absint_value( $id );
		if ( ! $this->repository->delete( $id ) ) {
			return new WP_Error(
				'harness_item_delete_failed',
				__( 'Could not delete item.', 'harness-plugin' ),
				array( 'status' => 404 )
			);
		}
		return true;
	}
}
