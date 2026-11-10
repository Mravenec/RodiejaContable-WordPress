<?php
/**
 * Weekly WP-Cron hook for monthly-style resumen email.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Cron;

use Rodieja\Contable\Services\MonthlyResumenService;

final class ResumenCronRegistrar {

	public const HOOK = 'rodieja_weekly_resumen_cron';

	public function __construct( private MonthlyResumenService $resumen ) {}

	public function register(): void {
		add_action( self::HOOK, array( $this, 'run' ) );
		add_action( 'init', array( $this, 'maybe_schedule' ) );
	}

	public function maybe_schedule(): void {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'weekly', self::HOOK );
		}
	}

	public function run(): void {
		$this->resumen->send_to_admin();
	}
}
