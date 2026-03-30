/**
 * DataSource — Manual | File | URL (CSV/XLSX only)
 */
import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Box, Flex, Stack, Text } from '@chakra-ui/react';
import { HotTable } from '@handsontable/react';
import { uploadCsvString, uploadFile, uploadFileUrl } from './api';

// ── Warm neutral palette ──────────────────────────────────────────────────────
const C = {
	bg:      '#f6f7f7',
	sidebar: '#f0f0f1',
	border:  '#dcdcde',
	dark:    '#1d2327',
	gray1:   '#646970',
	gray2:   '#8c8f94',
	gray3:   '#c3c4c7',
	dim:     '#f0f0f1',
	hover:   '#f6f7f7',
};

// ── Field atoms ───────────────────────────────────────────────────────────────

const FieldHint = ( { children } ) => (
	<Text fontSize="11px" color={ C.gray2 } mt="1" lineHeight="1.5">{ children }</Text>
);

const FieldLabel = ( { children } ) => (
	<Text fontSize="11px" fontWeight="500" color={ C.gray1 } letterSpacing="0.03em" textTransform="uppercase" mb="1">
		{ children }
	</Text>
);

const inputSx = {
	'&::placeholder': { color: '#C4C2BC' },
	'&:focus':        { borderColor: '#1C1C1E', boxShadow: '0 0 0 3px rgba(28,28,30,0.07)', outline: 'none' },
};

const FieldInput = ( props ) => (
	<Box
		as="input"
		display="block" w="100%"
		px="11px" py="9px"
		borderRadius="9px" border="1.5px solid" borderColor={ C.border }
		bg="white" color={ C.dark } fontSize="13px" fontFamily="inherit"
		sx={ inputSx }
		{ ...props }
	/>
);

// ── Source icons ──────────────────────────────────────────────────────────────

function IconManual( { active } ) {
	const c = active ? '#fff' : C.gray1;
	return (
		<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
			<rect x="1" y="1" width="12" height="3" rx="1" fill={ c } opacity=".9" />
			<rect x="1" y="6" width="12" height="2" rx="1" fill={ c } opacity=".6" />
			<rect x="1" y="10" width="7" height="2" rx="1" fill={ c } opacity=".35" />
		</svg>
	);
}

function IconSheet( { active } ) {
	const c = active ? '#fff' : C.gray1;
	return (
		<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
			<rect x="1" y="2" width="12" height="10" rx="1.5" stroke={ c } strokeWidth="1.2" />
			<path d="M1 5h12M1 8h12M5 2v10M9 2v10" stroke={ c } strokeWidth="1" opacity=".7" />
		</svg>
	);
}

function IconFile( { active } ) {
	const c = active ? '#fff' : C.gray1;
	return (
		<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
			<path d="M3 2a1 1 0 0 1 1-1h5l3 3v8a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2z" stroke={ c } strokeWidth="1.2" />
			<path d="M8 1v3h3" stroke={ c } strokeWidth="1.2" />
			<path d="M5 8h4M5 10.5h2.5" stroke={ c } strokeWidth="1" strokeLinecap="round" />
		</svg>
	);
}

function IconUrl( { active } ) {
	const c = active ? '#fff' : C.gray1;
	return (
		<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
			<path d="M5.5 8.5a3.5 3.5 0 0 0 4.95 0l1.5-1.5a3.5 3.5 0 0 0-4.95-4.95L6.07 3" stroke={ c } strokeWidth="1.2" strokeLinecap="round" />
			<path d="M8.5 5.5a3.5 3.5 0 0 0-4.95 0L2.05 7a3.5 3.5 0 0 0 4.95 4.95L7.93 11" stroke={ c } strokeWidth="1.2" strokeLinecap="round" />
		</svg>
	);
}

const SOURCES = [
	{ id: 'manual',   Icon: IconManual, label: __( 'Manual',  'visualizer' ) },
	{ id: 'sheet',    Icon: IconSheet,  label: __( 'Spreadsheet', 'visualizer' ) },
	{ id: 'csv_file', Icon: IconFile,   label: __( 'File',    'visualizer' ) },
	{ id: 'url',      Icon: IconUrl,    label: __( 'URL',     'visualizer' ) },
];

const SAMPLE_CSV      = `Task,Hours per Day\nstring,number\nWork,11\nEat,2\nCommute,2\nWatch TV,2\nSleep,7`;
const PLACEHOLDER_CSV = `Month,Revenue,Costs\nstring,number,number\nJan,45000,32000\nFeb,52000,38000`;

