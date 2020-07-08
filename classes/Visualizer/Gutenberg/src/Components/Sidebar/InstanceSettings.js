/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component } = wp.element;

const {
	PanelBody,
	Notice,
	TextControl
} = wp.components;

class InstanceSettings extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {

		const settings = this.props.chart['visualizer-settings'];

		return (
			<PanelBody
				title={ __( 'Instance Settings' ) }
				initialOpen={ true }
				className="visualizer-instance-panel"
			>

                <Notice status="info" isDismissible={ false } >
                    <p>
                        { __( 'These settings are valid only for this instance of the chart.' ) }
                    </p>
                    <p>
                        { __( 'This means that if you insert this chart again elsewhere, these values will not persist.' ) }
                    </p>
                </Notice>

                <TextControl
                    label={ __( 'Should this instance lazy Load?' ) }
                    help={ __( '-1: do not lazy load. Any number greater than -1 will lazy load the chart once the viewport is that many pixels away from the chart' ) }
                    value={ this.props.attributes.lazy ? Number( this.props.attributes.lazy ) : -1 }
                    type="number"
                    min="-1"
                    max="1000"
                    step="1"
                    onChange={ e => {
                        this.props.attributes.lazy = e;
                        this.props.edit( settings );
                    } }
                />

			</PanelBody>
		);
	}
}

export default InstanceSettings;
