
 ### v3.4.6 - 2020-08-11 
 **Changes:** 
 * - [Feat] Show chart ID in the chart library
* - [Fix] Compatibility with WP 5.5
* - [Fix] Google charts: Series number format not applying in the Gutenberg editor
* - [Fix] Google Table chart does not display chart if boolean values are specified
* - [Fix] Duplicated enque for jsapi loader
 
 ### v3.4.5 - 2020-07-08 
 **Changes:** 
 * [Feat] New Google Table Charts
* [Feat] Option for lazy loading Google Charts
* [Feat] Option to easily copy chart shortcode code
* [Fix] Remove Inside the Chart option for the legend position for Google Pie charts
 
 ### v3.4.4 - 2020-06-16 
 **Changes:** 
 * [Feat] Option to download charts as .png images
* [Fix] Make UI more intuitive when a chart is missing in the editor
* [Fix] Clicking Copy chart shows the Copied message multiple times
* [Fix] Conflict with Modern Events Calendar plugin
* [Fix] Chart size ( width and height ) options not working for ChartJS charts
* [Fix] Resizing the window causes annotation-based charts to throw an error
* [Fix] Remove Inside the Chart option as legend position for Google Pie charts
* [Fix] Clicking Advanced Options panel breaks Bubble chart
* [Fix] Missing posts revisions on chart update
 
 ### v3.4.3 - 2020-05-04 
 **Changes:** 
 * [Feat] Improved chart creation UX
* [Feat] New option to control the number of charts per page in the Charts Library
* [Feat] New option for filtering/ordering charts in the Charts Library
* [Feat] Support for custom codes for the boolean data type in Table charts
* [Fix] Support for displaying apostrophes in charts
* [Fix] Visualizer button layout in the Classic block
* [Fix] Bulk activation of plugin aborts activation of subsequent plugins
 
 ### v3.4.2 - 2020-02-17 
 **Changes:** 
 * New Cypress tests for the Gutenberg block
 
 ### v3.4.1 - 2020-02-14 
 **Changes:** 
 * [Fix] Insert chart button in the classic block
* [Fix for Pro version] Import from chart did not work
 
 ### v3.4.0 - 2020-02-13 
 **Changes:** 
 * [Feat] Support for authentication for JSON import
* [Feat] New chart type: Bubble
* [Feat] Combine one-time import and schedule import into a single control for an online .csv file import
* [Feat] Add support for annotations and other roles
* [Feat] For every chart show the last updated date and any error that exists
* [Feat] Tested up to WP 5.3
* [Fix] When new data is imported using csv/url, the manual editor still show old data
* [Fix] Having SCRIPT_DEBUG on causes issues in real time update of charts
* [Fix] Table chart: Error appears when trying to import from JSON
* [Fix] PHP Fatal error: Uncaught Error: Cannot unset string offsets
* [Fix] Long responsive table can overflow on smaller screens
 
 ### v3.3.4 - 2019-11-15 
 **Changes:** 
 * Fix issue with table chart not loading in the block editor
 
 ### v3.3.3 - 2019-11-12 
 **Changes:** 
 * Tested upto WordPress 5.3
 
 ### v3.3.2 - 2019-10-03 
 **Changes:** 
 * Add support for Dataset schema
* Horizontal Axis formatting should apply to tooltips
 
 ### v3.3.1 - 2019-09-28 
 **Changes:** 
 * Increase minimum requirement to PHP 5.6
* Fixed issue with loading customization.js on multisites
* Fixed issue with manually editing a remotely loaded chart
* Fixed issues with cloning
* Fixed issues with ChartJS assigning default colors
* Fix security issues in block editor
 
 ### v3.3.0 - 2019-08-14 
 **Changes:** 
 * Add support for ChartJS
* Add alpha color picker for supported charts
* Fix issue with some options of DataTable
* Include DataTable charts in block editor
* Fix issue with import from JSON not working with some sources
* Add menu and onboarding page
* Fix issue with frontend action checkboxes
* Improve UX in advanced settings
 
 ### v3.2.1 - 2019-05-05 
 **Changes:** 
 * Fix issue with async loading of scripts
 
 ### v3.2.0 - 2019-05-03 
 **Changes:** 
 * Add support for charts in AMP requests
