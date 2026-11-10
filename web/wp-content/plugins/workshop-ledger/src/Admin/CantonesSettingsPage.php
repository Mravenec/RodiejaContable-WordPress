<?php
/**
 * Settings · Costa Rica cantons (Options API).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\CostaRicaCantonesService;

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
			wp_die( esc_html__( 'Forbidden.', 'workshop-ledger' ), 403 );
		}
		$saved = false;
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			$this->nonce->verify_admin( 'wl_cantones_settings' );
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
		<div class="wrap wl-admin-wrap">
			<div class="wl-page-header">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=workshop-ledger' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'workshop-ledger' ) ); ?></a>
						· <a href="<?php echo esc_url( admin_url( 'admin.php?page=wl-settings' ) ); ?>"><?php echo $this->escaper->html( __( 'Settings', 'workshop-ledger' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'CR cantons', 'workshop-ledger' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Costa Rica cantons', 'workshop-ledger' ) ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( __( 'Province / canton pairs stored with the Options API for geo filters.', 'workshop-ledger' ) ); ?></p>
				</div>
			</div>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo $this->escaper->html( __( 'Cantons saved.', 'workshop-ledger' ) ); ?></p></div>
			<?php endif; ?>

			<form method="post" class="wl-shell-panel" style="max-width:720px">
				<?php $this->nonce->field( 'wl_cantones_settings' ); ?>
				<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'Directory', 'workshop-ledger' ) ); ?></h2>
				<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Add a blank row at the bottom for a new pair.', 'workshop-ledger' ) ); ?></p>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php echo $this->escaper->html( __( 'Province', 'workshop-ledger' ) ); ?></th>
							<th><?php echo $this->escaper->html( __( 'Canton', 'workshop-ledger' ) ); ?></th>
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
							<td><input type="text" class="regular-text" name="provincia[]" value="" placeholder="<?php echo $this->escaper->attr( __( 'New province', 'workshop-ledger' ) ); ?>" /></td>
							<td><input type="text" class="regular-text" name="canton[]" value="" placeholder="<?php echo $this->escaper->attr( __( 'New canton', 'workshop-ledger' ) ); ?>" /></td>
						</tr>
					</tbody>
				</table>
				<?php submit_button( __( 'Save cantons', 'workshop-ledger' ) ); ?>
			</form>
		</div>
		<?php
	}
}
