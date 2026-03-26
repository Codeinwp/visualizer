/**
 * Chart Builder — Entry point
 *
 * Mounts the ChooserModal + AI Builder wizard into #viz-chart-builder-root
 * and registers window.vizOpenChartChooser for library.js to call.
 */
import { render, createElement, useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ChakraProvider, defaultSystem } from '@chakra-ui/react';
import ChooserModal from './ChooserModal';
import AIBuilder    from './AIBuilder/index';
import './style.scss';

const CHOOSER_STORAGE_KEY = 'viz_chart_builder_default';

function getStoredBuilderChoice() {
	try {
		return window.localStorage.getItem( CHOOSER_STORAGE_KEY );
	} catch ( err ) {
		return null;
	}
}

function setStoredBuilderChoice( choice ) {
	try {
		window.localStorage.setItem( CHOOSER_STORAGE_KEY, choice );
	} catch ( err ) {
		// ignore storage failures
	}
}

function ChartBuilderApp() {
	// mode: 'hidden' | 'chooser' | 'ai-builder'
	const [ mode,            setMode            ] = useState( 'hidden' );
	const [ classicCallback, setClassicCallback ] = useState( null );
	const [ editChartId,     setEditChartId     ] = useState( null );

	useEffect( () => {
		window.vizOpenChartChooser = ( cb ) => {
			setEditChartId( null );
			setClassicCallback( () => cb );
			const storedChoice = getStoredBuilderChoice();
			if ( storedChoice === 'classic' ) {
				cb();
				return;
			}
			if ( storedChoice === 'ai' ) {
				setMode( 'ai-builder' );
				return;
			}
			setMode( 'chooser' );
		};
		window.vizOpenAIBuilderEdit = ( chartId ) => {
			setEditChartId( String( chartId ) );
			setMode( 'ai-builder' );
		};
		window.vizOpenAIBuilderNew = () => {
			setEditChartId( null );
			setMode( 'ai-builder' );
		};
		return () => {
			delete window.vizOpenChartChooser;
			delete window.vizOpenAIBuilderEdit;
			delete window.vizOpenAIBuilderNew;
		};
	}, [] );

	function handleClassic( rememberChoice = false ) {
		if ( rememberChoice ) {
			setStoredBuilderChoice( 'classic' );
		}
		setMode( 'hidden' );
		if ( typeof classicCallback === 'function' ) classicCallback();
	}
	function handleAIBuilder( rememberChoice = false ) {
		if ( rememberChoice ) {
			setStoredBuilderChoice( 'ai' );
		}
		setMode( 'ai-builder' );
	}
	function handleClose()     { setMode( 'hidden' ); setEditChartId( null ); }

	return (
		<ChakraProvider value={ defaultSystem }>
			<ChooserModal
				isOpen={ mode === 'chooser' }
				onClassic={ handleClassic }
				onAIBuilder={ handleAIBuilder }
				onClose={ handleClose }
			/>

			{ mode === 'ai-builder' && (
				<div className="viz-ai-builder-overlay" role="dialog" aria-modal="true">
					<div className="viz-ai-builder-modal">

						{ /* ── Header ── */ }
						<div className="viz-ai-builder-header">
							<div style={ { display: 'flex', alignItems: 'center', gap: 10 } }>
								<div className="viz-ai-builder-header__logo">
									<svg width="15" height="15" viewBox="0 0 15 15" fill="none">
										<rect x="1" y="7" width="3" height="6" rx="1" fill="white" fillOpacity=".5" />
										<rect x="6" y="4" width="3" height="9" rx="1" fill="white" fillOpacity=".75" />
										<rect x="11" y="1" width="3" height="12" rx="1" fill="white" />
									</svg>
								</div>
								<p style={ { fontSize: 14, fontWeight: 600, color: '#1d2327', margin: 0 } }>
									{ __( 'Visualizer', 'visualizer' ) }
								</p>
							</div>
							<button className="viz-ai-builder-close" onClick={ handleClose } aria-label="Close">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
									<path d="M12 4L4 12M4 4l8 8" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
								</svg>
							</button>
						</div>

						<AIBuilder onClose={ handleClose } initialChartId={ editChartId } />
					</div>
				</div>
			) }
		</ChakraProvider>
	);
}

const mountPoint = document.getElementById( 'viz-chart-builder-root' );
if ( mountPoint ) {
	render( createElement( ChartBuilderApp ), mountPoint );
}
