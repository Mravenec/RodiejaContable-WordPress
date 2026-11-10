<?php
/**
 * Settings · Audatex/INS flags (Options API — no live API).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Admin;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Security\NonceGuard;
use Rodieja\Contable\Security\OutputEscaper;
use Rodieja\Contable\Services\AudatexInsSettingsService;

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
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		$saved = false;
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			$this->nonce->verify_admin( 'rodieja_audatex_ins_settings' );
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
		<div class="wrap rodieja-admin-wrap">
			<div class="rodieja-page-header">
				<div class="rodieja-page-header__main">
					<p class="rodieja-page-header__crumbs">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-contable' ) ); ?>"><?php echo $this->escaper->html( __( 'Home', 'rodieja-contable' ) ); ?></a>
						· <a href="<?php echo esc_url( admin_url( 'admin.php?page=rodieja-settings' ) ); ?>"><?php echo $this->escaper->html( __( 'Settings', 'rodieja-contable' ) ); ?></a>
						· <?php echo $this->escaper->html( __( 'Integrations', 'rodieja-contable' ) ); ?>
					</p>
					<h1><?php echo $this->escaper->html( __( 'Integrations', 'rodieja-contable' ) ); ?></h1>
					<p class="rodieja-page-header__lede"><?php echo $this->escaper->html( __( 'Local Audatex / INS flags — Options API only, no live sync.', 'rodieja-contable' ) ); ?></p>
				</div>
			</div>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo $this->escaper->html( __( 'Settings saved.', 'rodieja-contable' ) ); ?></p></div>
			<?php endif; ?>

			<div class="rodieja-shell-grid">
				<form method="post" class="rodieja-shell-panel">
					<?php $this->nonce->field( 'rodieja_audatex_ins_settings' ); ?>
					<h2 class="rodieja-shell-panel__title"><?php echo $this->escaper->html( __( 'Flags', 'rodieja-contable' ) ); ?></h2>
					<p class="rodieja-shell-panel__hint"><?php echo $this->escaper->html( __( 'Enable manual file import paths used by Audatex InPart.', 'rodieja-contable' ) ); ?></p>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php echo $this->escaper->html( __( 'Enable Audatex', 'rodieja-contable' ) ); ?></th>
							<td><label><input type="checkbox" name="audatex_enabled" value="1" <?php checked( $s['audatex_enabled'] ); ?> /> <?php echo $this->escaper->html( __( 'Manual / file import', 'rodieja-contable' ) ); ?></label></td>
						</tr>
						<tr>
							<th scope="row"><?php echo $this->escaper->html( __( 'Enable INS', 'rodieja-contable' ) ); ?></th>
							<td><label><input type="checkbox" name="ins_enabled" value="1" <?php checked( $s['ins_enabled'] ); ?> /></label></td>
						</tr>
						<tr>
							<th scope="row"><?php echo $this->escaper->html( __( 'Last import', 'rodieja-contable' ) ); ?></th>
							<td><?php echo $this->escaper->html( $s['last_import_at'] ?: '—' ); ?></td>
						</tr>
						<tr>
							<th scope="row"><label for="last_import_note"><?php echo $this->escaper->html( __( 'Last import note', 'rodieja-contable' ) ); ?></label></th>
							<td><textarea class="large-text" rows="3" name="last_import_note" id="last_import_note"><?php echo $this->escaper->html( $s['last_import_note'] ); ?></textarea></td>
						</tr>
					</table>
					<?php submit_button( __( 'Save', 'rodieja-contable' ) ); ?>
				</form>
			</div>
		</div>
		<?php
	}
}
