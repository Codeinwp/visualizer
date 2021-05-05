/* jshint node:true */
/* global require */

module.exports = function (grunt) {
	'use strict';
	grunt.initConfig({
		wp_readme_to_markdown: {
			files: {
				'readme.md': 'readme.txt'
			},
		},
		version: {

			project: {
				src: [
					'package.json'
				]
			},
			style: {
				options: {
					prefix: 'Version\\:\\s'
				},
				src: [
					'index.php',
					'css/media.css',
				]
			},
			functions: {
				options: {
					prefix: 'VERSION\\s+=\\s+[\'"]'
				},
				src: [
					'classes/Visualizer/Plugin.php',
				]
			}
		}
	});
	grunt.loadNpmTasks('grunt-version');
	grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
};