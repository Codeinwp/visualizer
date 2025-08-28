<?php
define( 'VISUALIZER_REST_VERSION', 1 );
// if the below is true, then the js/customization.js in the plugin folder will be used instead of the one in the uploads folder (if it exists).
// this is also used in Block.php
if ( ! defined( 'VISUALIZER_TEST_JS_CUSTOMIZATION' ) ) {
    define( 'VISUALIZER_TEST_JS_CUSTOMIZATION', false );
}

if ( ! defined( 'VISUALIZER_CSV_DELIMITER' ) ) {
    define( 'VISUALIZER_CSV_DELIMITER', ',' );
}
if ( ! defined( 'VISUALIZER_CSV_ENCLOSURE' ) ) {
    define( 'VISUALIZER_CSV_ENCLOSURE', '"' );
}
if ( ! defined( 'VISUALIZER_DEBUG' ) ) {
    define( 'VISUALIZER_DEBUG', false );
}

define( 'VISUALIZER_SKIP_CHART_TYPE_PAGE', true );

// if x and y features are required, this value should read x,y or x|y or x;y.
define( 'VISUALIZER_ENABLE_BETA_FEATURES', '' );

// the link to pre-build queries.
define( 'VISUALIZER_DB_QUERY_DOC_URL', 'https://docs.themeisle.com/article/970-visualizer-sample-queries-to-generate-charts' );
define( 'VISUALIZER_MAIN_DOC', 'https://docs.themeisle.com/category/657-visualizer' );
define( 'VISUALIZER_DOC_COLLECTION', 'https://docs.themeisle.com/search?collectionId=561ec249c69791452ed4bceb&query=#+visualizer' );
define( 'VISUALIZER_DEMO_URL', 'https://demo.themeisle.com/visualizer/#' );
define( 'VISUALIZER_CODE_SNIPPETS_URL', 'https://docs.themeisle.com/category/726-visualizer' );
define( 'VISUALIZER_SUBSCRIBE_API', 'https://api.themeisle.com/tracking/subscribe' );

// to redirect all themeisle_log_event to error log.
define( 'VISUALIZER_LOCAL_DEBUG', false );
define( 'VISUALIZER_SURVEY', true );
define( 'VISUALIZER_PRO', true );