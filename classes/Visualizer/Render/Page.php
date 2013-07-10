<?php

class Visualizer_Render_Page extends Visualizer_Render {

	protected function _enqueueScripts() {
		wp_enqueue_style( 'visualizer-frame', VISUALIZER_ABSURL . 'css/frame.css', array(), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-frame', VISUALIZER_ABSURL . 'js/frame.js', array( 'jquery' ), Visualizer_Plugin::VERSION, true );
	}

	protected function _toHTML() {
		$this->_enqueueScripts();

		echo '<!DOCTYPE html>';
		echo '<html>';
			echo '<head>';
				$this->_renderHead();
				wp_print_styles();
				wp_print_head_scripts();
			echo '</head>';
			echo '<body>';
				$this->_renderBody();
				wp_print_footer_scripts();
			echo '</body>';
		echo '</html>';
	}

	protected function _renderHead() {
		echo '<meta charset="', get_bloginfo( 'charset' ), '">';
		echo '<title>Visualizer Chart Builder</title>';
	}

	protected function _renderBody() {

	}

}