* Add support to show charts from JSON/REST endpoints
* Fix loading of Google Visualization javascript files
* Add simple editors for editing chart data
* Tested up to WP 5.2
 
 ### v3.1.3 - 2019-02-24 
 **Changes:** 
 * Fix issue with changing column settings of the last column in table chart
* Add support for query language to get subset of data from Google Spreadsheet
* Fix conflict with jquery 3.3.x
* Migrated PHPExcel to PhpSpreadsheet
* Front end action 'print' should print the chart and fall back to printing the data
* Fix issue with table chart not showing in IE
* Fix issue with multiple instances of same chart not showing
* Fix issue with date type column does not work with Combo charts
* Tested with WP 5.1
 
 ### v3.1.2 - 2018-12-06 
 **Changes:** 
 * Fix bug "Warning: A non-numeric value encountered"
* Tested with WP 5.0
 
 ### v3.1.1 - 2018-12-05 
 **Changes:** 
 * Fix issue with Gutenberg support
* Fix issue with loading new Table chart
* Fix options that don't work correctly with some charts
 
 ### v3.1.0 - 2018-12-03 
 **Changes:** 
 * Add Table chart
* Fix date format in sample files
 
 ### v3.0.12 - 2018-10-11 
 **Changes:** 
 * Added filter to enable users to change schedule of charts.
* Fixed bug with line chart with timeofday column.
* Fixed bug with scheduled charts that sometimes did not show updated data.
* Javascript can be customized on a per user basis that will not be wiped out on update.
 
 ### v3.0.11 - 2018-08-15 
 **Changes:** 
 * Fixed issue with the Series Settings options for the Table Chart
* Fixed issue with chart showing "Table has no columns" with remote sources
 
 ### v3.0.10 - 2018-07-20 
 **Changes:** 
 * Fixed problem with chart reverting to the default values
* Fixed problem with Boolean column type
* Fixed problem with the Geo chart type not saving colors options
 
 ### v3.0.9 - 2018-07-12 
 **Changes:** 
 * New chart title option for the back-end of the charts that don't allow a title on the front-end
* Store the png images of the charts in a global array that can be used in JS
* Added options for charts animations
 
 ### v3.0.8 - 2018-06-27 
 **Changes:** 
 * Added revision support for the chart post type
* Added both % and Value to the Pie Slice
* Use the blog locale for Visualizer's options
* Fixed issue with data being fetched from the remote source every single time the chart was shown
* Fixed issue with scheduled charts not being updated if one of the scheduled charts is deleted
 
 ### v3.0.7 - 2018-03-26 
 **Changes:** 
 * Adds insert button in chart library.
* Remove frontend assets where they are not needed.
* Improve non-English charts compatibility. 
* Adds a filter to change charts locale.
 
 ### v3.0.6 - 2018-02-27 
 **Changes:** 
 * Fix UTF-8 support while saving the data. 
* Improve editing experience.  
* Improves compatibility with Premium version. 
* Adds chart button into TinyMCE editor.
 
 ### v3.0.5 - 2018-01-05 
 **Changes:** 
 * Fix chart rendering bug in firefox.
* Fix review notification.
 
 ### v3.0.4 - 2017-11-27 
 **Changes:** 
 * Fix for review message notification.
 
 ### v3.0.3 - 2017-11-16 
 **Changes:** 
 * Adds compatibility with WordPress 4.9.
 
 ### v3.0.2 - 2017-10-10 
 **Changes:** 
 * Fix dependency for composer dependencies.
 
 ### v3.0.1 - 2017-10-06 
 **Changes:** 
 * Improved compatibility with various theme and plugins.
* Fix for chart type selection when creation from media popup.
 
 ### v3.0.0 - 2017-09-05 
 **Changes:** 
 * Adds support manual configuration according to Google Visualization API.
