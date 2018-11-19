/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
const {
	Button,
	Dashicon,
	G,
	Path,
	SVG
} = wp.components;

function PanelButton({ label, icon, className, isBack, onClick }) {
	const classes = classnames( 'components-panel__body', 'components-panel__body-button', className, { 'visualizer-panel-back': isBack });
	return (
		<div className={ classes }>
			<h2 className="components-panel__body-title">
				<Button
					className="components-panel__body-toggle"
					onClick={ onClick }
				>
					<SVG className="components-panel__arrow" width="24px" height="24px" viewBox="-12 -12 48 48" xmlns="http://www.w3.org/2000/svg">
						<G><Path fill="none" d="M0,0h24v24H0V0z" /></G>
						<G><Path d="M7.41,8.59L12,13.17l4.59-4.58L18,10l-6,6l-6-6L7.41,8.59z" /></G>
					</SVG>
					{ icon && <Dashicon icon={ icon } className="components-panel__icon" /> }
					{ label }
				</Button>
			</h2>
		</div>
	);
}

export default PanelButton;
