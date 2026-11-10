<?php
/**
 * Configuración · cantones Costa Rica (Options API).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\CostaRicaCantonesService;

final class CantonesSettingsPage {

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private NonceGuard $nonce,
		private CostaRicaCantonesService $cantones
	) {}

	public function register(): void {
		// Rendered via AdminMenu · Configuración.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			$this->nonce->verify_admin( 'rodieja_cantones_settings' );
			$rows = array();
			$prov = isset( $_POST['provincia'] ) && is_array( $_POST['provincia'] ) ? wp_unslash( $_POST['provincia'] ) : array();
			$can  = isset( $_POST['canton'] ) && is_array( $_POST['canton'] ) ? wp_unslash( $_POST['canton'] ) : array();
			$max  = max( count( $prov ), count( $can ) );
			for ( $i = 0; $i < $max; ++$i ) {
				$rows[] = array(
					'provincia' => (string) ( $prov[ $i ] ?? '' ),
					'canton'    => (string) ( $can[ $i ] ?? '' ),
				);
			}
			$this->cantones->save( $rows );
			echo '<div class="notice notice-success"><p>' . $this->escaper->html( __( 'Cantons saved.', 'rodieja-contable' ) ) . '</p></div>';
		}
		$list = $this->cantones->all();
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Cantones Costa Rica', 'rodieja-contable' ) ); ?></h1>
			<form method="post">
				<?php $this->nonce->field( 'rodieja_cantones_settings' ); ?>
				<table class="widefat striped" style="max-width:640px">
					<thead><tr><th><?php echo $this->escaper->html( __( 'Province', 'rodieja-contable' ) ); ?></th><th><?php echo $this->escaper->html( __( 'Canton', 'rodieja-contable' ) ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $list as $row ) : ?>
						<tr>
							<td><input type="text" class="regular-text" name="provincia[]" value="<?php echo $this->escaper->attr( $row['provincia'] ); ?>" /></td>
							<td><input type="text" class="regular-text" name="canton[]" value="<?php echo $this->escaper->attr( $row['canton'] ); ?>" /></td>
						</tr>
					<?php endforeach; ?>
						<tr>
							<td><input type="text" class="regular-text" name="provincia[]" value="" placeholder="<?php echo $this->escaper->attr( __( 'Nueva province', 'rodieja-contable' ) ); ?>" /></td>
							<td><input type="text" class="regular-text" name="canton[]" value="" placeholder="<?php echo $this->escaper->attr( __( 'Nuevo canton', 'rodieja-contable' ) ); ?>" /></td>
						</tr>
					</tbody>
				</table>
				<?php submit_button( __( 'Save cantones', 'rodieja-contable' ) ); ?>
			</form>
		</div>
		<?php
	}
}
