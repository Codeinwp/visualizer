const { __ } = wp.i18n;
const {
	registerBlockType,
	Editable,
} = wp.blocks;

const {
	InspectorControls,
} = wp.editor;

const { 
    ToggleControl,
    SelectControl,
    Spinner,
} = wp.components;

var el = wp.element.createElement;

const consoleLog = msg => {
    //console.log(msg);
}

registerBlockType( 'visualizer/chart', {
	title: vjs.i10n.plugin,
	icon: 'index-card',
	category: 'common',
	supports: {
		html: false,
	},
    attributes: {

    },
    edit: props => {
        return [
        ];
    },
    save: props => {
       return null;
    },
} );