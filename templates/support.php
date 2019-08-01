<div id="viz-features" class="viz-settings">

	<?php
	$active_tab  = isset( $_REQUEST['tab'] ) ? sanitize_text_field( $_REQUEST['tab'] ) : 'help';
	$show_more = ! Visualizer_Module::is_pro();
	?>

	<div class="pro-features-header">
		<p class="logo">Visualizer: Tables and Charts Manager for WordPress</p>
		<span class="slogan">by <a
				href="https://themeisle.com/">ThemeIsle</a></span>
		<div class="header-btns">
			<?php if ( $show_more ) { ?>
			<a target="_blank" href="<?php echo Visualizer_Plugin::PRO_TEASER_URL; ?>" class="buy-now"><span
					class="dashicons dashicons-cart"></span> More features</a>
			<?php } ?>
		</div>
		<div class="clear"></div>
	</div>


	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=viz-support&tab=help' ) ); ?>"
		   class="nav-tab <?php echo $active_tab === 'help' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Support', 'visualizer' ); ?></a>
		<?php
		if ( $show_more ) {
			?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=viz-support&tab=more' ) ); ?>"
	   class="nav-tab <?php echo $active_tab === 'more' ? 'nav-tab-active' : ''; ?>"><?php _e( 'More Features', 'visualizer' ); ?></a>
			<?php
		}
		?>
	</h2>

	<div class="viz-features-content">
		<div class="viz-feature">
			<div id="feedzy_import_feeds" class="viz-feature-features">
					<?php
					switch ( $active_tab ) {
						case 'help':
							include_once VISUALIZER_ABSPATH . '/templates/docs.php';
							break;
						case 'more':
							include_once VISUALIZER_ABSPATH . '/templates/upsell.php';
							break;
					}
					?>
			</div>
		</div>
	</div>

</div>
