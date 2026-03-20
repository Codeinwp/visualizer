<?php
/**
 * Stub declarations for Elementor classes used by Visualizer.
 * These stubs are only used for static analysis (PHPStan) and are never loaded at runtime.
 */

namespace Elementor;

class Widget_Base {
	/**
	 * @return string
	 */
	public function get_name() {}

	/**
	 * @return string
	 */
	public function get_title() {}

	/**
	 * @return string
	 */
	public function get_icon() {}

	/**
	 * @return array<string>
	 */
	public function get_categories() {}

	/**
	 * @return array<string>
	 */
	public function get_keywords() {}

	/**
	 * @return void
	 */
	protected function register_controls() {}

	/**
	 * @return void
	 */
	protected function render() {}

	/**
	 * @return array<string, mixed>
	 */
	public function get_settings_for_display() {}

	/**
	 * @param string               $id
	 * @param array<string, mixed> $args
	 * @return void
	 */
	protected function start_controls_section( $id, $args = array() ) {}

	/**
	 * @return void
	 */
	protected function end_controls_section() {}

	/**
	 * @param string               $id
	 * @param array<string, mixed> $args
	 * @return void
	 */
	protected function add_control( $id, $args = array() ) {}
}

class Controls_Manager {
	const TAB_CONTENT = 'content';
	const SELECT      = 'select';
	const RAW_HTML    = 'raw_html';
}

class Editor {
	/**
	 * @return bool
	 */
	public function is_edit_mode() {}
}

class Preview {
	/**
	 * @return bool
	 */
	public function is_preview_mode() {}
}

class Plugin {
	/** @var Plugin */
	public static $instance;

	/** @var Editor */
	public $editor;

	/** @var Preview */
	public $preview;
}

interface Widgets_Manager_Interface {
	/**
	 * @param Widget_Base $widget
	 * @return void
	 */
	public function register( $widget );
}
