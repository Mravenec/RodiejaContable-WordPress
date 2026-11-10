<?php
/**
 * Configuración · Audatex/INS (Options, sin API live).
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
		// Rendered via AdminMenu · Configuración.
	}

	public function render(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'rodieja-contable' ), 403 );
		}
		if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			$this->nonce->verify_admin( 'rodieja_audatex_ins_settings' );
			$this->settings->save(
				array(
					'audatex_enabled'  => ! empty( $_POST['audatex_enabled'] ),
					'ins_enabled'      => ! empty( $_POST['ins_enabled'] ),
					'last_import_note' => isset( $_POST['last_import_note'] ) ? (string) wp_unslash( $_POST['last_import_note'] ) : '',
				)
			);
			echo '<div class="notice notice-success"><p>' . $this->escaper->html( __( 'Configuración guardada.', 'rodieja-contable' ) ) . '</p></div>';
		}
		$s = $this->settings->get();
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Configuración', 'rodieja-contable' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Flags de integración local — no hay sincronización en vivo con Audatex ni INS.', 'rodieja-contable' ) ); ?></p>
			<form method="post">
				<?php $this->nonce->field( 'rodieja_audatex_ins_settings' ); ?>
				<table class="form-table">
					<tr>
						<th><?php echo $this->escaper->html( __( 'Habilitar Audatex', 'rodieja-contable' ) ); ?></th>
						<td><label><input type="checkbox" name="audatex_enabled" value="1" <?php checked( $s['audatex_enabled'] ); ?> /> <?php echo $this->escaper->html( __( 'Import manual / archivo', 'rodieja-contable' ) ); ?></label></td>
					</tr>
					<tr>
						<th><?php echo $this->escaper->html( __( 'Habilitar INS', 'rodieja-contable' ) ); ?></th>
						<td><label><input type="checkbox" name="ins_enabled" value="1" <?php checked( $s['ins_enabled'] ); ?> /></label></td>
					</tr>
					<tr>
						<th><?php echo $this->escaper->html( __( 'Última importación', 'rodieja-contable' ) ); ?></th>
						<td><?php echo $this->escaper->html( $s['last_import_at'] ?: '—' ); ?></td>
					</tr>
					<tr>
						<th><label for="last_import_note"><?php echo $this->escaper->html( __( 'Nota última importación', 'rodieja-contable' ) ); ?></label></th>
						<td><textarea class="large-text" rows="3" name="last_import_note" id="last_import_note"><?php echo $this->escaper->html( $s['last_import_note'] ); ?></textarea></td>
					</tr>
				</table>
				<?php submit_button( __( 'Guardar', 'rodieja-contable' ) ); ?>
			</form>
		</div>
		<?php
	}
}
