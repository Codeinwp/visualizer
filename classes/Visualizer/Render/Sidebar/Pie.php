<?php

// +----------------------------------------------------------------------+
// | Copyright 2013  Madpixels  (email : visualizer@madpixels.net)        |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+
// | Author: Eugene Manuilov <eugene@manuilov.org>                        |
// +----------------------------------------------------------------------+


/**
 * Base class for all chart builder pages.
 *
 * @category Visualizer
 * @package Render
 * @subpackage Sidebar
 *
 * @since 1.0.0
 */
class Visualizer_Render_Sidebar_Pie extends Visualizer_Render_Sidebar {

	/**
	 * The Yes/No array.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_yesno;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $data The data what has to be associated with this render.
	 */
	public function __construct( $data = array( ) ) {
		parent::__construct( $data );

		$this->_yesno = array(
			''  => '',
			'1' => esc_html__( 'Yes', Visualizer_Plugin::NAME ),
			'0' => esc_html__( 'No', Visualizer_Plugin::NAME ),
		);
	}

	/**
	 * Renders template.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _toHTML() {
		$this->_renderGeneralSettings();
		$this->_renderPieSettings();
		$this->_renderResidueSettings();
		$this->_renderSlicesSettings();
		$this->_renderViewSettings();
	}

	/**
	 * Renders pie settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderPieSettings() {
		echo '<li class="group">';
			echo '<h3 class="group-title">', esc_html__( 'Pie Settings', Visualizer_Plugin::NAME ), '</h3>';
			echo '<ul class="group-content">';
				echo '<li>';
					echo '<div class="section-items open">';
						self::_renderSelectItem(
							esc_html__( 'Is 3D', Visualizer_Plugin::NAME ),
							'is3D',
							$this->is3D,
							$this->_yesno,
							esc_html__( 'If set to yes, displays a three-dimensional chart.', Visualizer_Plugin::NAME )
						);

						self::_renderSelectItem(
							esc_html__( 'Reverse Categories', Visualizer_Plugin::NAME ),
							'reverseCategories',
							$this->reverseCategories,
							$this->_yesno,
							esc_html__( 'If set to yes, will draw slices counterclockwise.', Visualizer_Plugin::NAME )
						);

						self::_renderSelectItem(
							esc_html__( 'Slice Text', Visualizer_Plugin::NAME ),
							'pieSliceText',
							$this->pieSliceText,
							array(
								''           => '',
								'percentage' => esc_html__( 'The percentage of the slice size out of the total', Visualizer_Plugin::NAME ),
								'value'      => esc_html__( 'The quantitative value of the slice', Visualizer_Plugin::NAME ),
								'label'      => esc_html__( 'The name of the slice', Visualizer_Plugin::NAME ),
								'none'       => esc_html__( 'No text is displayed', Visualizer_Plugin::NAME ),
							),
							esc_html__( 'The content of the text displayed on the slice.', Visualizer_Plugin::NAME )
						);

						self::_renderColorPickerItem(
							esc_html__( 'Slice Border Color', Visualizer_Plugin::NAME ),
							'pieSliceBorderColor',
							$this->pieSliceBorderColor,
							'#fff'
						);
					echo '</div>';
				echo '</li>';
			echo '</ul>';
		echo '</li>';
	}

	/**
	 * Renders residue settings group.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderResidueSettings() {
		echo '<li class="group">';
			echo '<h3 class="group-title">', esc_html__( 'Residue Settings', Visualizer_Plugin::NAME ), '</h3>';
			echo '<ul class="group-content">';
				echo '<li>';
					echo '<div class="section-items open">';
						self::_renderTextItem(
							esc_html__( 'Visibility Threshold', Visualizer_Plugin::NAME ),
							'sliceVisibilityThreshold',
							$this->sliceVisibilityThreshold,
							esc_html__( 'The slice relative part, below which a slice will not show individually. All slices that have not passed this threshold will be combined to a single slice, whose size is the sum of all their sizes. Default is not to show individually any slice which is smaller than half a degree.', Visualizer_Plugin::NAME ),
							'0.001388889'
						);

						self::_renderTextItem(
							esc_html__( 'Residue Slice Label', Visualizer_Plugin::NAME ),
							'pieResidueSliceLabel',
							$this->pieResidueSliceLabel,
							esc_html__( 'A label for the combination slice that holds all slices below slice visibility threshold.' ),
							esc_html__( 'Other', Visualizer_Plugin::NAME )
						);

						self::_renderColorPickerItem(
							esc_html__( 'Residue Slice Color', Visualizer_Plugin::NAME ),
							'pieResidueSliceColor',
							$this->pieResidueSliceColor,
							'#ccc'
						);
					echo '</div>';
				echo '</li>';
			echo '</ul>';
		echo '</li>';
	}

	/**
	 * Renders slices settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _renderSlicesSettings() {
		echo '<li class="group">';
			echo '<h3 class="group-title">', esc_html__( 'Slices Colors', Visualizer_Plugin::NAME ), '</h3>';
			echo '<ul class="group-content">';
				echo '<li>';
					echo '<div class="section-items open">';
						for ( $i = 0, $cnt = count( $this->__data ); $i < $cnt; $i++ ) {
							self::_renderColorPickerItem(
								$this->__data[$i][0],
								'slices[' . $i . '][color]',
								isset( $this->slices[$i]['color'] ) ? $this->slices[$i]['color'] : null,
								null
							);
						}
					echo '</div>';
				echo '</li>';
			echo '</ul>';
		echo '</li>';
	}

}