<?php
/**
 * Settings · Costa Rica cantons (Options API).
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
		// Rendered via AdminMenu · Settings.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$saved = false;
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
			$saved = true;
		}
		$list = $this->cantones->all();
		?>
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-page-header">
				<div class="rodieja-page-header__main">
					<p class="rodieja-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-contable' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'rodieja-contable' ) ); ?></a>
						· <a href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-settings' ) ); ?>"><?php echo $this->escaper->html( __( 'Settings', 'rodieja-contable' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'CR cantons', 'rodieja-contable' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Costa Rica cantons', 'rodieja-contable' ) ); ?></h1>
					<p class="rodieja-page-header__lede"><?php echo $this->escaper->html( __( 'Province / canton pairs stored with the Options API for geo filters.', 'rodieja-contable' ) ); ?></p>
				</div>
			</div>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo $this->escaper->html( __( 'Cantons saved.', 'rodieja-contable' ) ); ?></p></div>
			<?php endif; ?>

			<form method="post" class="rodieja-shell-panel" style="max-width:720px">
				<?php $this->nonce->field( 'rodieja_cantones_settings' ); ?>
				<h2 class="rodieja-shell-panel__title"><?php echo $this->escaper->html( __( 'Directory', 'rodieja-contable' ) ); ?></h2>
				<p class="rodieja-shell-panel__hint"><?php echo $this->escaper->html( __( 'Add a blank row at the bottom for a new pair.', 'rodieja-contable' ) ); ?></p>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php echo $this->escaper->html( __( 'Province', 'rodieja-contable' ) ); ?></th>
							<th><?php echo $this->escaper->html( __( 'Canton', 'rodieja-contable' ) ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $list as $row ) : ?>
						<tr>
							<td><input type="text" class="regular-text" name="provincia[]" value="<?php echo $this->escaper->attr( $row['provincia'] ); ?>" /></td>
							<td><input type="text" class="regular-text" name="canton[]" value="<?php echo $this->escaper->attr( $row['canton'] ); ?>" /></td>
						</tr>
					<?php endforeach; ?>
						<tr>
							<td><input type="text" class="regular-text" name="provincia[]" value="" placeholder="<?php echo $this->escaper->attr( __( 'New province', 'rodieja-contable' ) ); ?>" /></td>
							<td><input type="text" class="regular-text" name="canton[]" value="" placeholder="<?php echo $this->escaper->attr( __( 'New canton', 'rodieja-contable' ) ); ?>" /></td>
						</tr>
					</tbody>
				</table>
				<?php submit_button( __( 'Save cantons', 'rodieja-contable' ) ); ?>
			</form>
		</div>
		<?php
	}
}
