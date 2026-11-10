<?php
/**
 * Settings · Audatex/INS flags (Options API — no live API).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Admin;

use WorkshopLedger\Security\CapabilityGate;
use WorkshopLedger\Security\NonceGuard;
use WorkshopLedger\Security\OutputEscaper;
use WorkshopLedger\Services\AudatexInsSettingsService;

final class AudatexInsSettingsPage {

	public function __construct(
		private OutputEscaper $escaper,
		private CapabilityGate $gate,
		private NonceGuard $nonce,
		private AudatexInsSettingsService $settings
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
			$this->nonce->verify_admin( 'wl_audatex_ins_settings' );
			$this->settings->save(
				array(
					'audatex_enabled'  => ! empty( $_POST['audatex_enabled'] ),
					'ins_enabled'      => ! empty( $_POST['ins_enabled'] ),
					'last_import_note' => isset( $_POST['last_import_note'] ) ? (string) wp_unslash( $_POST['last_import_note'] ) : '',
				)
			);
			$saved = true;
		}
		$s = $this->settings->get();
		?>
		<div class="wrap wl-admin-wrap">
			<div class="wl-page-header">
				<div class="wl-page-header__main">
					<p class="wl-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=workshop-ledger' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'workshop-ledger' ) ); ?></a>
						· <a href="<?php echo esc_url( admin_url( 'admin.php?page=wl-settings' ) ); ?>"><?php echo $this->escaper->html( __( 'Settings', 'workshop-ledger' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Integrations', 'workshop-ledger' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Integrations', 'workshop-ledger' ) ); ?></h1>
					<p class="wl-page-header__lede"><?php echo $this->escaper->html( __( 'Local Audatex / INS flags — Options API only, no live sync.', 'workshop-ledger' ) ); ?></p>
				</div>
			</div>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo $this->escaper->html( __( 'Settings saved.', 'workshop-ledger' ) ); ?></p></div>
			<?php endif; ?>

			<div class="wl-shell-grid">
				<form method="post" class="wl-shell-panel">
					<?php $this->nonce->field( 'wl_audatex_ins_settings' ); ?>
					<h2 class="wl-shell-panel__title"><?php echo $this->escaper->html( __( 'Flags', 'workshop-ledger' ) ); ?></h2>
					<p class="wl-shell-panel__hint"><?php echo $this->escaper->html( __( 'Enable manual file import paths used by Audatex InPart.', 'workshop-ledger' ) ); ?></p>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php echo $this->escaper->html( __( 'Enable Audatex', 'workshop-ledger' ) ); ?></th>
							<td><label><input type="checkbox" name="audatex_enabled" value="1" <?php checked( $s['audatex_enabled'] ); ?> /> <?php echo $this->escaper->html( __( 'Manual / file import', 'workshop-ledger' ) ); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><?php echo $this->escaper->html( __( 'Enable INS', 'workshop-ledger' ) ); ?></th>
							<td><label><input type="checkbox" name="ins_enabled" value="1" <?php checked( $s['ins_enabled'] ); ?> /></label></td>
						</tr>
						<tr>
							<th scope="row"><?php echo $this->escaper->html( __( 'Last import', 'workshop-ledger' ) ); ?></th>
							<td><?php echo $this->escaper->html( $s['last_import_at'] ?: '—' ); ?></td>
						</tr>
						<tr>
							<th scope="row"><label for="last_import_note"><?php echo $this->escaper->html( __( 'Last import note', 'workshop-ledger' ) ); ?></label></th>
							<td><textarea class="large-text" rows="3" name="last_import_note" id="last_import_note"><?php echo $this->escaper->html( $s['last_import_note'] ); ?></textarea></td>
						</tr>
					</table>
					<?php submit_button( __( 'Save', 'workshop-ledger' ) ); ?>
				</form>
			</div>
		</div>
		<?php
	}
}
