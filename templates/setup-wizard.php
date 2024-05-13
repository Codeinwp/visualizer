<?php
/**
 * Setup wizard template.
 *
 * @category Visualizer
 * @package Templates
 */

$dashboard_url = add_query_arg(
	array(
		'action' => 'visualizer_dismiss_wizard',
		'status' => 0,
	),
	admin_url( 'admin.php' )
);

$chart_id           = ! empty( $this->wizard_data['chart_id'] ) ? (int) $this->wizard_data['chart_id'] : '';
$wp_optimole_active = is_plugin_active( 'optimole-wp/optimole-wp.php' );
$last_step_number   = 5;

// Check if we are in the Live Preview which is used to showcase only the plugin features without any other distractions. Ideal for marketing purposes (like Live Preview on WordPress.org or on the plugin's website)
$is_live_preview = ! empty( $_GET['env'] ) ? ( 'preview' === sanitize_key( $_GET['env'] ) ) : false;

?>
<div class="vz-wizard-wrap vz-wrap">
	<div class="vz-header--small">
		<div class="container">
			<div class="vz-logo">
				<div class="vz-logo-icon">
					<img src="<?php echo esc_url( VISUALIZER_ABSURL . 'images/visualizer-logo.svg' ); ?>" width="136" height="54" alt="">
				</div>
			</div>
			<div class="back-btn">
				<a href="<?php echo esc_url( $dashboard_url ); ?>" class="btn-link">
					<span class="dashicons dashicons-arrow-left-alt"></span><?php esc_html_e( 'Go to dashboard', 'visualizer' ); ?>
				</a>
			</div>
		</div>
	</div>
	<div class="container">
		<div class="vz-wizard">
			<div id="smartwizard" class="sw">
				<ul class="nav">
					<li class="nav-item">
						<a class="nav-link" href="#step-1">
							1
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#step-2">
							2
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#step-3">
							3
						</a>
					</li>
					<?php if ( ! $is_live_preview ) { ?>
						<?php if ( ! $wp_optimole_active ) : ?>
							<li class="nav-item">
								<a class="nav-link" href="#step-4">
									4
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#step-5">
									5
								</a>
							</li>
						<?php else : ?>
							<?php $last_step_number = 4; ?>
							<li class="nav-item">
								<a class="nav-link" href="#step-4">
									4
								</a>
							</li>
						<?php endif; ?>
					<?php } ?>
				</ul>
				<div class="tab-content">
					<div id="step-1" class="tab-pane" role="tabpanel" aria-labelledby="step-1">
						<div class="vz-accordion-item">
							<div class="vz-accordion-item__title">
								<div class="vz-accordion-item__button">
									<h2 class="h2 pb-8">
										<?php esc_html_e( 'Select a chart from the options available below', 'visualizer' ); ?></h2>
									<p class="p">
										<?php esc_html_e( 'Pro tip: Keep your requirements in mind and choose the best chart to meet them.', 'visualizer' ); ?>
									</p>
								</div>
							</div>
							<div class="vz-accordion-item__content border-top">
								<div class="vz-form-wrap">
									<div class="form-block pb-0">
										<div class="vz-chart-list">
											<ul>
												<li>
													<label class="vz-chart-option" for="vz-chart-1">
														<input type="radio" class="vz-radio-btn" id="vz-chart-1" name="visualizer[wizard_data][chart_type]" value="pie">
														<h3 class="h3"><?php esc_html_e( 'Pie/Donut chart', 'visualizer' ); ?></h3>
														<div class="img type-box-pie"></div>
														<div class="bg"></div>
													</label>
												</li>
												<li>
													<label class="vz-chart-option" for="vz-chart-2">
														<input type="radio" class="vz-radio-btn" id="vz-chart-2" name="visualizer[wizard_data][chart_type]" value="bar">
														<h3 class="h3"><?php esc_html_e( 'Bar chart', 'visualizer' ); ?></h3>
														<div class="img type-box-bar"></div>
														<div class="bg"></div>
													</label>
												</li>
												<li>
													<label class="vz-chart-option" for="vz-chart-3">
														<input type="radio" class="vz-radio-btn" id="vz-chart-3" name="visualizer[wizard_data][chart_type]" value="line">
														<h3 class="h3"><?php esc_html_e( 'Line chart', 'visualizer' ); ?></h3>
														<div class="img type-box-line"></div>
														<div class="bg"></div>
													</label>
												</li>
												<li>
													<label class="vz-chart-option" for="vz-chart-4">
														<input type="radio" class="vz-radio-btn" id="vz-chart-4" name="visualizer[wizard_data][chart_type]" value="tabular">
														<h3 class="h3"><?php esc_html_e( 'Table', 'visualizer' ); ?></h3>
														<div class="img type-box-tabular"></div>
														<div class="bg"></div>
													</label>
												</li>
												<li>
													<div class="vz-pro-option">
														<label class="vz-chart-option">
															<input type="radio" class="vz-radio-btn" id="vz-chart-5" readonly>
															<div class="vz-pro-label-wrap">
																<h3><?php esc_html_e( 'Geo chart', 'visualizer' ); ?></h3>
																<span class="pro-label"><?php esc_html_e( 'PRO', 'visualizer' ); ?></span>
															</div>
															<div class="img type-box-geo"></div>
															<div class="bg"></div>
														</label>
														<div class="pro-overlay">
															<div class="pro-box">
																<a href="<?php echo esc_url( tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'setupWizard' ) ); ?>"
																	class="btn btn-secondary" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'visualizer' ); ?></a>
															</div>
														</div>
													</div>
												</li>
												<li>
													<div class="vz-power-pro">
														<h3 class="h3"><?php esc_html_e( 'Discover the power of PRO!', 'visualizer' ); ?></h3>
														<ul>
															<li><?php esc_html_e( '11 more chart types', 'visualizer' ); ?></li>
															<li><?php esc_html_e( 'Private charts', 'visualizer' ); ?></li>
															<li><?php esc_html_e( 'Auto-sync with online files', 'visualizer' ); ?></li>
															<li>
																<?php esc_html_e( 'Frontend Actions(Print, Export, Copy, Download', 'visualizer' ); ?>)
															</li>
															<li><?php esc_html_e( 'Create charts from WordPress tables', 'visualizer' ); ?></li>
														</ul>
														<a href="<?php echo esc_url( tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'setupWizard' ) ); ?>" class="btn btn-secondary" target="_blank"><?php esc_html_e( 'View more features', 'visualizer' ); ?></a>
													</div>
												</li>
											</ul>
										</div>
									</div>
									<div class="form-block">
										<button class="btn btn-primary disabled" data-step_number="1"><?php esc_html_e( 'Save And Continue', 'visualizer' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="step-2" class="tab-pane" role="tabpanel" aria-labelledby="step-2">
						<div class="vz-accordion-item">
							<div class="vz-accordion-item__title">
								<div class="vz-accordion-item__button">
									<h2 class="h2 pb-8"><?php esc_html_e( 'You\'re almost done!', 'visualizer' ); ?></h2>
									<p class="p"><?php esc_html_e( 'We use demo data during the import process, but don\'t worry; you can customize it later.', 'visualizer' ); ?></p>
								</div>
							</div>
							<div class="vz-accordion-item__content">
								<div class="vz-form-wrap">
									<div class="form-block" style="padding-top: 8px">
										<div class="vz-error-notice notice notice-error hidden"></div>
										<div class="pb-30">
											<div class="vz-shortcode-preview-box">
												<div class="vz-shortcode-preview-title border-0">
													<div class="icon">
														<img src="<?php echo esc_url( VISUALIZER_ABSURL . 'images/database-icon.png' ); ?>" alt="">
													</div>
													<div class="txt" style="width: 100%;">
														<h4 class="h4 pb-4"><?php esc_html_e( 'Importing demo data', 'visualizer' ); ?></h4>
														<p class="p" data-import_message="<?php esc_attr_e( 'Done! Demo data has been successfully imported.', 'visualizer' ); ?>"><?php esc_html_e( 'Hold on! we are importing demo data for your selected chart', 'visualizer' ); ?></p>
													<div class="vz-progress" style="margin-top: 4px;">
														<div class="vz-progress-bar"></div>
													</div>
													</div>
												</div>
											</div>
										</div>
										<p class="help-text help-text-primary pb-16"><?php esc_html_e( 'Import data from other charts, WordPress, databases, or manual data entries using Visualizer', 'visualizer' ); ?> <a href="<?php echo esc_url( tsdk_utmify( Visualizer_Plugin::PRO_TEASER_URL, 'setupWizard' ) ); ?>" target="_blank"><?php esc_html_e( 'Premium version', 'visualizer' ); ?></a></p>
									</div>
									<div class="form-block"><button class="btn btn-primary disabled" data-step_number="3"><?php esc_html_e( 'Continue', 'visualizer' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="step-3" class="tab-pane" role="tabpanel" aria-labelledby="step-3">
						<div class="vz-accordion-item">
							<div class="vz-accordion-item__title">
								<div class="vz-accordion-item__button">
									<h2 class="h2 pb-8"><?php esc_html_e( 'Insert a chart into the draft page', 'visualizer' ); ?></h2>
									<p class="p"><?php esc_html_e( 'Create a draft page featuring a Visualizer chart with just one click.', 'visualizer' ); ?></p>
								</div>
							</div>
							<div class="vz-accordion-item__content border-top">
								<div class="vz-form-wrap">
									<div class="form-block">
										<div class="vz-accordion">
											<div class="vz-accordion-item vz-features-accordion mb-0">
												<div class="vz-shortcode-preview-box">
													<div class="vz-accordion-item__title vz-accordion-checkbox__title">
														<div class="vz-checkbox">
															<input type="checkbox" class="vz-checkbox-btn" id="insert_shortcode" checked <?php echo $is_live_preview ? 'disabled' : ''; ?>>
														</div>
														<button type="button" class="vz-accordion-item__button">
															<div class="vz-accordion__step-title h4 pb-4"><?php esc_html_e( 'Create a draft page', 'visualizer' ); ?></div>
															<p class="help-text"><?php esc_html_e( 'We will automatically create a draft page with Visualizer chart for preview', 'visualizer' ); ?></p>
															<div class="vz-accordion__icon"><span class="dashicons dashicons-arrow-down-alt2"></span>
															</div>
														</button>
													</div>
													<div class="vz-accordion-item__content">
														<div class="vz-shortcode-preview-content">
															<?php $shortcode = '[visualizer id="{{chart_id}}" class=""]'; ?>
															<?php if ( $is_live_preview ) { ?>
																<p class="pb-16"><?php esc_html_e( 'Charts are added in the page/post via Gutenberg Blocks.', 'visualizer' ); ?></p>
																<p class="pb-16"><?php esc_html_e( 'Alternatively, you can use a shortcode with the following structure:', 'visualizer' ); ?></p>
															<?php } else { ?>
																<h4 class="h4 pb-16"><?php esc_html_e( 'Chart preview', 'visualizer' ); ?></h4>
																<div class="vz-chart pb-30">
																	<?php
																	if ( ! empty( $_GET['preview_chart'] ) ) {
																		$shortcode = str_replace( '{{chart_id}}', $chart_id, $shortcode );
																		echo do_shortcode( $shortcode );
																	}
																	?>
																</div>
															<?php } ?>
															<div class="vz-code-box">
																<input type="text" id="basic_shortcode" value="<?php echo esc_attr( $shortcode ); ?>" readonly>
																<button type="button" class="vz-copy-code-btn" data-clipboard-target="#basic_shortcode"><?php esc_html_e( 'click to copy', 'visualizer' ); ?> <img src="<?php echo esc_url( VISUALIZER_ABSURL . 'images/copy.svg' ); ?>" alt="">
																</button>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-block">
										<button class="btn btn-primary vz-create-page"><?php esc_html_e( 'Save And Continue', 'visualizer' ); ?> <span class="dashicons dashicons-arrow-right-alt"></span></button>
										<span class="spinner"></span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php if ( ! $wp_optimole_active && ! $is_live_preview ) { ?>
						<div id="step-4" class="tab-pane" role="tabpanel" aria-labelledby="step-4">
							<div class="vz-accordion-item">
								<div class="vz-accordion-item__title">
									<div class="vz-accordion-item__button">
										<h2 class="h2 pb-8"><?php esc_html_e( 'Extra Features', 'visualizer' ); ?></h2>
										<p class="p"><?php esc_html_e( 'We\'re confident you\'ll appreciate the improvements. If not, you can remove them at any time.', 'visualizer' ); ?></p>
									</div>
								</div>
								<div class="vz-accordion-item__content border-top">
									<div class="vz-form-wrap">
										<div class="form-block">
											<div class="vz-error-notice notice notice-error hidden"></div>
											<div class="vz-accordion">
												<div class="vz-accordion-item vz-features-accordion mb-0">
													<div class="vz-accordion-item__title vz-accordion-checkbox__title">
														<div class="vz-checkbox">
															<input type="checkbox" class="vz-checkbox-btn" checked>
														</div>
														<button type="button" class="vz-accordion-item__button">
															<div class="vz-accordion__step-title h4 pb-4"><?php esc_html_e( 'Enable perfomance features for your website.', 'visualizer' ); ?></div>
															<p class="help-text"><?php esc_html_e( 'Optimize and speed up your site with our trusted add-on—it\'s free!', 'visualizer' ); ?></p>
															<div class="vz-accordion__icon"><span class="dashicons dashicons-arrow-down-alt2"></span>
															</div>
														</button>
													</div>
													<div class="vz-accordion-item__content">
														<div class="vz-features-list">
															<ul>
																<li>
																	<div class="icon">
																		<img src="<?php echo esc_url( VISUALIZER_ABSURL . 'images/boost-logo.png' ); ?>" width="37" height="30" alt="">
																	</div>
																	<div class="txt">
																		<div class="h4 pb-4"><?php esc_html_e( 'Boost your website speed', 'visualizer' ); ?> <span class="pro-label free-label"><?php esc_html_e( 'Free', 'visualizer' ); ?></span></div>
																		<p class="help-text"><?php esc_html_e( 'Improve your website speed and images by 80% with', 'visualizer' ); ?> <a href="<?php echo esc_url( tsdk_utmify( 'https://optimole.com/', 'VisualizerSetupWizard' ) ); ?>" target="_blank"><?php esc_html_e( 'Optimole', 'visualizer' ); ?></a></p>
																	</div>
																</li>
															</ul>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="form-block">
											<button class="btn btn-primary vz-wizard-install-plugin" data-step_number="4"><?php esc_html_e( 'Improve now', 'visualizer' ); ?>
											<button class="btn btn-primary next-btn skip-improvement" style="display: none;"><?php esc_html_e( 'Skip Improvement', 'visualizer' ); ?></button>
											<span class="spinner"></span>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					<?php if ( ! $is_live_preview ) { ?>
						<div id="step-<?php echo esc_attr( $last_step_number ); ?>" class="tab-pane" role="tabpanel" aria-labelledby="step-5">
							<div class="vz-accordion-item">
								<div class="vz-accordion-item__title">
									<div class="vz-accordion-item__button">
										<h2 class="h2 pb-8"><?php esc_html_e( 'Updates, tutorials, special offers, and more', 'visualizer' ); ?></h2>
										<p class="p"><?php esc_html_e( 'Get exclusive access to the Visualizer newsletter.', 'visualizer' ); ?></p>
									</div>
								</div>
								<div class="vz-accordion-item__content border-top">
									<div class="vz-form-wrap">
										<div class="form-block">
											<div class="vz-newsletter-wrap">
												<div class="vz-newsletter">
													<p class="p pb-30"><?php esc_html_e( 'Share your email with us! That way, we can keep you updated with exciting product news, handy tutorials, exclusive offers, and lots more awesome content.', 'visualizer' ); ?></p>
													<div class="vz-form-group">
														<input type="email" class="form-control" id="vz_subscribe_email" placeholder="<?php echo esc_attr( get_bloginfo( 'admin_email' ) ); ?>">
													</div>
												</div>
												<div class="vz-newsletter-img">
													<img src="<?php echo esc_url( VISUALIZER_ABSURL . 'images/newsletter-img.png' ); ?>" alt="">
												</div>
											</div>
										</div>
										<div class="form-block">
											<div class="vz-btn-group">
												<button class="btn btn-primary vz-subscribe" data-vz_subscribe="true"><?php esc_html_e( 'Send Me Access', 'visualizer' ); ?></button>
												<button class="btn btn-outline-primary vz-subscribe" data-vz_subscribe="false"><?php esc_html_e( 'Skip, Don&#x92;t give me access', 'visualizer' ); ?></button>
												<span class="spinner"></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<div class="redirect-popup">
		<div class="redirect-popup-box">
			<div class="icon">
				<svg width="5" height="23" viewBox="0 0 5 23" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path
						d="M0.6875 23V6.4375H4.57812V23H0.6875ZM4.15625 3.65625C3.73958 4.0625 3.22917 4.26562 2.625 4.26562C2.02083 4.26562 1.51042 4.0625 1.09375 3.65625C0.677083 3.23958 0.46875 2.73958 0.46875 2.15625C0.46875 1.5625 0.677083 1.0625 1.09375 0.65625C1.51042 0.239583 2.02083 0.03125 2.625 0.03125C3.22917 0.03125 3.73958 0.239583 4.15625 0.65625C4.58333 1.0625 4.79688 1.5625 4.79688 2.15625C4.79688 2.73958 4.58333 3.23958 4.15625 3.65625Z"
						fill="#39C3D2" fill-opacity="0.75" />
				</svg>
			</div>
			<h3 class="h3 popup-title"></h3>
			<div class="redirect-loader">
				<img src="<?php echo esc_url( VISUALIZER_ABSURL . 'images/mask-loader.png' ); ?>" width="45" height="45"
					alt="loader">
			</div>
		</div>
	</div>
</div>
