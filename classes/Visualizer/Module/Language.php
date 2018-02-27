<?php
/**
 * The Language function file for tinymce.
 *
 * @link       http://themeisle.com
 * @since      3.0.0
 */
/**
 *
 * SECURITY : Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed!' );
}

/**
 *
 * Translation for TinyMCE
 */

if ( ! class_exists( '_WP_Editors' ) ) {
	require( ABSPATH . WPINC . '/class-wp-editor.php' );
}

/**
 * The module for all languages stuff.
 *
 * @category Visualizer
 * @package Module
 *
 * @since 1.0.0
 */
class Visualizer_Module_Language extends Visualizer_Module {

	/**
	 * The strings for translation.
	 *
	 * @access   protected
	 * @var      array $strings The ID of this plugin.
	 */
	protected $strings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    3.0.0
	 * @access   public
	 */
	public function __construct() {
		$this->strings = array(
			'plugin_label'  => __( 'Insert Chart', 'visualizer' ),
			'plugin_title'  => __( 'Insert Chart', 'visualizer' ),
		);
	}

	/**
	 *
	 * The method that returns the translation array
	 *
	 * @access   public
	 * @return string
	 */
	public function tinymce_translation() {

		$locale     = _WP_Editors::$mce_locale;
		$translated = 'tinyMCE.addI18n("' . $locale . '.visualizer_tinymce_plugin", ' . json_encode( $this->strings ) . ");\n";

		return $translated;
	}

}

$visualizerLangClass = new Visualizer_Module_Language();
$strings         = $visualizerLangClass->tinymce_translation();
