<?php
/**
 * Native get_posts catalog hierarchy reader.
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Repositories;

use WorkshopLedger\Contracts\CatalogHierarchyRepositoryInterface;

final class CatalogHierarchyRepository implements CatalogHierarchyRepositoryInterface {

	public function tree(): array {
		$marcas       = $this->posts( 'wl_marca' );
		$modelos      = $this->posts( 'wl_modelo' );
		$generaciones = $this->posts( 'wl_generacion' );
		$vehiculos    = $this->posts( 'wl_vehiculo' );
		$parts_by_veh = $this->index_parts_by_vehicle();
		$txs_by_veh   = $this->index_transactions_by_vehicle();

		$modelos_by_marca = array();
		foreach ( $modelos as $m ) {
			$mid = (int) get_post_meta( $m->ID, 'wl_marca_id', true );
			$modelos_by_marca[ $mid ][] = $m;
		}

		$gens_by_modelo = array();
		foreach ( $generaciones as $g ) {
			$moid = (int) get_post_meta( $g->ID, 'wl_modelo_id', true );
			$gens_by_modelo[ $moid ][] = $g;
		}

		$vehs_by_gen = array();
		foreach ( $vehiculos as $v ) {
			$gid = (int) get_post_meta( $v->ID, 'wl_generacion_id', true );
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
						$vehs[] = $this->vehicle_row(
							$veh,
							$parts_by_veh[ (int) $veh->ID ] ?? array(),
							$txs_by_veh[ (int) $veh->ID ] ?? array()
						);
					}
					$gens[] = array(
						'id'          => (int) $gen->ID,
						'title'       => get_the_title( $gen ),
						'anio_inicio' => get_post_meta( $gen->ID, 'wl_anio_inicio', true ),
						'anio_fin'    => get_post_meta( $gen->ID, 'wl_anio_fin', true ),
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
	 * @param list<array{id:int,title:string,precio:float,stock:int,edit_url:string}> $parts
	 * @param list<array{id:int,title:string,monto:float,fecha:string,sentido:string,edit_url:string}> $txs
	 * @return array<string, mixed>
	 */
	private function vehicle_row( \WP_Post $veh, array $parts, array $txs ): array {
		$precio     = (float) get_post_meta( $veh->ID, 'wl_precio_compra', true );
		$grua       = (float) get_post_meta( $veh->ID, 'wl_costo_grua', true );
		$comisiones = (float) get_post_meta( $veh->ID, 'wl_comisiones', true );
		$recuperado = (float) get_post_meta( $veh->ID, 'wl_costo_recuperado', true );
		$inversion  = $precio + $grua + $comisiones;
		$thumb      = get_the_post_thumbnail_url( $veh, 'thumbnail' );

		return array(
			'id'               => (int) $veh->ID,
			'title'            => get_the_title( $veh ),
			'codigo'           => (string) get_post_meta( $veh->ID, 'wl_codigo', true ),
			'anio'             => get_post_meta( $veh->ID, 'wl_anio', true ),
			'edit_url'         => (string) get_edit_post_link( $veh->ID, 'raw' ),
			'thumb_url'        => is_string( $thumb ) ? $thumb : '',
			'estado'           => $this->vehicle_estado( (int) $veh->ID ),
			'fecha_ingreso'    => (string) get_post_meta( $veh->ID, 'wl_fecha_ingreso', true ),
			'precio_compra'    => $precio,
			'costo_grua'       => $grua,
			'comisiones'       => $comisiones,
			'costo_recuperado' => $recuperado,
			'inversion_total'  => $inversion,
			'costo_pendiente'  => $inversion - $recuperado,
			'repuestos'        => $parts,
			'transacciones'    => $txs,
		);
	}

	private function vehicle_estado( int $post_id ): string {
		$terms = get_the_terms( $post_id, 'wl_estado_vehiculo' );
		if ( ! is_array( $terms ) || empty( $terms ) ) {
			return '';
		}
		$first = $terms[0];
		return $first instanceof \WP_Term ? (string) $first->name : '';
	}

	/**
	 * @return array<int, list<array{id:int,title:string,precio:float,stock:int,edit_url:string}>>
	 */
	private function index_parts_by_vehicle(): array {
		$out = array();
		foreach ( $this->posts( 'wl_repuesto' ) as $p ) {
			$vid = (int) get_post_meta( $p->ID, 'wl_vehiculo_id', true );
			if ( $vid <= 0 ) {
				continue;
			}
			$out[ $vid ][] = array(
				'id'       => (int) $p->ID,
				'title'    => get_the_title( $p ),
				'precio'   => (float) get_post_meta( $p->ID, 'wl_precio', true ),
				'stock'    => (int) get_post_meta( $p->ID, 'wl_stock', true ),
				'edit_url' => (string) get_edit_post_link( $p->ID, 'raw' ),
			);
		}
		return $out;
	}

	/**
	 * @return array<int, list<array{id:int,title:string,monto:float,fecha:string,sentido:string,edit_url:string}>>
	 */
	private function index_transactions_by_vehicle(): array {
		$out = array();
		foreach ( $this->posts( 'wl_transaccion' ) as $t ) {
			$vid = (int) get_post_meta( $t->ID, 'wl_vehiculo_id', true );
			if ( $vid <= 0 ) {
				continue;
			}
			$out[ $vid ][] = array(
				'id'       => (int) $t->ID,
				'title'    => get_the_title( $t ),
				'monto'    => (float) get_post_meta( $t->ID, 'wl_monto', true ),
				'fecha'    => (string) get_post_meta( $t->ID, 'wl_fecha', true ),
				'sentido'  => (string) get_post_meta( $t->ID, 'wl_sentido', true ),
				'edit_url' => (string) get_edit_post_link( $t->ID, 'raw' ),
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
