<?php

class Visualizer_Render_Page extends Visualizer_Render {

	/**
	 * Enqueues scripts and styles what will be used in a page.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _enqueueScripts() {
		wp_enqueue_style( 'visualizer-frame', VISUALIZER_ABSURL . 'css/frame.css', array( 'buttons' ), Visualizer_Plugin::VERSION );
		wp_enqueue_script( 'visualizer-frame', VISUALIZER_ABSURL . 'js/frame.js', array( 'jquery' ), Visualizer_Plugin::VERSION, true );
	}

	/**
	 * Renders a page.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		$this->_enqueueScripts();

		echo '<!DOCTYPE html>';
		echo '<html>';
			echo '<head>';
				$this->_renderHead();
				wp_print_styles();
				wp_print_head_scripts();
			echo '</head>';
			echo '<body class="wp-core-ui ', implode( ' ', $this->_getBodyClasses() ), '">';
				echo '<form method="post">';
					echo '<div id="content">';
						$this->_renderBody();
					echo '</div>';
					echo '<div id="toolbar">';
						$this->_renderToolbar();
					echo '</div>';
				echo '</form>';
				wp_print_footer_scripts();
			echo '</body>';
		echo '</html>';
	}

	/**
	 * Renders page head.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderHead() {
		echo '<meta charset="', get_bloginfo( 'charset' ), '">';
		echo '<title>Visualizer Chart Builder</title>';
	}

	/**
	 * Renders page body.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderBody() {}

	/**
	 * Renders toolbar content.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderToolbar() {}

	/**
	 * Renturns page body classes.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @return array The array of classes.
	 */
	protected function _getBodyClasses() {
		return array();
	}

}