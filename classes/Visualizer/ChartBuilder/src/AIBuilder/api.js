/**
 * AJAX helpers for the AI Chart Builder.
 * All calls go to admin-ajax.php using the vizAIBuilder global localized by PHP.
 */

const { ajaxUrl, nonce } = window.vizAIBuilder || {};

async function post( action, body ) {
	const form = new FormData();
	form.append( 'action', action );
	form.append( 'nonce', nonce );
	for ( const [ key, val ] of Object.entries( body ) ) {
		form.append( key, val );
	}
	const res  = await fetch( ajaxUrl, { method: 'POST', body: form } );
	let json;
	try {
		json = await res.json();
	} catch {
		throw new Error( 'Server returned an unexpected response.' );
	}
	if ( ! json.success ) {
		throw new Error( json.data?.message || 'Request failed.' );
	}
	return json.data;
}

/** Create an auto-draft chart post. Returns { chart_id, upload_nonce }. */
export async function createChart() {
	return post( 'visualizer-ai-create', {} );
}

/** Get the upload nonce for an existing chart (edit mode). Returns { upload_nonce }. */
export async function getChartNonce( chartId ) {
	return post( 'visualizer-ai-chart-nonce', { chart_id: chartId } );
}

/** Fetch chart data for edit mode. Returns { title, series, data, code }. */
export async function fetchChart( chartId ) {
	return post( 'visualizer-ai-fetch', { chart_id: chartId } );
}

/** Publish chart with D3 code. Returns { id, shortcode }. */
export async function saveChart( chartId, title, code ) {
	return post( 'visualizer-ai-save', {
		chart_id: chartId,
		title,
		code,
	} );
}

// ── Upload helpers ─────────────────────────────────────────────────────────────

/** Build a FormData for any upload call, including the per-chart upload nonce. */
function uploadForm( action, chartId, uploadNonce, fields = {} ) {
	const form = new FormData();
	form.append( 'action', action );
	form.append( 'nonce', uploadNonce );
	form.append( 'chart_id', chartId );
	for ( const [ key, val ] of Object.entries( fields ) ) {
		if ( val !== null && val !== undefined && val !== '' ) {
			form.append( key, val );
		}
	}
	return form;
}

async function uploadFetch( form ) {
	const res = await fetch( ajaxUrl, { method: 'POST', body: form } );
	let json;
	try {
		json = await res.json();
	} catch {
		throw new Error( 'Server returned an unexpected response.' );
	}
	if ( ! json.success ) {
		throw new Error( json.data?.message || 'Upload failed.' );
	}
	return json.data;
}

/** Upload pasted CSV string. Returns { series, data }. */
export async function uploadCsvString( chartId, uploadNonce, csvData ) {
	const form = uploadForm( 'visualizer-ai-upload', chartId, uploadNonce, {
		source_type: 'csv_string',
		csv_data: csvData,
	} );
	return uploadFetch( form );
}

/** Upload a CSV or XLSX file. Returns { series, data }. */
export async function uploadFile( chartId, uploadNonce, file ) {
	const ext  = file.name.split( '.' ).pop().toLowerCase();
	const form = uploadForm( 'visualizer-ai-upload', chartId, uploadNonce, {
		source_type: ext === 'xlsx' ? 'xlsx_file' : 'csv_file',
	} );
	form.append( 'data_file', file );
	return uploadFetch( form );
}

/** Upload a remote CSV/XLSX URL. Returns { series, data }. */
export async function uploadFileUrl( chartId, uploadNonce, url, schedule = '' ) {
	const form = uploadForm( 'visualizer-ai-upload', chartId, uploadNonce, {
		source_type: 'file_url',
		file_url:    url,
		schedule:    schedule,
	} );
	return uploadFetch( form );
}

/** Upload a JSON URL source. Returns { series, data }. */
export async function uploadJsonUrl( chartId, uploadNonce, params ) {
	const {
		url, root = '', paging = '', method = 'GET',
		auth = '', username = '', password = '', headers = '',
		schedule = '',
	} = params;
	const form = uploadForm( 'visualizer-ai-upload', chartId, uploadNonce, {
		source_type:   'json_url',
		json_url:      url,
		json_root:     root,
		json_paging:   paging,
		json_method:   method,
		json_auth:     auth,
		json_username: username,
		json_password: password,
		json_headers:  headers,
		json_schedule: schedule,
	} );
	return uploadFetch( form );
}

// ── AI generation helpers ──────────────────────────────────────────────────────

/**
 * Start async chart generation.
 * Pass existingCode (string) when refining so the agent has full context of the current chart.
 * Returns { workflow_id }.
 */
export async function generateChart( chartId, prompt, series, data, existingCode = null, refImageBase64 = null, refImageMime = null ) {
	const body = {
		chart_id: chartId,
		prompt,
		series:   JSON.stringify( series ),
		data:     JSON.stringify( data ),
	};
	if ( existingCode ) {
		body.existing_code = existingCode;
	}
	if ( refImageBase64 ) {
		body.ref_image      = refImageBase64;
		body.ref_image_mime = refImageMime || 'image/jpeg';
	}
	return post( 'visualizer-ai-generate', body );
}

/**
 * Poll the status of an async generation job.
 * Returns { status, output: { spec } | null }.
 */
export async function pollStatus( workflowId ) {
	return post( 'visualizer-ai-status', { workflow_id: workflowId } );
}

/** Upload a database query source. Returns { series, data }. */
export async function uploadDbQuery( chartId, uploadNonce, query, dbParams = {} ) {
	const {
		host = '', port = 3306, name = '',
		username = '', password = '', type = 'mysql',
	} = dbParams;
	const form = uploadForm( 'visualizer-ai-upload', chartId, uploadNonce, {
		source_type:  'db_query',
		db_query:     query,
		db_host:      host,
		db_port:      port,
		db_name:      name,
		db_username:  username,
		db_password:  password,
		db_type:      type,
	} );
	return uploadFetch( form );
}
