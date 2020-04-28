<?php
	$is_pro = Visualizer_Module::is_pro();
?>
	<div class="pro-feature">
		<div class="pro-feature-inner">
			<div class="pro-feature-features">
				<h2>Welcome to Visualizer!</h2>
				<p>Visualizer lets you easily create and customize responsive tables and charts so you can share your data effectively to your users.</p>
				<p>With this version, you can already:</p>

				<ul style="list-style: disc; list-style-position: inside;">
					<li>Create an unlimited number of tables and charts</li>
					<li>Manually edit the data used by any graphs and tables</li>
					<li>Import data from a URL or file</li>
					<li>Fully customize the design and behavior of your tables and charts</li>
					<?php if ( $is_pro ) { ?>
					<li>Schedule regular updates to your charts</li>
					<li>Import from the database or other charts</li>
					<?php } ?>
				</ul>

				<?php if ( ! $is_pro ) { ?>
				<p>We have many more features and charts, and offer email & chat support if you purchase our <a href="<?php echo Visualizer_Plugin::PRO_TEASER_URL; ?>" target="_blank">Pro Version</a>.</p>
				<?php } ?>

				<p>Ready to begin? Let's <a href="<?php echo admin_url( 'admin.php?page=' . Visualizer_Plugin::NAME . '&vaction=addnew' ); ?>">create a chart</a> or <a href="<?php echo VISUALIZER_DEMO_URL; ?>" target="_blank">view a demo</a>!
			</div>
		</div>
	</div>
	<div class="clear"></div>

	<div class="pro-feature">
		<div class="pro-feature-inner">
			<div class="pro-feature-features">
				<h2>Documentation</h2>
				<p>To get started with Visualizer, we recommend you first bookmark our main documentation page <a href="<?php echo VISUALIZER_MAIN_DOC; ?>" target="_blank">here</a>.</p>

				<p>
					Notably, you could take a look at this first introductory tutorial: <a href="https://docs.themeisle.com/article/597-create-chart" target="_blank">How to create my first chart</a>.
				</p>

				<p>If you prefer learning through video, this could prove useful. It is a little dated however.</p>
			</div>
			<div class="pro-feature-image">
				<iframe width="500" height="235" src="https://www.youtube.com/embed/hQO_evnb_tQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>


	<div class="pro-feature">
		<div class="pro-feature-inner">
			<div class="pro-feature-features">
				<h2>Need help?</h2>
				<?php if ( ! $is_pro ) { ?>
					<p>Our support channel for users of the free version can be found <a href="https://wordpress.org/support/plugin/visualizer/" target="_blank">here</a>.</p>
				<?php } else { ?>
					<p>Contact our premium support by logging in to your account <a href="https://store.themeisle.com/login/" target="_blank">here</a>.</p>
				<?php } ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
