---
id: APP-5-3
title: Security · caps contador en REST mutaciones
role: security
epic: 5
estimate: 1
blocked_by:
  - APP-5-2
blocks: []
agent: security-agent-1
claimed_at: "2026-11-10T05:44:55.131Z"
---
## Objetivo

Security · caps contador en REST mutaciones

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### Finance REST permission_callback

Path: `src/Controllers/Rest/FinanceController.php`

```php
<?php
/**
 * Authenticated finance totals REST (mutations would use same gate).
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Controllers\Rest;

use Rodieja\Contable\Security\CapabilityGate;
use Rodieja\Contable\Services\FinanceTotalsService;
use WP_REST_Request;
use WP_REST_Response;

final class FinanceController {

	public const NAMESPACE = 'rodieja/v1';

	public function __construct(
		private FinanceTotalsService $totals,
		private CapabilityGate $gate
	) {}

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/finance/totals',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_totals' ),
				'permission_callback' => array( $this->gate, 'rest_can_manage' ),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/finance/transactions',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_transaction' ),
				'permission_callback' => array( $this->gate, 'rest_can_manage' ),
				'args'                => array(
					'title'  => array( 'required' => true, 'type' => 'string' ),
					'monto'  => array( 'required' => true, 'type' => 'number' ),
					'sentido'=> array( 'required' => true, 'type' => 'string' ),
					'fecha'  => array( 'required' => false, 'type' => 'string' ),
				),
/* … truncated 44 more lines — open the file */
```

## History

- 2026-11-10T05:44:55.886Z moved testing → done
- 2026-11-10T05:44:55.812Z checklist item 2 marked
- 2026-11-10T05:44:55.740Z moved doing → testing
- 2026-11-10T05:44:55.588Z checklist item 1 marked
- 2026-11-10T05:44:55.131Z claimed by security-agent-1 → doing
- 2026-11-10T05:35:44.473Z created → backlog
