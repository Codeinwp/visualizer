/**
 * Block dependencies
 */
import EditorArea from './Editor.js';

import './style.scss';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;

const { registerBlockType } = wp.blocks;

/**
 * Register block
 */
export default registerBlockType( 'visualizer/chart', {

	// Block Title
	title: __( 'Visualizer Chart' ),

	// Block Description
	description: __( 'A simple, easy to use and quite powerful tool to create, manage and embed interactive charts into your WordPress posts and pages.' ),

	// Block Category
	category: 'common',

	// Block Icon
	icon: 'chart-pie',

	// Block Keywords
	keywords: [
		__( 'Visualizer' ),
		__( 'Chart' ),
		__( 'Google Charts' )
	],
	attributes: {
		id: {
			type: 'number'
		},
		route: {
			type: 'string'
		}
	},
	supports: {
		customClassName: false
	},

	// Defining the edit interface
	edit: EditorArea,

	// Defining the front-end interface
	save() {

		// Rendering in PHP
		return null;
	}
});