export default function DataSource( { chartId, uploadNonce, onDataReady, dataLoaded, initialCsvText = '', disabled = false, dataWarning = null, onClearWarning = null } ) {
	const isPro = !! window?.vizAIBuilder?.isPro;
	const [ source,  setSource  ] = useState( 'manual' );
	const [ loading, setLoading ] = useState( false );
	const [ error,   setError   ] = useState( null );

	// Manual — pre-load sample data for new charts (no initialCsvText means no existing data).
	const [ csvText, setCsvText ] = useState( initialCsvText || SAMPLE_CSV );
	const [ sheetRows, setSheetRows ] = useState(
		initialCsvText ? csvToGrid( initialCsvText ) : csvToGrid( SAMPLE_CSV )
	);
	const sheetEditRef = useRef( false );
	const sheetHotRef = useRef( null );

	// File
	const [ file, setFile ] = useState( null );
	const fileInputRef = useRef( null );
	// Skip the initial auto-upload only when editing existing data.
	const skipUploadRef = useRef( !! initialCsvText );

	// URL
	const [ fileUrl, setFileUrl ] = useState( '' );
	const lastCsvRef = useRef( null );

	function parseCsv( text ) {
		const lines = text.split( /\r?\n/ ).map( ( l ) => l.trim() ).filter( Boolean );
		if ( lines.length < 3 ) return null;
		const header = lines[ 0 ];
		const types  = lines[ 1 ];
		const data   = lines.slice( 2 ).join( '\n' );
		return { header, types, data };
	}

	function csvToGrid( text ) {
		const lines = text.split( /\r?\n/ ).filter( ( l ) => l.length );
		if ( lines.length === 0 ) {
			return [
				[ 'Column 1', 'Column 2' ],
				[ 'string', 'number' ],
				[ '', '' ],
			];
		}
		const rows = lines.map( ( line ) => line.split( ',' ) );
		const maxCols = Math.max( ...rows.map( ( r ) => r.length ) );
		return rows.map( ( r ) => [ ...r, ...Array( Math.max( 0, maxCols - r.length ) ).fill( '' ) ] );
	}

	function gridToCsv( rows ) {
		return rows.map( ( r ) => r.join( ',' ) ).join( '\n' );
	}

	function cleanGrid( rows ) {
		if ( ! rows || ! rows.length ) return rows;
		const isEmpty = ( value ) => value === null || value === undefined || String( value ).trim() === '';
		let lastColIndex = -1;
		rows.forEach( ( row ) => {
			row.forEach( ( value, index ) => {
				if ( ! isEmpty( value ) ) {
					lastColIndex = Math.max( lastColIndex, index );
				}
			} );
		} );
		lastColIndex = Math.max( lastColIndex, 1 );
		let trimmed = rows.map( ( row ) => row.slice( 0, lastColIndex + 1 ) );

		let lastDataRow = -1;
		for ( let i = 2; i < trimmed.length; i++ ) {
			if ( trimmed[ i ].some( ( value ) => ! isEmpty( value ) ) ) {
				lastDataRow = i;
			}
		}
		const headerRows = trimmed.slice( 0, 2 );
		if ( lastDataRow >= 2 ) {
			trimmed = headerRows.concat( trimmed.slice( 2, lastDataRow + 1 ) );
		} else {
			trimmed = headerRows.concat( [ Array( lastColIndex + 1 ).fill( '' ) ] );
		}
		return trimmed;
	}

	function buildCsv( series = [], data = [] ) {
		if ( ! series.length ) return '';
		const labels = series.map( ( s ) => s.label ).join( ',' );
		const types  = series.map( ( s ) => s.type || 'string' ).join( ',' );
		const rows   = ( data || [] ).map( ( row ) => row.join( ',' ) );
		return [ labels, types, ...rows ].join( '\n' );
	}

	function switchSource( id ) {
		if ( id === 'manual' && source === 'sheet' ) {
			const cleaned = cleanGrid( sheetRows );
			skipUploadRef.current = true;
			setSheetRows( cleaned );
			setCsvText( gridToCsv( cleaned ) );
		}
		setSource( id );
		setError( null );
		if ( onClearWarning ) onClearWarning();
	}

	// ── Auto-load effects ─────────────────────────────────────────────────────

	// Populate manual textarea from initialCsvText (edit mode), without triggering upload.
	useEffect( () => {
		if ( ! initialCsvText ) return;
		skipUploadRef.current = true;
		lastCsvRef.current = initialCsvText.trim();
		setCsvText( initialCsvText );
	}, [ initialCsvText ] ); // eslint-disable-line react-hooks/exhaustive-deps

	// Manual CSV: debounce 700 ms — uploads whenever the full CSV text changes.
	useEffect( () => {
		if ( disabled ) return;
		if ( ( source !== 'manual' && source !== 'sheet' ) || ! csvText.trim() || ! chartId || ! uploadNonce ) return;
		if ( skipUploadRef.current ) { skipUploadRef.current = false; return; }
		const trimmed = csvText.trim();
		if ( lastCsvRef.current === trimmed ) return;
		const parsed = parseCsv( trimmed );
		if ( ! parsed ) {
			setError( __( 'CSV must include header and types rows.', 'visualizer' ) );
			return;
		}
		const t = setTimeout( async () => {
			setLoading( true ); setError( null );
			try {
				const r = await uploadCsvString( chartId, uploadNonce, trimmed );
				onDataReady( r.series, r.data );
				lastCsvRef.current = trimmed;
			} catch ( e ) { setError( e.message ); }
			finally { setLoading( false ); }
		}, 700 );
		return () => clearTimeout( t );
	}, [ source, csvText, uploadNonce ] ); // eslint-disable-line react-hooks/exhaustive-deps

	// File and URL import are manual (button-triggered).
	async function handleImportFile() {
		if ( disabled || ! file || ! chartId ) return;
		setLoading( true ); setError( null );
		try {
			const r = await uploadFile( chartId, uploadNonce, file );
			onDataReady( r.series, r.data );
			const csv = buildCsv( r.series, r.data );
			if ( csv ) {
				skipUploadRef.current = true;
				setCsvText( csv );
			}
			setSource( 'manual' );
		} catch ( e ) { setError( e.message ); }
		finally { setLoading( false ); }
	}

	async function handleImportUrl() {
		if ( disabled || ! fileUrl.trim() || ! chartId ) return;
		setLoading( true ); setError( null );
		try {
			const r = await uploadFileUrl( chartId, uploadNonce, fileUrl.trim() );
			onDataReady( r.series, r.data );
			const csv = buildCsv( r.series, r.data );
			if ( csv ) {
				skipUploadRef.current = true;
				setCsvText( csv );
			}
			setSource( 'manual' );
		} catch ( e ) { setError( e.message ); }
		finally { setLoading( false ); }
	}

	// Keep spreadsheet grid in sync with CSV when switching sources or external updates.
	useEffect( () => {
		if ( source !== 'sheet' ) return;
		if ( sheetEditRef.current ) { sheetEditRef.current = false; return; }
		setSheetRows( csvToGrid( csvText ) );
	}, [ source, csvText ] );

	function updateSheetCell( rowIndex, colIndex, value ) {
		setSheetRows( ( prev ) => {
			const next = prev.map( ( r ) => [ ...r ] );
			if ( ! next[ rowIndex ] ) next[ rowIndex ] = [];
			next[ rowIndex ][ colIndex ] = value;
			sheetEditRef.current = true;
			setCsvText( gridToCsv( next ) );
			return next;
		} );
	}

	function addSheetRow() {
		setSheetRows( ( prev ) => {
			const cols = prev[ 0 ] ? prev[ 0 ].length : 2;
			const next = [ ...prev, Array( cols ).fill( '' ) ];
			sheetEditRef.current = true;
			setCsvText( gridToCsv( next ) );
			return next;
		} );
	}

	function addSheetCol() {
		setSheetRows( ( prev ) => {
			const next = prev.map( ( r ) => [ ...r, '' ] );
			sheetEditRef.current = true;
			setCsvText( gridToCsv( next ) );
			return next;
		} );
	}

	return (
		<Stack gap="4">

			{ /* ── Source tabs ───────────────────────────────────────────── */ }
			<Box
				bg={ C.sidebar }
				borderRadius="10px"
				p="3px"
				border="1px solid"
				borderColor={ C.border }
				display="flex"
				gap="3px"
			>
				{ SOURCES.map( ( s ) => {
					const selected = source === s.id;
					return (
						<Box
							key={ s.id }
							flex="1"
							display="flex" alignItems="center" justifyContent="center" gap="8px"
							py="6px"
							borderRadius="8px"
							bg={ selected ? 'white' : 'transparent' }
							border={ selected ? '1px solid' : '1px solid transparent' }
							borderColor={ selected ? C.border : 'transparent' }
							boxShadow={ selected ? '0 2px 6px rgba(0,0,0,0.06)' : 'none' }
							cursor={ disabled ? 'not-allowed' : 'pointer' }
							opacity={ disabled ? 0.6 : 1 }
							onClick={ disabled ? undefined : () => switchSource( s.id ) }
							transition="all 0.15s"
						>
							<Box
								w="18px" h="18px" borderRadius="5px"
								display="flex" alignItems="center" justifyContent="center"
								bg={ selected ? C.dark : 'white' }
								border="1px solid"
								borderColor={ selected ? C.dark : C.border }
								transition="background 0.15s"
							>
								<s.Icon active={ selected } />
							</Box>
							<Text
								fontSize="10px"
								fontWeight={ selected ? '600' : '500' }
								color={ selected ? C.dark : C.gray1 }
								letterSpacing="0.01em"
								lineHeight="1"
								whiteSpace="nowrap"
							>
								{ s.label }
							</Text>
						</Box>
					);
				} ) }
			</Box>

			{ ! isPro && source !== 'manual' && (
				<Box
					p="4"
					borderRadius="12px"
					bg="linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%)"
					border="1px solid"
					borderColor={ C.border }
					boxShadow="0 8px 24px rgba(16,24,40,0.08)"
				>
					<Text fontSize="13px" fontWeight="700" color={ C.dark } mb="1">
						{ __( 'Unlock advanced data sources', 'visualizer' ) }
					</Text>
					<Text fontSize="12px" color={ C.gray1 } mb="3">
						{ __( 'Spreadsheet, File, and URL imports are available in Pro.', 'visualizer' ) }
					</Text>
					<Flex align="center" gap="2" mb="3">
						<Text fontSize="11px" color={ C.gray1 }>{ __( '• Spreadsheet editor', 'visualizer' ) }</Text>
						<Text fontSize="11px" color={ C.gray1 }>{ __( '• CSV & XLSX upload', 'visualizer' ) }</Text>
						<Text fontSize="11px" color={ C.gray1 }>{ __( '• Remote URL import', 'visualizer' ) }</Text>
					</Flex>
					<Flex align="center" gap="2">
						<Box
							as="a"
							href={ window?.vizAIBuilder?.upgradeUrl || 'https://themeisle.com/plugins/visualizer-charts-and-graphs/upgrade/' }
							target="_blank"
							rel="noopener noreferrer"
							display="inline-flex"
							alignItems="center"
							gap="6px"
							px="12px" py="8px"
							borderRadius="9px"
							fontSize="12px"
							fontWeight="700"
							bg={ C.dark }
							color="white"
							_hover={ { color: 'white', textDecoration: 'none', filter: 'brightness(0.98)' } }
							_focus={ { color: 'white', textDecoration: 'none' } }
						>
							{ __( 'Upgrade to Pro', 'visualizer' ) }
						</Box>
						<Text fontSize="11px" color={ C.gray2 }>
							{ __( 'Cancel anytime', 'visualizer' ) }
						</Text>
					</Flex>
				</Box>
			) }

			{ /* ── 1. Manual ───────────────────────────────────────────────── */ }
			{ source === 'manual' && (
				<Box border={ `1.5px solid ${ C.border }` } borderRadius="10px" overflow="hidden">
					<Flex
						align="center" justify="space-between"
						px="3" py="1.5"
						bg={ C.sidebar } borderBottom={ `1.5px solid ${ C.border }` }
					>
						<Text fontSize="11px" fontWeight="500" color={ C.gray1 } letterSpacing="0.03em" textTransform="uppercase">
							{ __( 'CSV data', 'visualizer' ) }
						</Text>
						<Flex align="center" gap="2.5">
							<Text fontSize="11px" color={ C.gray2 }>{ __( 'Row 1: labels · Row 2: types', 'visualizer' ) }</Text>
							<Box
								as="button"
								fontSize="11px" color={ C.gray1 } fontWeight="500"
								px="7px" py="2px" borderRadius="5px"
								bg="#E8E6E1" border="none" cursor="pointer"
								onClick={ disabled ? undefined : () => setCsvText( SAMPLE_CSV ) }
								disabled={ disabled }
								opacity={ disabled ? 0.6 : 1 }
								style={ { cursor: disabled ? 'not-allowed' : 'pointer' } }
							>
								{ __( 'Load sample', 'visualizer' ) }
							</Box>
						</Flex>
					</Flex>
					<Box
						as="textarea"
						display="block" w="100%" h="96px"
						border="none" outline="none" resize="none"
						px="12px" py="10px"
						fontFamily="'SF Mono', 'Fira Code', monospace" fontSize="12px" lineHeight="1.6"
						color={ C.dark }
						placeholder={ PLACEHOLDER_CSV }
						value={ csvText }
						onChange={ disabled ? undefined : ( e ) => setCsvText( e.target.value ) }
						disabled={ disabled }
						sx={ { '&::placeholder': { color: C.gray3 } } }
					/>
				</Box>
			) }

			{ /* ── 1b. Spreadsheet ─────────────────────────────────────────── */ }
			{ source === 'sheet' && isPro && (
				<Box border={ `1.5px solid ${ C.border }` } borderRadius="10px" overflow="hidden" bg="white">
					<Flex
						align="center" justify="space-between"
						px="3" py="1.5"
						bg={ C.sidebar } borderBottom={ `1.5px solid ${ C.border }` }
					>
						<Text fontSize="11px" fontWeight="500" color={ C.gray1 } letterSpacing="0.03em" textTransform="uppercase">
							{ __( 'Spreadsheet', 'visualizer' ) }
						</Text>
						<Flex align="center" gap="2">
							<Text fontSize="11px" color={ C.gray2 }>{ __( 'Row 1: labels · Row 2: types', 'visualizer' ) }</Text>
							<Box
								as="button"
								fontSize="11px" color={ C.gray1 } fontWeight="500"
								px="7px" py="2px" borderRadius="5px"
								bg="#E8E6E1" border="none"
								onClick={ disabled ? undefined : () => setCsvText( SAMPLE_CSV ) }
								disabled={ disabled }
								opacity={ disabled ? 0.6 : 1 }
								style={ { cursor: disabled ? 'not-allowed' : 'pointer' } }
							>
								{ __( 'Load sample', 'visualizer' ) }
							</Box>
						</Flex>
					</Flex>
					<Box
						className="viz-ai-hot"
						position="relative"
						overflow="visible"
					>
						{ sheetRows.length ? (
							<HotTable
								ref={ sheetHotRef }
								data={ sheetRows }
								handsontable={ window?.Handsontable }
								colHeaders={ true }
								rowHeaders={ true }
								allowInsertRow={ true }
								allowInsertColumn={ true }
								outsideClickDeselects={ false }
								stretchH="all"
								height="240"
								licenseKey="non-commercial-and-evaluation"
								minRows={ 3 }
								minCols={ 2 }
								manualColumnResize={ true }
								manualRowResize={ true }
								readOnly={ disabled }
								afterChange={ ( changes, source ) => {
									if ( ! changes || source === 'loadData' ) return;
									changes.forEach( ( [ row, col, , value ] ) => {
										updateSheetCell( row, col, value ?? '' );
									} );
								} }
							/>
						) : (
							<Text fontSize="12px" color={ C.gray2 } px="3" py="3">
								{ __( 'Loading spreadsheet…', 'visualizer' ) }
							</Text>
						) }
					</Box>
					<Flex align="center" gap="2" px="3" py="2" borderTop={ `1.5px solid ${ C.border }` } bg="white">
						<Box
							as="button"
							px="10px" py="5px" borderRadius="7px"
							fontSize="11px" fontWeight="500"
							bg="white" border="1.5px solid" borderColor={ C.border }
							cursor={ disabled ? 'not-allowed' : 'pointer' }
							opacity={ disabled ? 0.6 : 1 }
							onClick={ disabled ? undefined : addSheetRow }
						>
							{ __( 'Add row', 'visualizer' ) }
						</Box>
						<Box
							as="button"
							px="10px" py="5px" borderRadius="7px"
							fontSize="11px" fontWeight="500"
							bg="white" border="1.5px solid" borderColor={ C.border }
							cursor={ disabled ? 'not-allowed' : 'pointer' }
							opacity={ disabled ? 0.6 : 1 }
							onClick={ disabled ? undefined : addSheetCol }
						>
							{ __( 'Add column', 'visualizer' ) }
						</Box>
					</Flex>
				</Box>
			) }

			{ /* ── 2. File ─────────────────────────────────────────────────── */ }
			{ source === 'csv_file' && isPro && (
				<>
					<Flex align="center" gap="2">
						<Box
							flex="1"
							px="10px" py="7px"
							borderRadius="8px"
							border="1.5px solid"
							borderColor={ C.border }
							fontSize="12px"
							bg="white"
							color={ file ? C.dark : C.gray2 }
							opacity={ disabled ? 0.6 : 1 }
							whiteSpace="nowrap"
							overflow="hidden"
							textOverflow="ellipsis"
						>
							{ file ? file.name : __( 'Choose a file (.csv or .xlsx)', 'visualizer' ) }
						</Box>
						<Box
							as="button"
							px="12px" py="7px" borderRadius="8px"
							fontSize="11px" fontWeight="600"
							bg={ C.dark } color="white" border="none"
							cursor={ disabled ? 'not-allowed' : 'pointer' }
							onClick={ disabled ? undefined : () => fileInputRef.current?.click() }
							disabled={ disabled }
							opacity={ disabled ? 0.6 : 1 }
						>
							{ __( 'Browse', 'visualizer' ) }
						</Box>
					</Flex>
					<Box>
						<Box
							as="button"
							px="14px" py="7px" borderRadius="9px"
							fontSize="12px" fontWeight="500"
							bg={ C.dark } color="white" border="none"
							cursor={ ( disabled || ! file ) ? 'not-allowed' : 'pointer' }
							opacity={ ( disabled || ! file ) ? 0.6 : 1 }
							onClick={ ( disabled || ! file ) ? undefined : handleImportFile }
						>
							{ __( 'Import file', 'visualizer' ) }
						</Box>
					</Box>
					<input
						ref={ fileInputRef }
						type="file" accept=".csv,.xlsx"
						disabled={ disabled }
						style={ { display: 'none' } }
						onChange={ ( e ) => setFile( e.target.files[ 0 ] || null ) }
					/>
					<FieldHint>{ __( 'Row 1 = column names · Row 2 = types (string / number / date / boolean)', 'visualizer' ) }</FieldHint>
				</>
			) }

			{ /* ── 3. URL ──────────────────────────────────────────────────── */ }
			{ source === 'url' && isPro && (
				<Stack gap="3">
					<Box>
						<FieldLabel>{ __( 'Remote file URL', 'visualizer' ) }</FieldLabel>
						<FieldInput
							type="url"
							placeholder="https://example.com/data.csv"
							value={ fileUrl }
							onChange={ disabled ? undefined : ( e ) => setFileUrl( e.target.value ) }
							disabled={ disabled }
						/>
						<FieldHint>{ __( 'Supports .csv and .xlsx · Google Spreadsheet share URLs accepted', 'visualizer' ) }</FieldHint>
					</Box>
					<Box>
						<Box
							as="button"
							px="14px" py="7px" borderRadius="9px"
							fontSize="12px" fontWeight="500"
							bg={ C.dark } color="white" border="none"
							cursor={ ( disabled || ! fileUrl.trim() ) ? 'not-allowed' : 'pointer' }
							opacity={ ( disabled || ! fileUrl.trim() ) ? 0.6 : 1 }
							onClick={ ( disabled || ! fileUrl.trim() ) ? undefined : handleImportUrl }
						>
							{ __( 'Import URL', 'visualizer' ) }
						</Box>
					</Box>
				</Stack>
			) }

			{ /* ── Loading / error feedback ────────────────────────────────── */ }
			{ loading && (
				<Flex align="center" gap="2">
					<Box
						w="14px" h="14px" borderRadius="full"
						border="2px solid #ddd" borderTopColor={ C.dark }
						style={ { animation: 'viz-spin 0.7s linear infinite' } }
						flexShrink={ 0 }
					/>
					<Text fontSize="12px" color={ C.gray2 }>{ __( 'Loading data…', 'visualizer' ) }</Text>
				</Flex>
			) }
			{ error && (
				<Box p="2.5" bg="#fff5f5" border="1.5px solid #fca5a5" borderRadius="8px">
					<Text fontSize="12px" color="#b91c1c">{ error }</Text>
				</Box>
			) }
			{ dataLoaded && ! loading && ! error && (
				<Flex align="center" gap="1.5">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
						<circle cx="7" cy="7" r="6" fill="#d1fae5" />
						<path d="M4.5 7l2 2 3-3" stroke="#059669" strokeWidth="1.3" strokeLinecap="round" strokeLinejoin="round" />
					</svg>
					<Text fontSize="12px" color="#059669" fontWeight="500">{ __( 'Data loaded', 'visualizer' ) }</Text>
				</Flex>
			) }
			{ dataWarning && (
				<Box p="2.5" bg="#fffbeb" border="1.5px solid #fcd34d" borderRadius="8px">
					<Text fontSize="12px" color="#b45309">{ dataWarning }</Text>
				</Box>
			) }

		</Stack>
	);
}
