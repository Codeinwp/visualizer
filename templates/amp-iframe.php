<?php

$url             = "/visualizer-get-chart/$chart/";

$settings        = get_post_meta( $chart, Visualizer_Plugin::CF_SETTINGS, true );
$width           = $settings['width'];
$height          = $settings['height'];
if ( empty( $width ) ) {
	// this should typically be 100% but amp-iframe does not like % values
	$width      = 840;
}
if ( empty( $height ) ) {
	$height     = 400;
}

?>
<amp-iframe 
	width="<?php echo $width; ?>"
	height="<?php echo $height; ?>"
	layout="responsive"
	sandbox="allow-scripts allow-popups"
	frameborder="0"
	scrolling="no"
	src="<?php echo $url; ?>">
	<amp-img layout="fill" placeholder src="<?php echo VISUALIZER_ABSURL . '/images/blank.png'; ?>"></amp-img>
</amp-iframe>
