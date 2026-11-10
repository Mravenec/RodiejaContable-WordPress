<?php
/**
 * Minimal admin screen with nonce + capability + escaped output.
 *
 * @package Harness\Plugin
 */

declare(strict_types=1);

namespace Harness\Plugin\Controllers;

use Harness\Plugin\Security\CapabilityGate;
use Harness\Plugin\Security\NonceGuard;
use Harness\Plugin\Security\OutputEscaper;
use Harness\Plugin\Security\RequestSanitizer;
use Harness\Plugin\Services\ItemService;

final class AdminController {

	public function __construct(
		private ItemService $items,
		private CapabilityGate $gate,
		private NonceGuard $nonces,
		private RequestSanitizer $sanitizer,
		private OutputEscaper $escaper
	) {}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_post_harness_create_item', array( $this, 'handle_create' ) );
	}

	public function add_menu(): void {
		add_menu_page(
			__( 'Harness', 'harness-plugin' ),
			__( 'Harness', 'harness-plugin' ),
			CapabilityGate::MANAGE,
			'harness-plugin',
			array( $this, 'render_page' ),
			'dashicons-shield',
			58
		);
	}

	public function handle_create(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'harness-plugin' ), 403 );
		}

		$this->nonces->verify_admin();

		$title  = $this->sanitizer->text( $_POST['title'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified above.
		$result = $this->items->create_item( $title );

		$redirect = add_query_arg(
			array(
				'page'    => 'harness-plugin',
				'updated' => is_wp_error( $result ) ? '0' : '1',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	public function render_page(): void {
		if ( ! $this->gate->can_manage() ) {
			wp_die( esc_html__( 'Forbidden.', 'harness-plugin' ), 403 );
		}

		$items = $this->items->list_items( 20, 0 );
		?>
		<div class="wrap">
			<h1><?php echo $this->escaper->html( __( 'Harness Plugin', 'harness-plugin' ) ); ?></h1>
			<p><?php echo $this->escaper->html( __( 'Neutral scaffold — security-first demo items.', 'harness-plugin' ) ); ?></p>

			<form method="post" action="<?php echo $this->escaper->url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="harness_create_item" />
				<?php $this->nonces->field(); ?>
				<label for="harness-title"><?php echo $this->escaper->html( __( 'Title', 'harness-plugin' ) ); ?></label>
				<input type="text" id="harness-title" name="title" class="regular-text" maxlength="191" required />
				<?php submit_button( __( 'Add item', 'harness-plugin' ), 'primary', 'submit', false ); ?>
			</form>

			<table class="widefat striped" style="margin-top:1.5rem;max-width:640px;">
				<thead>
					<tr>
						<th><?php echo $this->escaper->html( __( 'ID', 'harness-plugin' ) ); ?></th>
						<th><?php echo $this->escaper->html( __( 'Title', 'harness-plugin' ) ); ?></th>
						<th><?php echo $this->escaper->html( __( 'Created', 'harness-plugin' ) ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $items ) ) : ?>
					<tr><td colspan="3"><?php echo $this->escaper->html( __( 'No items yet.', 'harness-plugin' ) ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><?php echo $this->escaper->html( (string) $item['id'] ); ?></td>
							<td><?php echo $this->escaper->html( $item['title'] ); ?></td>
							<td><?php echo $this->escaper->html( $item['created_at'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
