/**
 * ChooserModal
 *
 * Presents users with two options when creating a new chart:
 *   1. Classic Builder — opens the existing iframe-based wizard
 *   2. AI Chart Builder — opens the new D3-powered React wizard
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

function ChooserModal( { isOpen, onClassic, onAIBuilder, onClose } ) {
	const [ rememberChoice, setRememberChoice ] = useState( false );

	useEffect( () => {
		if ( isOpen ) {
			setRememberChoice( false );
		}
	}, [ isOpen ] );

	if ( ! isOpen ) {
		return null;
	}

	return (
		<div className="viz-chooser-overlay" role="dialog" aria-modal="true" aria-labelledby="viz-chooser-title">
			<div className="viz-chooser-modal">

				<button
					className="viz-chooser-close"
					onClick={ onClose }
					aria-label={ __( 'Close', 'visualizer' ) }
				>
					&#x2715;
				</button>

				<h2 id="viz-chooser-title" className="viz-chooser-title">
					{ __( 'Create a New Chart', 'visualizer' ) }
				</h2>

				<p className="viz-chooser-subtitle">
					{ __( 'Choose how you want to build your chart.', 'visualizer' ) }
				</p>

				<div className="viz-chooser-options">

					<button
						className="viz-chooser-option viz-chooser-option--ai"
						onClick={ () => onAIBuilder( rememberChoice ) }
					>
						<span className="viz-chooser-option__badge">
							{ __( 'New', 'visualizer' ) }
						</span>
						<span className="viz-chooser-option__icon viz-chooser-option__icon--ai">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" fill="currentColor"/>
							</svg>
						</span>
						<span className="viz-chooser-option__title">
							{ __( 'AI Chart Builder', 'visualizer' ) }
						</span>
						<span className="viz-chooser-option__desc">
							{ __( 'Describe your chart and let AI build it with D3.js.', 'visualizer' ) }
						</span>
					</button>

					<button
						className="viz-chooser-option"
						onClick={ () => onClassic( rememberChoice ) }
					>
						<span className="viz-chooser-option__icon viz-chooser-option__icon--classic">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<rect x="3" y="14" width="4" height="7" rx="1" fill="currentColor"/>
								<rect x="10" y="9" width="4" height="12" rx="1" fill="currentColor"/>
								<rect x="17" y="4" width="4" height="17" rx="1" fill="currentColor"/>
							</svg>
						</span>
						<span className="viz-chooser-option__title">
							{ __( 'Classic Builder', 'visualizer' ) }
						</span>
						<span className="viz-chooser-option__desc">
							{ __( 'Step-by-step wizard with all chart types and options.', 'visualizer' ) }
						</span>
					</button>

				</div>

				<div className="viz-chooser-remember">
					<label className="viz-chooser-remember__label">
						<input
							type="checkbox"
							checked={ rememberChoice }
							onChange={ ( event ) => setRememberChoice( event.target.checked ) }
						/>
						<span>{ __( 'Don\'t ask me again', 'visualizer' ) }</span>
					</label>
					<p className="viz-chooser-remember__help">
						{ __( 'We will open your selected builder by default next time.', 'visualizer' ) }
					</p>
				</div>

				<button className="viz-chooser-cancel" onClick={ onClose }>
					{ __( 'Cancel', 'visualizer' ) }
				</button>

			</div>
		</div>
	);
}

export default ChooserModal;