* Improves compatibility with more features from the pro version.
 
 ### v2.2.0 - 2017-08-16 
 **Changes:** 
 * Added custom number format for pie chart.
* Added frontend actions buttons ( Print, Export to CSV, Export to Excel, Copy)
 
 ### v2.1.9 - 2017-07-10 
 **Changes:** 
 * Fixed display error with hex color.
 
 ### v2.1.8 - 2017-07-03 
 **Changes:** 
 * Added chart title into library. 
* Fixed SDK issues with dashboard widget.
 
 ### v2.1.7 - 2017-06-17 
 **Changes:** 
 * Updated sdk loading logic.
 
 ### v2.1.6 - 2017-06-07 
 **Changes:** 
 - Fixed non-latin chars render.
 
 ### v2.1.5 - 2017-06-02 
 **Changes:** 
 - Fixed markup issue which caused issue on library loading.
 
 ### v2.1.4 - 2017-06-02 
 **Changes:** 
 - Added support for non Latin characters
 
 ### v2.1.3 - 2017-05-31 
 **Changes:** 
  
 ### v2.1.2 - 2017-05-30 
 **Changes:** 
 - Fixed priority issue with wp_enqueue_media
- Added latest version of sdk
 
 ### v2.1.1 - 2017-05-16 
 **Changes:** 
 - Fixed blinking chart issue.
 
 ### v2.1.0 - 2017-05-12 
 **Changes:** 
 - Fixed responsive issues on pagebuilders.
- Fixed chart rendering if container is not found.
- Added api key for geotype map.
- Added compatibility with import from post_types.
 
 ### v2.0.4 - 2017-04-21 
 **Changes:** 
 - Fixed library rendering issue. 
- Added git tag check for deployment. 
- Added wraith for visual regression testing.
 
 ### v2.0.3 - 2017-04-13 
 **Changes:** 
 - Fixed SVN deploy script. 
- Fixed release version.
 
 ### v2.0.2 - 2017-04-13 
 **Changes:** 
 - Fixed svn deploy script.
 
 ### v2.0.2 - 2017-04-13 
 **Changes:** 
 - Fixed SVN deploy
 
 ### v2.0.1 - 2017-04-13 
 **Changes:** 
 - Fixed vendor include.
 
### 2.0.0 - 13/04/2017
**Changes:** 
- Improved UI of the builder.
- Added compatibility with the new options in the pro version.
- Added new travis stack.
- Added optin for tracking.

### 1.7.6 - 20/01/2017
**Changes:** 
- Fixed issue when using the same shortcode multiple times on the same page.

### 1.7.5 - 14/12/2016
**Changes:** 
- Removed footer banner upsell
- Fixed series settings issue
- Fixed issue with comas being used in numbers

### 1.7.2 - 23/11/2016
**Changes:** 
- Improved responsive mechanism

### 1.7.1 - 20/10/2016
**Changes:** 
- Fixed bad link on grid lines

### 1.7.0 - 20/10/2016
**Changes:** 
- Release 1.7.0

### 1.6.6 - 14/09/2016
**Changes:** 
- Fixed issue on chart resizing on tabbed system

### 1.6.5 - 09/09/2016
**Changes:** 
- Fixed responsive issue
- Fixed no axis color for line and bar charts

### 1.6.0 - 19/08/2016
**Changes:** 
- Fixed potential XSS security bug
- Added support for charts in the media library
- Removed pointer for the pro version
- Added option to show legend on the left side

### 1.5.6 - 05/07/2016
**Changes:** 
- Added support for 3 more chart types
- Fixed issue with charts not saving

### 1.5.5 - 31/05/2016
**Changes:** 
- Added export feature for charts
- Fixed issue with hovering on charts


### 1.5.4 - 04/03/2016

 Changes: 


 * free search over charts

free search over charts
 * free search over charts

free search over charts
 * Merge pull request #69 from abaicus/development

!!!Changed search styles
 * Added free search text over charts
 * Merge pull request #68 from abaicus/development

Fixed Style Issues
 * Merge branch 'production' into development

# Conflicts:
#	classes/Visualizer/Plugin.php
