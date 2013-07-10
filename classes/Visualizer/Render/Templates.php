<?php

class Visualizer_Render_Templates extends Visualizer_Render {

	private $_templates = array(
		'library-chart',
	);

	private function _renderTemplate( $id, $callback ) {
		echo '<script id="tmpl-visualizer-', $id, '" type="text/html">';
			call_user_func( array( $this, $callback ) );
		echo '</script>';
	}

	protected function _renderLibraryChart() {
		echo '<div class="visualizer-library-chart-footer visualizer-clearfix">';
			echo '<a class="visualizer-library-chart-action visualizer-library-chart-delete" href="javascript:;" title="', esc_attr__( 'Delete', Visualizer_Plugin::NAME ), '"></a>';
			echo '<a class="visualizer-library-chart-action visualizer-library-chart-insert" href="javascript:;" title="', esc_attr__( 'Insert', Visualizer_Plugin::NAME ), '"></a>';
			echo '<a class="visualizer-library-chart-action visualizer-library-chart-clone" href="javascript:;" title="', esc_attr__( 'Clone', Visualizer_Plugin::NAME ), '"></a>';

			echo '<span class="visualizer-library-chart-shortcode" title="', esc_attr__( 'Click to select', Visualizer_Plugin::NAME ), '">&nbsp;[visualizer id=&quot;{{data.id}}&quot;]&nbsp;</span>';
		echo '</div>';
	}

	protected function _toHTML() {
		foreach ( $this->_templates as $template ) {
			$callback = '_render' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $template ) ) );
			$this->_renderTemplate( $template, $callback );
		}
	}

}