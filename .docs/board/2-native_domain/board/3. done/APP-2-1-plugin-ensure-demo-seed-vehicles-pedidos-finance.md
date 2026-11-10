---
id: APP-2-1
title: Plugin · ensure demo seed vehicles pedidos finance
role: plugin
epic: 2
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T05:20:11.293Z"
---
## Objetivo

Plugin · ensure demo seed vehicles pedidos finance

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### ensure seed fills pedidos

Path: `src/Services/DemoSeedService.php`

```php
<?php
/**
 * Seeds demo content via native wp_insert_post / meta / terms only.
 *
 * @package Rodieja\Contable
 */

declare(strict_types=1);

namespace Rodieja\Contable\Services;

final class DemoSeedService {

	/**
	 * Seed when empty, or fill missing domain posts if a partial seed exists.
	 *
	 * @return array<string, mixed>
	 */
	public function seed_if_empty(): array {
		$vehicle_count = (int) ( wp_count_posts( 'rodieja_vehiculo' )->publish ?? 0 );
		if ( 0 === $vehicle_count ) {
			return $this->seed_full();
		}
		return $this->ensure_missing();
	}

	/**
	 * @return array<string, mixed>
	 */
	private function seed_full(): array {
		$marca_id  = $this->post( 'rodieja_marca', 'Toyota' );
		$modelo_id = $this->post( 'rodieja_modelo', 'Corolla', array( 'rodieja_marca_id' => (string) $marca_id ) );
		$gen_id    = $this->post(
			'rodieja_generacion',
			'12th Gen',
			array(
				'rodieja_modelo_id'   => (string) $modelo_id,
				'rodieja_anio_inicio' => 2019,
				'rodieja_anio_fin'    => 2024,
			)
		);

		$this->ensure_terms();

		$vehiculo_id = $this->post(
			'rodieja_vehiculo',
			'Toyota Corolla 2020',
			array(
/* … truncated 198 more lines — open the file */
```

## History

- 2026-11-10T05:23:21.594Z moved testing → done
- 2026-11-10T05:22:05.467Z checklist item 2 marked
- 2026-11-10T05:22:05.380Z moved doing → testing
- 2026-11-10T05:22:05.209Z checklist item 1 marked
- 2026-11-10T05:20:11.294Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:20:02.465Z created → backlog
