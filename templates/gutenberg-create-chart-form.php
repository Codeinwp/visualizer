<form id="gutenberg-create-chart-form" method="POST">
	<select name="type" class="gutenberg-create-chart-type">
	<?php
		foreach ( $types as $type => $label ) {
	?>
		<option value="<?php echo $type;?>"><?php echo $label; ?></option>
	<?php
		}
	?>
	</select>

	<div class="gutenberg-create-chart-form">
		<select name="source" class="gutenberg-create-chart-source">
			<option value="csv"><?php _e( 'Import data from file', 'visualizer' ); ?></option>
			<option value="url"><?php _e( 'Import data from URL', 'visualizer' ); ?></option>
			<option value="chart"><?php _e( 'Import from other chart', 'visualizer' ); ?></option>
			<option value="manual"><?php _e( 'Manual Data', 'visualizer' ); ?></option>
		</select>

		<span class="gutenberg-create-chart-source-attributes">
			<span data-source="csv" data-form-enctype="multipart/form-data">
				<input type="file" name="remote_data" class="visualizer-data-source-file">
			</span>
			<span data-source="url" data-form-enctype="application/x-www-form-urlencoded">
				<input type="url" name="remote_data" class="gutenberg-create-chart-remote" placeholder="<?php esc_html_e( 'Please enter the URL of CSV file', 'visualizer' ); ?>" class="visualizer-input">
			</span>
			<span data-source="chart" data-form-enctype="application/x-www-form-urlencoded">
				<select name="chart" class="gutenberg-create-chart-chart">
				<?php
					foreach ( $charts as $chart ) {
				?>
					<option value="<?php echo $chart['id'];?>"><?php echo $chart['name'];?></option>
				<?php
					}
				?>
				</select>
			</span>
			<span data-source="manual" data-form-enctype="application/x-www-form-urlencoded">
				<?php esc_html_e( 'You can enter the manual data by editing the chart once it has been created.', 'visualizer' ); ?>
			</span>
		</span>
	</div>

	<input type="button" name="create_chart" class="gutenberg-create-chart" value="<?php esc_html_e( 'Create Chart', 'visualizer' );?>">
</form>