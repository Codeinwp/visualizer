=== WordPress Charts and Graphs Lite ===
Contributors:  codeinwp,marius2012,marius_codeinwp,hardeepasrani,themeisle,Madalin_ThemeIsle,rozroz
Tags: chart, charts, charting, graph, graphs, graphing, visualisation, visualise data, visualization, visualize data, HTML5, canvas, pie chart, line chart, bar chart, column chart, gauge chart, area chart, scatter chart, candlestick chart, geo chart, google visualization api
Requires at least: 3.5
Tested up to: 4.8.0
Stable tag: trunk
License: GPL v2.0 or later
License URI: http://www.opensource.org/licenses/gpl-license.php

A simple and quite powerful WordPress chart plugin to create and embed interactive charts & tables into your site.

== Description ==
 
<a href="http://themeisle.com/plugins/visualizer-charts-and-graphs-lite/" rel="nofollow">WordPress Visualizer plugin</a> is a simple, easy to use and quite powerful tool to create, manage and embed interactive charts & tables into your WordPress posts and pages.

The plugin uses Google Visualization API to add responsive & animated charts/diagrams, which support cross-browser compatibility (adopting VML for older IE versions) and cross-platform portability to iOS and new Android releases. Is the best Excel to WordPress solution who let's you insert charts to your wp site using a simple chart builder.

> **Time-saving features available in the Pro version:**
>
> * Import data from other charts
> * Easy edit the data using a live editor
> * 3 more chart types ( Combo, Timeline and Table chart )
> * Auto synchronize with your online file.
> * Create charts from your wordpress posts, pages,products or any other post_type.
> * Priority email support from the developer of the plugin
> * Support and updates for 1 year
>
> **[Learn more about Visualizer PRO](http://themeisle.com/plugins/visualizer-charts-and-graphs/)**


### 9 Chart types + 3 more in the pro version ###
This WordPress graph plugin provides a variety of charts that are optimized to address your WordPress data visualization needs. It is line chart,flow chart, area chart, bar chart, column chart, pie chart, geo chart, gauge chart, candlestick chart and scatter chart. These charts are based on pure HTML5/SVG technology (adopting VML for old IE versions), so no extra plugins are required. Adding these charts to your page can be done in a few simple steps. The premium version can act as a interactive WordPress Table plugin, with sorting capabilities.

### Flexible and customizable ###
Make the charts your own. Configure an extensive set of options to perfectly match the look and feel of your website. You can use Google Chart Tools with their default setting - all customization is optional and the basic setup is launch-ready. However, charts can be easily customizable in case your webpage adopts a style which is at odds with provided defaults. Every chart exposes a number of options that customize its look and feel.

### HTML5/SVG ###
Charts are rendered using HTML5/SVG technology to provide cross-browser compatibility (including VML for older IE versions) and cross platform portability to iPhones, iPads and Android. Your users will never have to mess with extra plugins or any software. If they have a web browser, they can see your charts.

*above descriptions were partially taken from Google Visualization API site*

The plugins works perfectly with the all <a href="http://justfreethemes.com" rel="nofollow">free</a> or <a href="http://www.codeinwp.com/blog/best-wordpress-themes/" rel="nofollow">premium WordPress themes</a>


 = See how Visualizer can integrate with your website  =

* [Create line chart ](https://demo.themeisle.com/visualizer/line-chart/)
* [Create pie chart ](https://demo.themeisle.com/visualizer/pie-chart/)
* [Create bar chart](https://demo.themeisle.com/visualizer/bar-chart/)
* [Create column chart](https://demo.themeisle.com/visualizer/column-chart/)
* [Create area chart](https://demo.themeisle.com/visualizer/area-chart/)
* [Create geo chart](https://demo.themeisle.com/visualizer/geo-chart/)
* [Create table chart](https://demo.themeisle.com/visualizer/table-chart/)
* [Create gauge chart](https://demo.themeisle.com/visualizer/gauge-chart//)
* [Create candlestick chart](https://demo.themeisle.com/visualizer/candlestick-chart/)
* [Create combo chart](https://demo.themeisle.com/visualizer/combo-chart/)
* [Create scatter chart](https://demo.themeisle.com/visualizer/scatter-chart/)
* [Create timeline chart](https://demo.themeisle.com/visualizer/timeline-chart/)


== Installation ==

1. Upload the files to the `/wp-content/plugins/visualizer/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Shortcode does not turn into graph =

Pay attention that to turn your shortcodes into graphs, your theme has to have `<?php wp_footer(); ?>` call at the bottom of **footer.php** file.

= How can I create a chart? =

http://docs.themeisle.com/article/597-create-chart

= How can I edit a chart? =

http://docs.themeisle.com/article/602-how-can-i-edit-a-chart

= How can I delete a chart? =

http://docs.themeisle.com/article/600-delete-chart

= How can I clone a chart? =

http://docs.themeisle.com/article/598-clone-chart

= How can I highlight a single bar? =

http://docs.themeisle.com/article/603-how-can-i-highlight-a-single-bar

= How can I populate chart series and data dynamically? =

http://docs.themeisle.com/article/605-how-can-i-populate-chart-series-and-data-dynamically

= How can I populate data from Google Spreadsheet? =

http://docs.themeisle.com/article/607-how-can-i-populate-data-from-google-spreadsheet

= How can i import content from another chart? =

http://docs.themeisle.com/article/609-how-can-i-import-content-from-another-chart

= How to export a chart? =

http://docs.themeisle.com/article/608-how-to-export-a-chart

= How can i edit the data manually? =

http://docs.themeisle.com/article/610-how-can-i-edit-the-data-manually

== Screenshots ==

1. Chart type selection
2. Chart data uploading
3. Chart options editing
4. Charts media library
5. Charts library

== Changelog ==
= 2.1.7 = 

* Updated sdk loading logic.



= 2.1.4 =
* Fixed issues with non-latin chars on CSV files to import.

= 2.1.2 =
* Fixed priority issue with wp_enqueue_media
* Added latest version of sdk

= 2.1.1 =
* Fixed charts bliking on some themes.

= 2.1.0 =
* Fixed geomap issue with apikey.
* Fixed responsive issues on tabbed interface and page builders.
* Added compatibility with premium import from posts/page feature.

= 2.0.4 =
* Fixed resize issue in the library page.

= 2.0.0 =
* Improved design and layout to support multiple datasources.
* Added new integrations in the Pro version.
* Added opt-in for tracking.

= 1.7.6 =
* Fixed issue when using the same shortcode multiple times on the same page.

= 1.7.5 =
* Removed footer banner upsell
* Fixed series settings issue
* Fixed issue with comas being used in numbers

= 1.7.2 =
* Improved charts responsive system

= 1.7.1 =
* Fixed grid lines error links

= 1.7.0 =
* Fixed responsive issues
* Fixed issues with zero margin values
* Fixed import issue

= 1.6.6 =
* Fixed charts resizing on tabbed content

= 1.6.5 =
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
* The first version of what wil be the best wp charts plugin.
