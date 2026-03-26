/**
 * AI Builder — two-column layout matching layout.html design
 *
 * Left  : Step 1 (DataSource) + Step 2 (Describe)
 * Right : Live preview sidebar (560px)
 * Footer: Cancel + Generate / Publish actions
 */
import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Box, Flex, Spinner, Text } from '@chakra-ui/react';
import * as d3 from 'd3';
import * as topojson from 'topojson-client';
import CodeMirror from '@uiw/react-codemirror';
import { javascript } from '@codemirror/lang-javascript';
import { createChart, getChartNonce, fetchChart, saveChart, generateChart, pollStatus, uploadCsvString } from './api';
import DataSource from './DataSource';

/** Convert Visualizer series + data arrays to plain objects for D3. */
function toD3Values( series, data ) {
	return data.map( ( row ) => {
		const obj = {};
		series.forEach( ( col, i ) => { obj[ col.label ] = row[ i ]; } );
		return obj;
	} );
}

/** Execute D3 code in a container element. Returns an error message if it fails. */
function renderD3( code, container, series, data ) {
	if ( ! code || ! container ) return;
	const values = toD3Values( series, data );
	container.innerHTML = '';
	try {
		// eslint-disable-next-line no-new-func
		new Function( 'd3', 'topojson', 'container', 'data', code )( d3, topojson, container, values );
		return null;
	} catch ( err ) {
		const msg = `Chart error: ${ err.message }`;
		container.innerHTML = `<p style="color:#c00;padding:12px">${ msg }</p>`;
		return msg;
	}
}

// ── Palette (matches ChooserModal / WP-admin style) ───────────────────────────
const C = {
	bg:      '#f6f7f7',
	sidebar: '#f0f0f1',
	border:  '#dcdcde',
	dark:    '#1d2327',
	gray1:   '#646970',
	gray2:   '#8c8f94',
	gray3:   '#c3c4c7',
	dim:     '#f0f0f1',
	teal:    '#39c3d2',
};

const PROMPT_PRESETS = [
	__( 'Bar chart comparing totals by category, sorted descending', 'visualizer' ),
	__( 'Line chart showing trend over time with markers', 'visualizer' ),
	__( 'Stacked area chart to show part-to-whole over time', 'visualizer' ),
	__( 'Scatter plot showing correlation between two numeric fields', 'visualizer' ),
	__( 'Pie chart showing share by category with labels', 'visualizer' ),
];

// ── Helpers ───────────────────────────────────────────────────────────────────

/** Reconstruct a CSV string from Visualizer series/data arrays for display. */
function buildCsvFromData( series, data ) {
	if ( ! Array.isArray( series ) || ! series.length ) return '';
	const labels = series.map( ( s ) => s.label ).join( ',' );
	const types  = series.map( ( s ) => s.type || 'string' ).join( ',' );
	const rows   = ( data || [] ).map( ( row ) => row.join( ',' ) );
	return [ labels, types, ...rows ].join( '\n' );
}

// ── Step badge ────────────────────────────────────────────────────────────────
function StepBadge( { num, active } ) {
	return (
		<Box
			w="22px" h="22px" borderRadius="full" flexShrink={ 0 }
			bg={ active ? C.dark : C.dim }
			border={ active ? 'none' : `1px solid ${ C.border }` }
			display="flex" alignItems="center" justifyContent="center"
		>
			<Text
				fontSize="11px"
				fontWeight={ active ? '600' : '500' }
				color={ active ? 'white' : C.gray2 }
				lineHeight="1"
			>
				{ num }
			</Text>
		</Box>
	);
}

// ── Pill button (footer / misc) ───────────────────────────────────────────────
function PillBtn( { children, onClick, disabled, bg = C.dark, color = 'white', border, borderColor, cursor } ) {
	return (
		<Box
			as="button"
			onClick={ disabled ? undefined : onClick }
			disabled={ disabled }
			display="inline-flex" alignItems="center" gap="6px"
			px="20px" py="9px" borderRadius="9px"
			fontSize="13px" fontWeight="500"
			bg={ disabled ? ( bg === C.dark ? '#555' : bg ) : bg }
			color={ color } border={ border || 'none' }
			borderColor={ borderColor }
			cursor={ disabled ? 'not-allowed' : ( cursor || 'pointer' ) }
			opacity={ disabled ? 0.65 : 1 }
			transition="background 0.15s"
			_hover={ disabled ? {} : { filter: 'brightness(0.92)' } }
		>
			{ children }
		</Box>
	);
}

