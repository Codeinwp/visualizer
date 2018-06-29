<?php
   $url             = "/visualizer-get-chart/$chart";
?>
<amp-iframe 
	width="500"
	height="500"
	layout="responsive"
	sandbox="allow-scripts allow-popups"
	frameborder="0"
	src="<?php echo $url; ?>">
	<amp-img placeholder></amp-img>
</amp-iframe>
