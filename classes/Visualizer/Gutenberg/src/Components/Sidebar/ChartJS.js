import Sidebar from '../Sidebar.js';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const { ColorPalette } = wp.blockEditor || wp.editor;

const {
	BaseControl,
	CheckboxControl,
	PanelBody,
	SelectControl,
	TextControl
} = wp.components;

class ChartJS extends Sidebar {
	constructor() {
		super( ...arguments );
	}
}
