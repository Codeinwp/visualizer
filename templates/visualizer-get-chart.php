<style>
	#wpadminbar, .visualizer-actions { display: none; }
</style>

<?php
	wp_head();
	$chart = get_query_var( '_chart' );
	echo do_shortcode( "[visualizer id=\"$chart\"]" );
	wp_footer();
?>