export default function AIBuilder( { onClose, initialChartId = null } ) {
	const [ chartId,     setChartId     ] = useState( null );
	const [ uploadNonce, setUploadNonce ] = useState( null );
	const [ initError,   setInitError   ] = useState( null );

	const [ series,         setSeries         ] = useState( [] );
	const [ data,           setData           ] = useState( [] );
	const [ previewSeries,  setPreviewSeries  ] = useState( [] );
	const [ previewData,    setPreviewData    ] = useState( [] );
	const [ dataLoaded,     setDataLoaded     ] = useState( false );
	const [ initialCsvText, setInitialCsvText ] = useState( '' );

	const [ prompt,   setPrompt   ] = useState( '' );
	const [ refImage, setRefImage ] = useState( null );

	const [ code,       setCode       ] = useState( null );
	const [ generating, setGenerating ] = useState( false );
	const [ genError,   setGenError   ] = useState( null );
	const [ renderError, setRenderError ] = useState( null );
	const [ editorError, setEditorError ] = useState( null );
	const [ dataIncompatible, setDataIncompatible ] = useState( null );
	const [ fixing,     setFixing     ] = useState( false );
	const [ editOpen,   setEditOpen   ] = useState( false );
	const [ draftCode,  setDraftCode  ] = useState( '' );

	const [ title,     setTitle     ] = useState( '' );
	const [ saving,    setSaving    ] = useState( false );
	const [ saveError, setSaveError ] = useState( null );
	const [ shortcode, setShortcode ] = useState( null );
	const [ copied,    setCopied    ] = useState( false );

	const previewRef           = useRef( null );
	const fullscreenRef        = useRef( null );
	const fullscreenOverlayRef = useRef( null );
	const refImageInputRef     = useRef( null );
	const [ fullScreen,  setFullScreen  ] = useState( false );
	const lastSeriesKeyRef = useRef( null );
	const fixAttemptedRef = useRef( false );
	const codeOriginRef = useRef( 'agent' );
	const isLocked = generating || fixing;
	const canGenerate = !! prompt?.trim() && ! generating && ! fixing;

	// Focus fullscreen overlay and enable Esc close.
	useEffect( () => {
		if ( ! fullScreen ) return;
		fullscreenOverlayRef.current?.focus();
		const onKey = ( e ) => {
			if ( e.key === 'Escape' ) setFullScreen( false );
		};
		window.addEventListener( 'keydown', onKey );
		return () => window.removeEventListener( 'keydown', onKey );
	}, [ fullScreen ] );

	// Close editor on Escape.
	useEffect( () => {
		if ( ! editOpen ) return;
		const onKey = ( e ) => {
			if ( e.key === 'Escape' ) closeEditor();
		};
		window.addEventListener( 'keydown', onKey );
		return () => window.removeEventListener( 'keydown', onKey );
	}, [ editOpen ] );

	// Prevent background page scroll while editor is open.
	useEffect( () => {
		if ( ! editOpen ) return;
		const { body } = document;
		const prevOverflow = body.style.overflow;
		body.style.overflow = 'hidden';
		return () => { body.style.overflow = prevOverflow; };
	}, [ editOpen ] );

	useEffect( () => {
		if ( initialChartId ) {
			// Edit mode: reuse the existing chart and fetch its upload nonce.
			setChartId( parseInt( initialChartId, 10 ) );
			Promise.all( [
				getChartNonce( initialChartId ),
				fetchChart( initialChartId ),
			] )
				.then( ( [ nonceRes, chartRes ] ) => {
					setUploadNonce( nonceRes.upload_nonce );

					const applyExisting = ( existing ) => {
						if ( existing.title )  setTitle( existing.title );
						if ( existing.series ) setSeries( existing.series );
						if ( existing.data )   { setData( existing.data ); setDataLoaded( true ); }
						if ( existing.series ) setPreviewSeries( existing.series );
						if ( existing.data )   setPreviewData( existing.data );
						if ( existing.series && existing.data ) {
							const csv = buildCsvFromData( existing.series, existing.data );
							if ( csv ) setInitialCsvText( csv );
						}
						if ( existing.code ) {
							setCode( existing.code );
						}
					};

					// Prefer localized chart if present; fall back to fetch response.
					const chartKey = 'visualizer-' + initialChartId;
					const existing = window.visualizer?.charts?.[ chartKey ];
					if ( existing ) {
						applyExisting( existing );
					} else if ( chartRes ) {
						applyExisting( chartRes );
					}
				} )
				.catch( ( e ) => setInitError( e.message ) );
		} else {
			createChart()
				.then( ( res ) => { setChartId( res.chart_id ); setUploadNonce( res.upload_nonce ); } )
				.catch( ( e ) => setInitError( e.message ) );
		}
	}, [ initialChartId ] );

	// Re-render preview whenever code or source data changes.
	useEffect( () => {
		const activeCode = editOpen ? draftCode : code;
		if ( ! activeCode || ! previewRef.current ) return;
		if ( ! Array.isArray( previewSeries ) || ! Array.isArray( previewData ) || previewSeries.length === 0 ) {
			previewRef.current.innerHTML = '';
			return;
		}
		const err = renderD3( activeCode, previewRef.current, previewSeries, previewData );
		if ( editOpen ) {
			setEditorError( err || null );
			return;
		}
		if ( err ) {
			setRenderError( err );
			if ( codeOriginRef.current === 'agent' && ! fixing && ! fixAttemptedRef.current ) {
				fixAttemptedRef.current = true;
				autoFix( err );
			}
		} else {
			setRenderError( null );
		}
	}, [ code, draftCode, editOpen, previewSeries, previewData ] );

	// Fullscreen render
	useEffect( () => {
		if ( ! fullScreen || ! code || ! fullscreenRef.current ) return;
		if ( ! Array.isArray( series ) || ! Array.isArray( data ) || series.length === 0 ) {
			fullscreenRef.current.innerHTML = '';
			return;
		}

		renderD3( code, fullscreenRef.current, series, data );

		const onResize = () => renderD3( code, fullscreenRef.current, series, data );
		window.addEventListener( 'resize', onResize );

		return () => {
			window.removeEventListener( 'resize', onResize );
			if ( fullscreenRef.current ) fullscreenRef.current.innerHTML = '';
		};
	}, [ fullScreen, code, series, data ] );

	function handleDataReady( newSeries, newData ) {
		const nextKey = JSON.stringify( newSeries || [] );
		const seriesChanged = lastSeriesKeyRef.current && lastSeriesKeyRef.current !== nextKey;
		setSeries( newSeries );
		setData( newData );
		setDataLoaded( true );

		if ( seriesChanged ) {
			setDataIncompatible( __( 'The column headers changed. Please regenerate to update the chart.', 'visualizer' ) );
			setRenderError( null );
		} else if ( code ) {
			const testContainer = document.createElement( 'div' );
			const err = renderD3( code, testContainer, newSeries, newData );
			if ( err ) {
				setDataIncompatible( __( 'These data changes do not match the current chart. Please regenerate to update the preview.', 'visualizer' ) );
				setRenderError( null );
			} else {
				setPreviewSeries( newSeries );
				setPreviewData( newData );
				setDataIncompatible( null );
			}
		} else {
			setPreviewSeries( newSeries );
			setPreviewData( newData );
			setDataIncompatible( null );
		}

		setGenError( null );
		lastSeriesKeyRef.current = nextKey;
	}
	// Track series signature for edit-mode preload.
	useEffect( () => {
		if ( series && series.length ) {
			lastSeriesKeyRef.current = JSON.stringify( series );
		}
	}, [ series ] );

	async function handleGenerate() {
		if ( ! prompt?.trim() ) return;
		setGenerating( true );
		setGenError( null );
		setRenderError( null );
		setDataIncompatible( null );
		fixAttemptedRef.current = false;
		try {
			// 1. Convert reference image to base64 if provided.
			let refImageBase64 = null;
			let refImageMime   = null;
			if ( refImage ) {
				refImageBase64 = await new Promise( ( resolve, reject ) => {
					const reader = new FileReader();
					reader.onload  = () => resolve( reader.result.split( ',' )[ 1 ] );
					reader.onerror = () => reject( new Error( __( 'Failed to read image file.', 'visualizer' ) ) );
					reader.readAsDataURL( refImage );
				} );
				refImageMime = refImage.type || 'image/jpeg';
			}

			// 2. Kick off the async generation job.
			// Pass the existing code when refining so the agent knows the current chart state.
			const { workflow_id: workflowId } = await generateChart( chartId, prompt, series, data, code || null, refImageBase64, refImageMime );

			// 3. Poll until complete or failed (max ~3 min, 2 s interval)
			const MAX_POLLS  = 90;
			const POLL_MS    = 2000;
			let   polls      = 0;

			while ( polls < MAX_POLLS ) {
				await new Promise( ( r ) => setTimeout( r, POLL_MS ) );
				polls++;

				const result = await pollStatus( workflowId );
				const status = result.status ?? result.workflowStatus;

				if ( status === 'completed' ) {
					const newCode = result.output?.code;
					const dataCsv = result.output?.data_csv || result.output?.csv || null;
					if ( ! newCode ) throw new Error( __( 'Generation completed but no code was returned.', 'visualizer' ) );
					setCode( newCode );
					codeOriginRef.current = 'agent';
					if ( dataCsv && uploadNonce ) {
						try {
							const trimmedCsv = String( dataCsv ).trim();
							const parsed = await uploadCsvString( chartId, uploadNonce, trimmedCsv );
							setInitialCsvText( trimmedCsv );
							handleDataReady( parsed.series, parsed.data );
						} catch ( e ) {
							setPreviewSeries( series );
							setPreviewData( data );
							setDataIncompatible( null );
							setGenError( __( 'Chart generated, but the extracted data could not be parsed. Please verify the data or regenerate.', 'visualizer' ) );
						}
					} else {
						setPreviewSeries( series );
						setPreviewData( data );
						setDataIncompatible( null );
					}
					return;
				}

				if ( status === 'failed' ) {
					throw new Error( __( 'Chart generation failed. Please try again.', 'visualizer' ) );
				}
				// status === 'queued' | 'running' — keep polling
			}

			throw new Error( __( 'Generation timed out. Please try again.', 'visualizer' ) );
		} catch ( e ) {
			setGenError( e.message );
		} finally {
			setGenerating( false );
		}
	}

	function buildFixPrompt( errorMessage ) {
		const base = [
			'The D3 code you returned threw a runtime/syntax error when executed.',
			`Error: "${ errorMessage }"`,
			'Please fix the D3 code to resolve the error.',
			'Keep the chart intent the same and only return corrected D3 code.',
		].join( '\n' );
		if ( prompt?.trim() ) {
			return `${ base }\n\nOriginal user request:\n${ prompt.trim() }`;
		}
		return base;
	}

	async function autoFix( errorMessage ) {
		if ( ! chartId || ! dataLoaded || fixing ) return;
		setFixing( true );
		setGenError( null );
		try {
			const fixPrompt = buildFixPrompt( errorMessage );
			const { workflow_id: workflowId } = await generateChart( chartId, fixPrompt, series, data, code || null, null, null );
			const MAX_POLLS  = 60;
			const POLL_MS    = 2000;
			let   polls      = 0;

			while ( polls < MAX_POLLS ) {
				await new Promise( ( r ) => setTimeout( r, POLL_MS ) );
				polls++;

				const result = await pollStatus( workflowId );
				const status = result.status ?? result.workflowStatus;

				if ( status === 'completed' ) {
					const newCode = result.output?.code;
					if ( ! newCode ) throw new Error( __( 'Fix completed but no code was returned.', 'visualizer' ) );
					setCode( newCode );
					codeOriginRef.current = 'agent';
					setRenderError( null );
					return;
				}

				if ( status === 'failed' ) {
					throw new Error( __( 'Fix attempt failed. Please try again.', 'visualizer' ) );
				}
			}

			throw new Error( __( 'Fix attempt timed out. Please try again.', 'visualizer' ) );
		} catch ( e ) {
			setGenError( e.message );
		} finally {
			setFixing( false );
		}
	}

	async function handleFixClick() {
		if ( ! renderError ) return;
		await autoFix( renderError );
	}

	async function handleSave() {
		if ( ! code ) return;
		setSaving( true );
		setSaveError( null );
		try {
			const res = await saveChart( chartId, title.trim() || __( 'AI Chart', 'visualizer' ), code );
			setShortcode( res.shortcode );
		} catch ( e ) {
			setSaveError( e.message );
		} finally {
			setSaving( false );
		}
	}

	function handleCopy() {
		navigator.clipboard?.writeText( shortcode ).catch( () => {} );
		setCopied( true );
		setTimeout( () => setCopied( false ), 2000 );
	}

	function appendPreset( text ) {
		const next = prompt ? `${ prompt.trim() }\n${ text }` : text;
		setPrompt( next );
	}

	function openEditor() {
		setDraftCode( code || '' );
		setEditorError( null );
		setEditOpen( true );
	}

	function closeEditor() {
		setEditOpen( false );
		setEditorError( null );
	}

	function saveEditor() {
		if ( ! draftCode ) return;
		if ( dataLoaded && Array.isArray( series ) && Array.isArray( data ) && series.length ) {
			const testContainer = document.createElement( 'div' );
			const err = renderD3( draftCode, testContainer, series, data );
			if ( err ) {
				setEditorError( err );
				return;
			}
		}
		setCode( draftCode );
		codeOriginRef.current = 'manual';
		setPreviewSeries( series );
		setPreviewData( data );
		setDataIncompatible( null );
		setEditorError( null );
		setRenderError( null );
		fixAttemptedRef.current = false;
		setEditOpen( false );
	}

	// ── Init / error states ───────────────────────────────────────────────────

	if ( initError ) {
		return (
			<Flex flex="1" align="center" justify="center" p="10">
				<Box px="4" py="3" bg="#fef2f2" borderLeft="3px solid #cc1818" borderRadius="4px">
					<Text fontSize="sm" color="#cc1818">{ initError }</Text>
				</Box>
			</Flex>
		);
	}

	if ( ! chartId ) {
		return (
			<Flex flex="1" align="center" justify="center" gap="3" p="10" color={ C.gray2 } fontSize="13px">
				<Spinner size="sm" />
				{ __( 'Initializing…', 'visualizer' ) }
			</Flex>
		);
	}

	// ── Main layout ───────────────────────────────────────────────────────────

	return (
		<>
			{ /* ════ Body ═══════════════════════════════════════════════════════ */ }
			<Flex flex="1" overflow="hidden" minH="0">

				{ /* ── Left panel ─────────────────────────────────────────────── */ }
				<Box
					flex="1" overflowY={ editOpen ? 'hidden' : 'auto' }
					px="6" py="5" bg={ C.bg }
					display="flex" flexDirection="column" gap="5"
					borderRight="1px solid" borderColor={ C.border }
				>
					{ editOpen ? (
						<>
							<Flex align="center" justify="space-between">
								<Text fontSize="13px" fontWeight="600" color={ C.dark } lineHeight="1">
									{ __( 'Edit D3 code', 'visualizer' ) }
								</Text>
								<PillBtn onClick={ closeEditor } bg="white" color={ C.gray1 } border="1.5px solid" borderColor={ C.border }>
									{ __( 'Back', 'visualizer' ) }
								</PillBtn>
							</Flex>
							<Box
								flex="1"
								h="100%"
								minH="360px"
								bg="white"
								border="1.5px solid"
								borderColor={ C.border }
								borderRadius="10px"
								overflow="auto"
								onWheel={ ( e ) => e.stopPropagation() }
								sx={ {
									'.cm-editor': { height: '100%' },
									'.cm-scroller': { height: '100%', overflow: 'auto' },
								} }
							>
								<CodeMirror
									value={ draftCode }
									height="100%"
									maxHeight="100%"
									autoFocus
									extensions={ [ javascript( { jsx: false } ) ] }
									onChange={ ( value ) => setDraftCode( value ) }
									editable={ ! isLocked }
									basicSetup={ { lineNumbers: true } }
								/>
							</Box>
							{ editorError && (
								<Box px="3" py="2" bg="#fff7ed" borderLeft="3px solid #f59e0b" borderRadius="4px">
									<Text fontSize="12px" color="#b45309">{ editorError }</Text>
								</Box>
							) }
							<Text fontSize="11px" color={ C.gray2 }>
								{ __( 'Need a refresher? Read the D3 documentation to understand selections, scales, and shapes:', 'visualizer' ) }{' '}
								<a href="https://d3js.org/getting-started" target="_blank" rel="noopener noreferrer">
									{ __( 'd3js.org/getting-started', 'visualizer' ) }
								</a>
							</Text>
							<Flex align="center" justify="flex-end" gap="2">
								<PillBtn onClick={ closeEditor } bg="white" color={ C.gray1 } border="1.5px solid" borderColor={ C.border }>
									{ __( 'Cancel', 'visualizer' ) }
								</PillBtn>
								<PillBtn onClick={ saveEditor } disabled={ isLocked || ! draftCode || !! editorError } bg={ C.dark }>
									{ __( 'Save', 'visualizer' ) }
								</PillBtn>
							</Flex>
						</>
					) : (
						<>
							{ /* Step 1: Data source */ }
							<Flex align="center" gap="2.5">
								<StepBadge num="1" active={ true } />
								<Text fontSize="13px" fontWeight="600" color={ C.dark } lineHeight="1">
									{ __( 'Data source', 'visualizer' ) }
								</Text>
							</Flex>

					<DataSource
						chartId={ chartId }
						uploadNonce={ uploadNonce }
						onDataReady={ handleDataReady }
						dataLoaded={ dataLoaded }
						initialCsvText={ initialCsvText }
						disabled={ isLocked }
						dataWarning={ dataIncompatible }
						onClearWarning={ () => setDataIncompatible( null ) }
					/>
							{ /* Divider */ }
							<Box borderTop="1px solid" borderColor={ C.border } />

							{ /* Step 2: Describe */ }
							<Box display="flex" flexDirection="column" gap="2.5">
								<Flex align="center" gap="2.5">
									<StepBadge num="2" active={ true } />
									<Text fontSize="13px" fontWeight="600" color={ C.dark } lineHeight="1">
										{ __( 'Describe your chart', 'visualizer' ) }
									</Text>
								</Flex>

								{ /* Describe textarea box */ }
								<Box
									border="1.5px solid" borderColor={ C.border }
									borderRadius="10px" overflow="hidden"
									bg="white"
								>
									<Box
										as="textarea"
										w="100%" minH="68px"
										border="none" outline="none"
										bg="transparent"
										p="10px 12px"
										fontSize="13px" fontFamily="inherit"
										resize="vertical"
										color={ C.dark }
										placeholder={ __( 'e.g. Create a grouped bar chart comparing revenue and profit by quarter', 'visualizer' ) }
										value={ prompt }
										onChange={ isLocked ? undefined : ( e ) => setPrompt( e.target.value ) }
										onKeyDown={ isLocked ? undefined : ( e ) => {
											if ( e.key === 'Enter' && ! e.shiftKey ) {
												e.preventDefault();
												if ( canGenerate ) handleGenerate();
											}
										} }
										disabled={ isLocked }
										display="block"
										sx={ { '&::placeholder': { color: C.gray3 } } }
									/>
									<Flex
										align="center" justify="space-between"
										px="3" py="1.5"
										borderTop="1.5px solid" borderColor={ C.border }
										bg="white" gap="2"
									>
										{ /* Reference image button */ }
										<Box
											as="button"
											display="flex" alignItems="center" gap="5px"
											px="10px" py="5px" borderRadius="7px"
											fontSize="11px" fontWeight="500"
											bg="white" border="1.5px solid" borderColor={ C.border }
											cursor={ isLocked ? 'not-allowed' : 'pointer' } color={ C.gray1 }
											opacity={ isLocked ? 0.6 : 1 }
											onClick={ isLocked ? undefined : () => refImageInputRef.current?.click() }
										>
											<svg width="11" height="11" viewBox="0 0 11 11" fill="none">
												<path d="M1.5 5.5h8M5.5 1.5v8" stroke="currentColor" strokeWidth="1.4" strokeLinecap="round" />
											</svg>
											{ refImage ? refImage.name : __( 'Reference image', 'visualizer' ) }
										</Box>

										{ /* Generate / Regenerate button */ }
										{ ! shortcode && (
											<Box
												as="button"
												display="flex" alignItems="center" gap="5px"
												px="10px" py="5px" borderRadius="7px"
												fontSize="11px" fontWeight="500"
												bg={ code ? 'white' : ( canGenerate ? C.teal : C.dim ) }
												color={ code ? C.gray1 : ( canGenerate ? 'white' : C.gray2 ) }
												border={ code ? `1.5px solid ${ C.border }` : 'none' }
												cursor={ canGenerate ? 'pointer' : 'not-allowed' }
												opacity={ canGenerate ? 1 : 0.65 }
												onClick={ canGenerate ? handleGenerate : undefined }
												transition="background 0.15s"
												_hover={ canGenerate ? { filter: 'brightness(0.92)' } : {} }
											>
												{ generating ? (
													<><Spinner size="xs" mr="1" color={ code ? C.gray1 : 'white' } />{ __( 'Generating…', 'visualizer' ) }</>
												) : fixing ? (
													<><Spinner size="xs" mr="1" color={ code ? C.gray1 : 'white' } />{ __( 'Fixing…', 'visualizer' ) }</>
												) : code ? (
													__( '↺ Regenerate', 'visualizer' )
												) : (
													<>
														<svg width="11" height="11" viewBox="0 0 13 13" fill="none">
															<circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" strokeWidth="1.2" />
															<path d="M4.5 6.5l1.5 1.5 3-3" stroke="currentColor" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round" />
														</svg>
														{ __( 'Generate', 'visualizer' ) }
													</>
												) }
											</Box>
										) }
									</Flex>
								</Box>
								<Text fontSize="11px" color={ C.gray2 }>
									{ __( 'Press Enter to generate. Shift + Enter for a new line.', 'visualizer' ) }
								</Text>

								<input
									ref={ refImageInputRef }
									type="file" accept="image/*"
									style={ { display: 'none' } }
									disabled={ isLocked }
									onChange={ ( e ) => setRefImage( e.target.files[ 0 ] || null ) }
								/>

								{ genError && (
									<Box px="3" py="2" bg="#fef2f2" borderLeft="3px solid #cc1818" borderRadius="4px">
										<Text fontSize="12px" color="#cc1818">{ genError }</Text>
									</Box>
								) }
								{ /* Prompt presets */ }
								<Box>
									<Text fontSize="11px" fontWeight="600" color={ C.gray1 } letterSpacing="0.03em" textTransform="uppercase" mb="2">
										{ __( 'Presets', 'visualizer' ) }
									</Text>
									<Flex wrap="wrap" gap="2">
										{ PROMPT_PRESETS.map( ( preset ) => (
											<Box
												key={ preset }
												as="button"
												px="10px" py="6px"
												borderRadius="999px"
												fontSize="11px"
												fontWeight="500"
												bg="white"
												border="1px solid"
												borderColor={ C.border }
												color={ C.gray1 }
												cursor={ isLocked ? 'not-allowed' : 'pointer' }
												opacity={ isLocked ? 0.6 : 1 }
												onClick={ isLocked ? undefined : () => appendPreset( preset ) }
											>
												{ preset }
											</Box>
										) ) }
									</Flex>
								</Box>
								{ renderError && (
									<Box px="3" py="2" bg="#fff7ed" borderLeft="3px solid #f59e0b" borderRadius="4px">
										<Text fontSize="12px" color="#b45309">{ renderError }</Text>
										{ fixAttemptedRef.current && ! fixing && (
											<Box mt="2">
												<PillBtn onClick={ handleFixClick } bg="white" color={ C.dark } border="1.5px solid" borderColor={ C.border }>
													{ __( 'Fix', 'visualizer' ) }
												</PillBtn>
											</Box>
										) }
									</Box>
								) }
							</Box>
						</>
					) }
				</Box>

				{ /* ── Right: rebuilt preview panel ─────────────────────────────── */ }
				<Box w="560px" flexShrink={ 0 } display="flex" flexDirection="column" bg="#f7f7f8" borderLeft="1px solid #e2e3e6">
					<Flex align="center" justify="space-between" px="5" py="4" borderBottom="1px solid #e2e3e6">
						<Box>
							<Text fontSize="12px" fontWeight="700" color={ C.dark } letterSpacing="0.02em">
								{ __( 'Preview', 'visualizer' ) }
							</Text>
							<Text fontSize="12px" color={ C.gray1 } lineHeight="1.4">
								{ __( 'Live rendering based on your data and prompt', 'visualizer' ) }
							</Text>
						</Box>
						{ ! editOpen && (
							<Flex align="center" gap="2">
								<Box
									as="button"
									fontSize="12px"
									fontWeight="600"
									px="10px"
									py="6px"
									borderRadius="8px"
									bg="white"
									border="1px solid #dcdcde"
									color={ C.gray1 }
									cursor={ isLocked ? 'not-allowed' : 'pointer' }
									opacity={ isLocked ? 0.6 : 1 }
									onClick={ isLocked ? undefined : openEditor }
								>
									{ __( 'Edit code', 'visualizer' ) }
								</Box>
								{ code && (
									<Box
										as="button"
										fontSize="12px"
										fontWeight="600"
										px="10px"
										py="6px"
										borderRadius="8px"
										bg="white"
										border="1px solid #dcdcde"
										color={ C.gray1 }
										cursor={ isLocked ? 'not-allowed' : 'pointer' }
										opacity={ isLocked ? 0.6 : 1 }
										onClick={ isLocked ? undefined : () => setFullScreen( true ) }
									>
										{ __( 'Full screen', 'visualizer' ) }
									</Box>
								) }
							</Flex>
						) }
					</Flex>

					<Box flex="1" p="5" display="flex" flexDirection="column" minH="0" position="relative">
						<Box
							ref={ previewRef }
							flex="1"
							minH="320px"
							overflow="visible"
							position="relative"
							display={ code ? 'flex' : 'none' }
							alignItems="center"
							justifyContent="center"
							sx={ {
								'> svg': { width: '100% !important', height: '100% !important', display: 'block' },
							} }
						>
						</Box>
						{ ! code && (
							<Box
								position="absolute"
								inset="0"
								display="flex"
								alignItems="center"
								justifyContent="center"
								p="6"
							>
								<Box textAlign="center">
									<Text fontSize="13px" fontWeight="600" color={ C.dark } mb="1">
										{ __( 'No chart yet', 'visualizer' ) }
									</Text>
									<Text fontSize="12px" color={ C.gray2 } lineHeight="1.5">
										{ __( 'Load data and describe your chart to get started', 'visualizer' ) }
									</Text>
								</Box>
							</Box>
						) }
					</Box>

					{ shortcode && (
						<Box px="5" py="4" borderTop="1px solid #e2e3e6" bg="#f7f7f8">
							<Text fontSize="11px" fontWeight="700" color={ C.gray1 } textTransform="uppercase" letterSpacing="0.06em" mb="2">
								{ __( 'Shortcode', 'visualizer' ) }
							</Text>
							<Flex align="center" gap="2">
								<Box
									as="code"
									flex="1"
									px="3"
									py="2"
									fontSize="11px"
									fontFamily="mono"
									bg="white"
									border="1px solid #e2e3e6"
									borderRadius="8px"
									overflow="hidden"
									textOverflow="ellipsis"
									whiteSpace="nowrap"
								>
									{ shortcode }
								</Box>
								<Box
									as="button"
									px="10px"
									py="6px"
									borderRadius="8px"
									fontSize="12px"
									fontWeight="600"
									bg="white"
									border="1px solid #dcdcde"
									color={ C.gray1 }
									cursor={ isLocked ? 'not-allowed' : 'pointer' }
									opacity={ isLocked ? 0.6 : 1 }
									onClick={ isLocked ? undefined : handleCopy }
								>
									{ copied ? __( 'Copied!', 'visualizer' ) : __( 'Copy', 'visualizer' ) }
								</Box>
							</Flex>
						</Box>
					) }
				</Box>
			</Flex>

			{ /* ════ Footer ══════════════════════════════════════════════════════ */ }
			<Flex
				align="center" justify="flex-end" gap="2"
				px="6" py="3.5"
				borderTop="1px solid" borderColor={ C.border }
				bg={ C.bg } flexShrink={ 0 }
			>
				{ saveError && (
					<Text fontSize="12px" color="#cc1818" mr="auto">{ saveError }</Text>
				) }

				<PillBtn onClick={ onClose } bg="white" color={ C.gray1 } border="1.5px solid" borderColor={ C.border }>
					{ __( 'Cancel', 'visualizer' ) }
				</PillBtn>

				{ code && ! shortcode && ! editOpen && (
					<>
						<Box px="4" py="1.5" mr="1">
							<Box
								as="input"
								type="text"
								placeholder={ __( 'Chart title…', 'visualizer' ) }
								value={ title }
								onChange={ isLocked ? undefined : ( e ) => setTitle( e.target.value ) }
								disabled={ isLocked }
								px="10px" py="7px" borderRadius="8px"
								border={ `1.5px solid ${ C.border }` }
								fontSize="12px" color={ C.dark }
								bg="white" outline="none"
								sx={ { '&::placeholder': { color: C.gray3 }, '&:focus': { borderColor: C.dark } } }
							/>
						</Box>
						<PillBtn onClick={ handleSave } disabled={ saving || isLocked } bg={ C.teal }>
							{ saving ? __( 'Publishing…', 'visualizer' ) : __( 'Publish chart', 'visualizer' ) }
						</PillBtn>
					</>
				) }

				{ shortcode && (
					<PillBtn onClick={ () => { onClose(); window.location.reload(); } } disabled={ isLocked } bg={ C.dark }>
						{ __( 'Done', 'visualizer' ) }
					</PillBtn>
				) }
			</Flex>

			{ fullScreen && (
				<div
					className="viz-ai-fullscreen-overlay"
					role="dialog"
					aria-modal="true"
					onClick={ ( e ) => { if ( e.target === e.currentTarget ) setFullScreen( false ); } }
					tabIndex={ -1 }
					ref={ fullscreenOverlayRef }
				>
					<div className="viz-ai-fullscreen-modal">
						<div className="viz-ai-fullscreen-header">
							<span>{ __( 'Full screen preview', 'visualizer' ) }</span>
							<button className="viz-ai-fullscreen-close" onClick={ () => setFullScreen( false ) } aria-label={ __( 'Close', 'visualizer' ) }>
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none">
									<path d="M12 4L4 12M4 4l8 8" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
								</svg>
								<span>{ __( 'Close', 'visualizer' ) }</span>
							</button>
						</div>
						<div className="viz-ai-fullscreen-body" ref={ fullscreenRef } />
					</div>
				</div>
			) }

			{ /* Editor now lives in the left panel, no modal */ }
		</>
	);
}
