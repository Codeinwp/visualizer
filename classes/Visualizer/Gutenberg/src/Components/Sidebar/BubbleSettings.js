/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const { ColorPalette } = wp.blockEditor || wp.editor;

import { isChecked } from '../../utils.js';

const {
	CheckboxControl,
	BaseControl,
	PanelBody,
	TextControl
} = wp.components;

class BubbleSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	componentDidMount() {

	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Bubble Settings' ) }
				initialOpen={ false }
				className="visualizer-advanced-panel"
			>

                <TextControl
                    label={ __( 'Opacity' ) }
                    help={ __( 'The default opacity of the bubbles, where 0.0 is fully transparent and 1.0 is fully opaque.' ) }
                    type="number"
                    min="0"
                    max="1"
                    step="0.1"
                    value={ settings.bubble.opacity }
                    onChange={ e => {
                        if ( ! settings.bubble ) {
                            settings.bubble = {};
                        }
                        settings.bubble.opacity = e;
                        this.props.edit( settings );
                    } }
                />

                <BaseControl
                    label={ __( 'Stroke Color' ) }
                >
                    <ColorPalette
                        value={ settings.bubble.stroke }
                        onChange={ e => {
                            if ( ! settings.bubble ) {
                                settings.bubble = {};
                            }
                            settings.bubble.stroke = e;
                            this.props.edit( settings );
                        } }
                    />
                </BaseControl>

				<CheckboxControl
					label={ __( 'Sort Bubbles by Size' ) }
					help={ __( 'If checked, sorts the bubbles by size so the smaller bubbles appear above the larger bubbles. If unchecked, bubbles are sorted according to their order in the table.' ) }
					checked={ isChecked( settings, 'sortBubblesBySize' ) }
					onChange={ e => {
						settings.sortBubblesBySize = e;
						this.props.edit( settings );
					} }
				/>

                <TextControl
                    label={ __( 'Size (max)' ) }
                    help={ __( 'The size value (as appears in the chart data) to be mapped to sizeAxis.maxSize. Larger values will be cropped to this value.' ) }
                    type="number"
                    step="1"
                    value={ settings.sizeAxis.maxValue }
                    onChange={ e => {
                        if ( ! settings.sizeAxis ) {
                            settings.sizeAxis = {};
                        }
                        settings.sizeAxis.maxValue = e;
                        this.props.edit( settings );
                    } }
                />

                <TextControl
                    label={ __( 'Size (min)' ) }
                    help={ __( 'The size value (as appears in the chart data) to be mapped to sizeAxis.minSize. Smaller values will be cropped to this value.' ) }
                    type="number"
                    step="1"
                    value={ settings.sizeAxis.minValue }
                    onChange={ e => {
                        if ( ! settings.sizeAxis ) {
                            settings.sizeAxis = {};
                        }
                        settings.sizeAxis.minValue = e;
                        this.props.edit( settings );
                    } }
                />

			</PanelBody>
		);
	}
}

export default BubbleSettings;
