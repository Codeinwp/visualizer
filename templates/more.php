<?php
if ( ! Visualizer_Module::is_pro() ) {
	?>
	<div class="pro-feature">
		<div class="pro-feature-inner">
			<div class="pro-feature-features">
				<h2>More charts!</h2>
				<p>Gain access to 6 more charts right away, and more in the future. So far these include the gauge, candlestick, timeline, combo, polar area and radar charts.</p>
				<p>Of course, these are fully customizable!</p>
			</div>
			<div class="pro-feature-image"><img src="<?php echo VISUALIZER_ABSURL; ?>/images/pro/more_charts.png"></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>

	<div class="pro-feature">
		<div class="pro-feature-inner">
		<div class="pro-feature-features">
			<h2>Excel-like data editor</h2>
			<p>Use our excel-like data editor to configure your charts, unlimited value, and you can paste your data directly from excel!</p>

		</div>
			<div class="pro-feature-image"><img src="<?php echo VISUALIZER_ABSURL; ?>/images/pro/excel.png"></div>
		<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>

	<div class="pro-feature">
		<div class="pro-feature-inner">
		<div class="pro-feature-features">
			<h2>Premium support</h2>
			<p>Get timely help from our trained representatives, they will answer all your questions, and even help you setup your instance.</p>
			<p>With our Agency plan, you'll even get to chat with them in real time and get immediate answers (within regular business hours).</p>
		</div>
			<div class="pro-feature-image"><img src="<?php echo VISUALIZER_ABSURL; ?>/images/pro/support.png"></div>
		<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>

	<?php
}
if ( ! apply_filters( 'visualizer_is_business', false ) ) {
	?>

	<div class="pro-feature">
		<div class="pro-feature-inner">
		<div class="pro-feature-features">
			<h2>Import any data from your database!</h2>
			<p>Do you want to create a chart based on custom queries? Or display data about WordPress statistics? Do you want to import data periodically (every day, every hour, etc.) ?</p>
			<p>With Pro you can do all of these, and much more. <a href="<?php echo Visualizer_Plugin::PRO_TEASER_URL; ?>">Visit our site to know more.</a></p>
		</div>
			<div class="pro-feature-image"><img src="<?php echo VISUALIZER_ABSURL; ?>/images/pro/import.png"></div>
		<div class="clear"></div>
		</div>
	</div>

	<?php
}
?>
