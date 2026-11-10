<?php
/**
 * Unit smoke for sanitizer (no WP bootstrap required for these calls if stubs exist).
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Tests\Unit;

use Harness\Plugin\Security\RequestSanitizer;
use PHPUnit\Framework\TestCase;

final class RequestSanitizerTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		if ( ! function_exists( 'sanitize_text_field' ) ) {
			$this->markTestSkipped( 'WordPress functions unavailable outside WP bootstrap.' );
		}
	}

	public function test_absint_value(): void {
		$s = new RequestSanitizer();
		$this->assertSame( 12, $s->absint_value( '12abc' ) );
	}
}
