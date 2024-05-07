// Fork of https://github.com/WordPress/gutenberg/blob/trunk/test/e2e/playwright.config.ts

/**
 * External dependencies
 */
import os from 'os';
import { fileURLToPath } from 'url';
import { defineConfig, devices } from '@playwright/test';

/**
 * WordPress dependencies
 */
const baseConfig = require( '@wordpress/scripts/config/playwright.config' );

const config = defineConfig( {
	...baseConfig,
	reporter: process.env.CI
		? [ [ 'github' ], [ './config/flaky-tests-reporter.js' ] ]
		: 'list',
	workers: 1,
	globalSetup: fileURLToPath(
		new URL( './config/global-setup.js', 'file:' + __filename ).href
	),
	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ], permissions: ["clipboard-read"] },
			grepInvert: /-chromium/,
		},
		// {
		// 	name: 'webkit',
		// 	use: {
		// 		...devices[ 'Desktop Safari' ],
		// 		/**
		// 		 * Headless webkit won't receive dataTransfer with custom types in the
		// 		 * drop event on Linux. The solution is to use `xvfb-run` to run the tests.
		// 		 * ```sh
		// 		 * xvfb-run npm run test:e2e:playwright
		// 		 * ```
		// 		 * See `.github/workflows/end2end-test-playwright.yml` for advanced usages.
		// 		 */
		// 		headless: os.type() !== 'Linux',
		// 	},
		// 	grep: /@webkit/,
		// 	grepInvert: /-webkit/,
		// },
		// {
		// 	name: 'firefox',
		// 	use: { ...devices[ 'Desktop Firefox' ] },
		// 	grep: /@firefox/,
		// 	grepInvert: /-firefox/,
		// },
	],
} );

export default config;
