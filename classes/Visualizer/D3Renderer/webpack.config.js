const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const buildDir = path.resolve( __dirname, 'build' );

/**
 * Main bundle (index.js) — standard wp-scripts config with WP dependency
 * extraction.  Runs in the parent page context.
 */
const mainConfig = {
	...defaultConfig,
	entry: {
		index: path.resolve( __dirname, 'src/index.js' ),
	},
	output: {
		...defaultConfig.output,
		path: buildDir,
	},
};

/**
 * Iframe bundle (iframe.js) — standalone IIFE that runs inside a sandboxed
 * iframe.  WordPress dependency extraction is disabled so that d3 and
 * topojson are bundled inline (the iframe has no access to wp.* globals).
 */
const iframeConfig = {
	...defaultConfig,
	entry: {
		iframe: path.resolve( __dirname, 'src/iframe.js' ),
	},
	output: {
		...defaultConfig.output,
		path: buildDir,
	},
	// Remove DependencyExtractionWebpackPlugin — iframe has no WP globals.
	plugins: defaultConfig.plugins.filter(
		( plugin ) => plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
	),
	// Bundle d3/topojson inline; do not treat anything as external.
	externals: {},
};

module.exports = [ mainConfig, iframeConfig ];
