<?php

class Visualizer_Render_Templates extends Visualizer_Render {

	protected function _toHTML() {
		?><script id="tmpl-visualizer-library-chart" type="text/html">
			<div class="visualizer-library-chart-footer visualizer-clearfix">
				<a class="visualizer-library-chart-action visualizer-library-chart-delete" href="javascript:;" title="<?php esc_attr_e( 'Delete', Visualizer_Plugin::NAME ) ?>"></a>
				<a class="visualizer-library-chart-action visualizer-library-chart-insert" href="javascript:;" title="<?php esc_attr_e( 'Insert', Visualizer_Plugin::NAME ) ?>"></a>
				<a class="visualizer-library-chart-action visualizer-library-chart-clone" href="javascript:;" title="<?php esc_attr_e( 'Clone', Visualizer_Plugin::NAME ) ?>"></a>

				<span class="visualizer-library-chart-shortcode" title="<?php esc_attr_e( 'Click to select', Visualizer_Plugin::NAME ) ?>">&nbsp;[visualizer id=&quot;{{data.id}}&quot;]&nbsp;</span>
			</div>
		</script>

		<script id="tmpl-visualizer-builder-form" type="text/html">
		</script>

		<script id="tmpl-visualizer-chart-type-picker" type="text/html">
			<?php foreach ( $this->types as $index => $type ) : ?>
			<div class="visualizer-type-box visualizer-type-box-<?php echo $type ?>">
				<label class="visualizer-type-label<?php echo $index == 0 ? ' visualizer-type-label-selected' : '' ?>">
					<input type="radio" class="visualizer-type-radio" name="type" value="<?php echo $type ?>" <?php checked( $index == 0 ) ?>>
				</label>
			</div>
			<?php endforeach; ?>
		</script>
		<?php
	}

}