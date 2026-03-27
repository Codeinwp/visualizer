<?php
/**
 * Global chart style settings page template.
 *
 * Variables available:
 *   $settings  array  Current global settings (color_primary, color_secondary)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$updated = isset( $_GET['updated'] ) ? sanitize_text_field( $_GET['updated'] ) : '';
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Visualizer Settings', 'visualizer' ); ?></h1>

	<?php if ( 'true' === $updated ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'visualizer' ); ?></p>
		</div>
	<?php elseif ( 'cleared' === $updated ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings cleared.', 'visualizer' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'visualizer_save_global_settings' ); ?>
		<input type="hidden" name="action" value="visualizer_save_global_settings" />

		<table class="form-table" role="presentation">
			<tbody>

				<tr>
					<th scope="row">
						<label for="visualizer-color-primary"><?php esc_html_e( 'Primary Color', 'visualizer' ); ?></label>
					</th>
					<td>
						<input
							type="text"
							id="visualizer-color-primary"
							name="visualizer_color_primary"
							class="visualizer-color-picker"
							value="<?php echo esc_attr( $settings['color_primary'] ); ?>"
							data-default-color=""
						/>
						<p class="description"><?php esc_html_e( 'Primary color applied to the first data series in new charts.', 'visualizer' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="visualizer-color-secondary"><?php esc_html_e( 'Secondary Color', 'visualizer' ); ?></label>
					</th>
					<td>
						<input
							type="text"
							id="visualizer-color-secondary"
							name="visualizer_color_secondary"
							class="visualizer-color-picker"
							value="<?php echo esc_attr( $settings['color_secondary'] ); ?>"
							data-default-color=""
						/>
						<p class="description"><?php esc_html_e( 'Secondary color applied to the second data series in new charts.', 'visualizer' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="visualizer-apply-existing"><?php esc_html_e( 'Apply To Existing Charts', 'visualizer' ); ?></label>
					</th>
					<td>
						<label>
							<input
								type="checkbox"
								id="visualizer-apply-existing"
								name="visualizer_apply_existing"
								value="1"
								<?php checked( $settings['apply_existing'], '1' ); ?>
							/>
							<?php esc_html_e( 'Apply global colors to existing charts at render time (this will override chart colors on display).', 'visualizer' ); ?>
						</label>
					</td>
				</tr>

			</tbody>
		</table>

		<p class="submit">
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Save Settings', 'visualizer' ); ?>
			</button>
			<button type="submit" name="visualizer_clear_settings" value="1" class="button" style="margin-left:10px;">
				<?php esc_html_e( 'Clear Settings', 'visualizer' ); ?>
			</button>
		</p>
	</form>
</div>

<script type="text/javascript">
(function($) {
	$(document).ready(function() {
		$('.visualizer-color-picker').wpColorPicker();
	});
})(jQuery);
</script>
