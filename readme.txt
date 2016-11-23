=== WordPress Charts and Graphs Lite ===
Contributors:  codeinwp,marius2012,marius_codeinwp,hardeepasrani,themeisle,Madalin_ThemeIsle
Tags: chart, charts, charting, graph, graphs, graphing, visualisation, visualise data, visualization, visualize data, HTML5, canvas, pie chart, line chart, bar chart, column chart, gauge chart, area chart, scatter chart, candlestick chart, geo chart, google visualization api
Requires at least: 3.5
Tested up to: 4.6.1
Stable tag: trunk
License: GPL v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php

A simple and quite powerful WordPress chart plugin to create, manage and embed interactive charts into your WordPress posts and pages.

== Description ==
 
<a href="http://themeisle.com/plugins/visualizer-charts-and-graphs-lite/" rel="nofollow">WordPress Visualizer plugin</a> is a simple, easy to use and quite powerful tool to create, manage and embed interactive charts into your WordPress posts and pages.

The plugin uses Google Visualization API to add charts, which support cross-browser compatibility (adopting VML for older IE versions) and cross-platform portability to iOS and new Android releases.

> **Time-saving features available in the Pro version:**
>
> * Import data from other charts
> * Easy edit the data using a live editor
> * 3 more chart types ( Combo, Timeline and Table chart )
> * Priority email support from the developer of the plugin
> * Support and updates for 1 year
>
> **[Learn more about Visualizer PRO](http://themeisle.com/plugins/visualizer-charts-and-graphs-pro-addon/)**


### 9 Chart types + 3 more in the pro version ###
This WordPress graph plugin provides a variety of charts that are optimized to address your WordPress data visualization needs. It is line chart, area chart, bar chart, column chart, pie chart, geo chart, gauge chart, candlestick chart and scatter chart. These charts are based on pure HTML5/SVG technology (adopting VML for old IE versions), so no extra plugins are required. Adding these charts to your page can be done in a few simple steps.

### Flexible and customizable ###
Make the charts your own. Configure an extensive set of options to perfectly match the look and feel of your website. You can use Google Chart Tools with their default setting - all customization is optional and the basic setup is launch-ready. However, charts can be easily customizable in case your webpage adopts a style which is at odds with provided defaults. Every chart exposes a number of options that customize its look and feel.

### HTML5/SVG ###
Charts are rendered using HTML5/SVG technology to provide cross-browser compatibility (including VML for older IE versions) and cross platform portability to iPhones, iPads and Android. Your users will never have to mess with extra plugins or any software. If they have a web browser, they can see your charts.

*above descriptions were partially taken from Google Visualization API site*

The plugins works perfectly with the all <a href="http://justfreethemes.com" rel="nofollow">free</a> or <a href="http://www.codeinwp.com/blog/best-wordpress-themes/" rel="nofollow">premium WordPress themes</a>

### Knowledge Base ###

1. [How can I create a chart?](https://github.com/madpixelslabs/visualizer/wiki/How-can-I-create-a-chart%3F)
1. [How can I edit a chart?](https://github.com/madpixelslabs/visualizer/wiki/How-can-I-edit-a-chart%3F)
1. [How can I clone a chart?](https://github.com/madpixelslabs/visualizer/wiki/How-can-I-clone-a-chart%3F)
1. [How can I delete a chart?](https://github.com/madpixelslabs/visualizer/wiki/How-can-I-delete-a-chart%3F)
1. [How can I highlight a single bar?](https://github.com/madpixelslabs/visualizer/wiki/How-can-I-highlight-a-single-bar%3F)
1. [How can I populate chart series and data dynamically?](https://github.com/madpixelslabs/visualizer/wiki/How-can-I-populate-chart-series-and-data-dynamically%3F)
1. [How can I populate data from Google Spreadsheet?](https://github.com/madpixelslabs/visualizer/wiki/How-can-I-populate-data-from-Google-Spreadsheet%3F)

== Installation ==

1. Upload the files to the `/wp-content/plugins/visualizer/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Shortcode does not turn into graph =

Pay attention that to turn your shortcodes into graphs, your theme has to have `<?php wp_footer(); ?>` call at the bottom of **footer.php** file.

== Screenshots ==

1. Chart type selection
2. Chart data uploading
3. Chart options editing
4. Charts media library
5. Charts library

== Changelog ==

= 1.7.2=
* Improved charts responsive system

= 1.7.1=
* Fixed grid lines error links

= 1.7.0=
* Fixed responsive issues
* Fixed issues with zero margin values
* Fixed import issue

= 1.6.6=
* Fixed charts resizing on tabbed content

= 1.6.5=
* Fixed responsive issue
* Fixed no axis text color for line and bar charts


= 1.6.0 =
* Fixed security issue when importing charts
* Removed pointer for the pro version
* Fixed charts import from media library
* Added support to show legend on the left side


= 1.5.6 =
* Added support for 3 more chart types
* Fixed issue with charts not saving


= 1.5.5 =
* Added export for charts
* Enable default value for focus target. Fixed issue with hover which was not working on some machines.


= 1.5.4 =
* Added free search text over graphs

= 1.5.2 =
* Added step2 and 3 into step 1

= 1.5.1 =
* Fixed bug with from web button

= 1.5 =
* Added support for live editor
* Added support for importing data from other charts
* Added filter for chart settings
* Fixed bug when zero was not working on the charts

= 1.4.2.3 =
* Implemented ability to edit horizontal and vertical axis number format for bar and column charts

= 1.4.2.2 =
* Added ability to pass a class for chart wrapper div
* Added proper label for custom post type

= 1.4.2.1 =
* Fixed issue with download_url function which not exists at front end
* Added functionality which prevents direct access to the plugin folder

= 1.4.2 =
* Fixed remote CSV uploading issue when allow_url_fopen option is disabled in php.ini
* Replaced flattr image on widget and added donate link to plugin meta data
* Added notification message at library page when allow_url_fopen option is disabled

= 1.4.1.1 =
* Removed CSV parser escape constant to prevent warnings which appears when PHP 5.2.x or less is used

= 1.4.1 =
* Fixed issue which prevents the plugin working on SSL backend
* Fixed issue with CSV file uploading in IE and other browsers
* Fixed issue with empty series, which appears due to leading space in a source file
* Added ability to define custom delimiter, enclosure and escape variables for CSV parsing

= 1.4 =
* Implemented aggregation target and selection mode options for candlestick chart
* Implemented focus target and data opacity for columnar chars
* Implemented data opacity and interpolate nulls settings for line chart
* Implemented ability to edit tooltip settings
* Implemented new settings for linear charts like selection mode and aggregation target
* Implemented area and point opacity settings for area chart
* Implemented new settings for pie chart like pie hole, start angle and slice offset
* Implemented ability to select a color for chart title and legend items
* Fixed number formatting settings for linear charts, from now it works only for axis labels
* Reworked general settings section by moving title and font settings into separate groups

= 1.3.0.2 =
* Replaced links to github wiki

= 1.3.0.1 =
* Added Flattr button

= 1.3.0 =
* Implemented ability to set number and date formatters
* Implemented ability to select transparent background for a chart
* Fixed JS bugs which appear when post type editor support is disabled
* Fixed issue with NULL values for numeric series
* Fixed invalid charts rendering at "Add Media" library
* Fixed compatibility issue with another Google API related plugins
* Added "rate the plugin" box

= 1.2.0 =
* Implemented minor grid lines settings.
* Implemented view window settings.
* Horizontal and vertical axes settings were split into separate groups.

= 1.1.4 =
* Bug with float values has been fixed.

= 1.1.3 =
* Issue with "fseek warning" for Google Spreadsheet document source, was fixed.

= 1.1.2 =
* Compatibility issues with WordPress version 3.6 has been fixed.

= 1.1.1 =
* Active type tab in the charts library was fixed.
* Library styles were updated.

= 1.1.0 =
* Auto population was added for remote CSV file source.
* Ability to hook chart series and data was implemented.
* Ability to upload CSV files from web was implemented.

= 1.0.1 =
* The bug with CSV file uploading was fixed.

= 1.0.0 =
* The first version of the plugin was implemented.
