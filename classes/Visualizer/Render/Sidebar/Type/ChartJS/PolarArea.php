<?php

/**
 * Class for polar area chart sidebar settings.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 3.2.0
 */
class Visualizer_Render_Sidebar_Type_ChartJS_PolarArea extends Visualizer_Render_Sidebar_Type_ChartJS_Pie {

	/**
	 * The constructor.
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );
	}

	/**
	 * Renders settings specific to the Pie chart.
	 *
	 * @since 3.3.0
	 *
	 * @access protected
	 */
	protected function _renderChartTypeSettings() {
		self::_renderGroupStart( esc_html__( 'Slices Settings', 'visualizer' ) );
		for ( $i = 0, $cnt = count( $this->__data ); $i < $cnt; $i++ ) {
			self::_renderSectionStart( esc_html( $this->__data[ $i ][0] ), false );
				$this->_renderSliceSettings( $i );
			self::_renderSectionEnd();
		}
		self::_renderGroupEnd();
	}

}
