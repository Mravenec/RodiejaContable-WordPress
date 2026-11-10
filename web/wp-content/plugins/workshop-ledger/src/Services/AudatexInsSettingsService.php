<?php
/**
 * Audatex / INS integration flags (Options only — no live API).
 *
 * @package WorkshopLedger
 */

declare(strict_types=1);

namespace WorkshopLedger\Services;

final class AudatexInsSettingsService {

	public const OPTION_KEY = 'wl_audatex_ins_settings';

	/**
	 * @return array{audatex_enabled: bool, ins_enabled: bool, last_import_note: string, last_import_at: string}
	 */
	public function get(): array {
		$raw = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		return array(
			'audatex_enabled'  => ! empty( $raw['audatex_enabled'] ),
			'ins_enabled'      => ! empty( $raw['ins_enabled'] ),
			'last_import_note' => sanitize_text_field( (string) ( $raw['last_import_note'] ?? '' ) ),
			'last_import_at'   => sanitize_text_field( (string) ( $raw['last_import_at'] ?? '' ) ),
		);
	}

	/**
	 * @param array{audatex_enabled?: bool, ins_enabled?: bool, last_import_note?: string, last_import_at?: string} $data
	 */
	public function save( array $data ): void {
		$current = $this->get();
		$next    = array(
			'audatex_enabled'  => isset( $data['audatex_enabled'] ) ? (bool) $data['audatex_enabled'] : $current['audatex_enabled'],
			'ins_enabled'      => isset( $data['ins_enabled'] ) ? (bool) $data['ins_enabled'] : $current['ins_enabled'],
			'last_import_note' => isset( $data['last_import_note'] ) ? sanitize_textarea_field( (string) $data['last_import_note'] ) : $current['last_import_note'],
			'last_import_at'   => isset( $data['last_import_at'] ) ? sanitize_text_field( (string) $data['last_import_at'] ) : $current['last_import_at'],
		);
		update_option( self::OPTION_KEY, $next, false );
	}

	public function note_import( string $note ): void {
		$this->save(
			array(
				'last_import_note' => $note,
				'last_import_at'   => gmdate( 'Y-m-d H:i:s' ),
			)
		);
	}
}
