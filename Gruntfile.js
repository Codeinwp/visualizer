/* jshint node:true */
/* global require */

module.exports = function (grunt) {
	'use strict';

	var loader = require( 'load-project-config' ),
		config = require( 'grunt-plugin-fleet' );
	config = config();
	// jshint ignore: start
	config.taskMap['faq_builder'] = 'grunt-helpscout-faq';
	// jshint ignore: end
    config.files.js.push( '!js/gutenberg/*.js' );
    config.files.js.push( '!webpack.config.js' );
	loader(grunt, config).init();
	loader( grunt, config ).init();
};