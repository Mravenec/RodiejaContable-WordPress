<?php
/**
 * Native get_posts catalog hierarchy reader.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Repositories;

use Rodieja\Contable\Contracts\CatalogHierarchyRepositoryInterface;

final class CatalogHierarchyRepository implements CatalogHierarchyRepositoryInterface {

	public function tree(): array {
		$marcas        = $this->posts( 'rodieja_marca' );
		$modelos       = $this->posts( 'rodieja_modelo' );
		$generaciones  = $this->posts( 'rodieja_generacion' );
		$vehiculos     = $this->posts( 'rodieja_vehiculo' );

		$modelos_by_marca = array();
		foreach ( $modelos as $m ) {
			$mid = (int) get_post_meta( $m->ID, 'rodieja_marca_id', true );
			$modelos_by_marca[ $mid ][] = $m;
		}

		$gens_by_modelo = array();
		foreach ( $generaciones as $g ) {
			$moid = (int) get_post_meta( $g->ID, 'rodieja_modelo_id', true );
			$gens_by_modelo[ $moid ][] = $g;
		}

		$vehs_by_gen = array();
		foreach ( $vehiculos as $v ) {
			$gid = (int) get_post_meta( $v->ID, 'rodieja_generacion_id', true );
			$vehs_by_gen[ $gid ][] = $v;
		}

		$out = array();
		foreach ( $marcas as $marca ) {
			$mods = array();
			foreach ( $modelos_by_marca[ (int) $marca->ID ] ?? array() as $modelo ) {
				$gens = array();
				foreach ( $gens_by_modelo[ (int) $modelo->ID ] ?? array() as $gen ) {
					$vehs = array();
					foreach ( $vehs_by_gen[ (int) $gen->ID ] ?? array() as $veh ) {
						$vehs[] = array(
							'id'       => (int) $veh->ID,
							'title'    => get_the_title( $veh ),
							'codigo'   => (string) get_post_meta( $veh->ID, 'rodieja_codigo', true ),
							'anio'     => get_post_meta( $veh->ID, 'rodieja_anio', true ),
							'edit_url' => (string) get_edit_post_link( $veh->ID, 'raw' ),
						);
					}
					$gens[] = array(
						'id'          => (int) $gen->ID,
						'title'       => get_the_title( $gen ),
						'anio_inicio' => get_post_meta( $gen->ID, 'rodieja_anio_inicio', true ),
						'anio_fin'    => get_post_meta( $gen->ID, 'rodieja_anio_fin', true ),
						'vehiculos'   => $vehs,
					);
				}
				$mods[] = array(
					'id'           => (int) $modelo->ID,
					'title'        => get_the_title( $modelo ),
					'generaciones' => $gens,
				);
			}
			$out[] = array(
				'id'      => (int) $marca->ID,
				'title'   => get_the_title( $marca ),
				'modelos' => $mods,
			);
		}

		return $out;
	}

	/**
	 * @return list<\WP_Post>
	 */
	private function posts( string $type ): array {
		global $wpdb;
		// Prefer get_posts; $wpdb kept for layer contract / future prepared joins.
		unset( $wpdb );

		$posts = get_posts(
			array(
				'post_type'      => $type,
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 500,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		return is_array( $posts ) ? $posts : array();
	}
}
