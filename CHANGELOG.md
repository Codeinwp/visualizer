
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
