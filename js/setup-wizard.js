/**
 * Plugin Name: Visualizer: Tables and Charts for WordPress
 * Plugin URI: https://themeisle.com/plugins/visualizer-charts-and-graphs/
 * Author: Themeisle
 *
 * @package Visualizer
 */
/* jshint unused:false */
jQuery(function ($) {

	/**
	 * Check if the user is in live preview mode.
	 * Live Preview is used in WordPress Playground to test the plugin.
	 * Use this check to deactivate any process that does not showcase the plugin features (e.g. subscription to the newsletter, etc.)
	 * 
	 * @type {boolean}
	 */
	const isLivePreview = window.location.search.includes('env=preview');

	/**
	 * Redirect to draft page after subscribe (optional).
	 * 
	 * @param {Object} postData - Post data. 
	 */
	const goToDraftPage = function ( postData = {} ) {
		postData ??= {};
		postData.action ??= 'visualizer_wizard_step_process';
		postData.security ??= visualizerSetupWizardData.ajax.security;
		postData.step ??= 'step_subscribe';

		// Subscribe the user using the email if provided. Redirect to the draft page.
		$.post(visualizerSetupWizardData.ajax.url, postData, function (res) {

			// Toggle the redirect popup.
			$('.redirect-popup').find('h3.popup-title').html(res.message);
			$('.redirect-popup').show();

			if (1 === res.status) {
				setTimeout(function () {
					window.location.href = res.redirect_to;
				}, 5000);
			} else {
				$('.redirect-popup').hide();
			}
			currentStep.find('.spinner').removeClass('is-active');
		}).fail(function () {
			$('.redirect-popup').hide();
			currentStep.find('.spinner').removeClass('is-active');
		});
	}

	var provideContent = function(id, stepDirection, stepPosition, selStep, callback) {
		// Import chart data.
		if ( 1 == id ) {
			var chartType = $(".vz-radio-btn:checked").val();
			var percentBarWidth = 0;
			var loaderDelay;
			$.ajax( {
				beforeSend: function() {
					loaderDelay = setInterval(function () {
						$('.vz-progress-bar').css({
							width: percentBarWidth++ + '%'
						});
					}, 1000);
				},
				type: 'POST',
				url: visualizerSetupWizardData.ajax.url,
				dataType: 'json',
				data: {
					action: 'visualizer_wizard_step_process',
					security: visualizerSetupWizardData.ajax.security,
					chart_type: chartType,
					step: 'step_2',
				},
				success: function (data) {
					clearInterval( loaderDelay );
					loaderDelay = setInterval(function () {
						$('.vz-progress-bar').css({
							width: percentBarWidth++ + '%'
						});
						if ( percentBarWidth >= 100 ) {
							if ( 1 === data.success ) {
								var importMessage = jQuery('[data-import_message]');
								importMessage
								.html( importMessage.data('import_message') )
								.addClass('import_message');

								$('#step-2').find('button.disabled').removeClass('disabled');
							} else if( 2 === data.status && '' !== data.message ) {
								$('#step-2')
								.find('.vz-error-notice')
								.html( '<p>' + data.message + '</p>' )
								.removeClass('hidden');
							} else {
								$('#smartwizard').smartWizard('reset');
							}
							$('.vz-progress-bar').css({
								width: 100 + '%'
							});
							$('.vz-progress').css({
								visibility: 'hidden'
							});
							clearInterval( loaderDelay );
						}
					}, 36 );
				},
				error: function() {
					$('#step-2').find('.vz-progress-bar').animate({ width: '0' });
				}
			} );
		}
		callback();
	}
	$("#smartwizard").smartWizard({
		transition: {
			animation: "fade", // Animation effect on navigation, none|fade|slideHorizontal|slideVertical|slideSwing|css(Animation CSS class also need to specify)
			speed: "400", // Animation speed. Not used if animation is 'css'
		},
		lang: {
			// Language variables for button
			next: visualizerSetupWizardData.nextButtonText,
			previous: visualizerSetupWizardData.backButtonText,
		},
		keyboard: {
			keyNavigation: false, // Enable/Disable keyboard navigation(left and right keys are used if enabled)
		},
		anchor: {
			enableNavigation: false, // Enable/Disable anchor navigation
			enableNavigationAlways: false, // Activates all anchors clickable always
		},
		style: {
			// CSS Class settings
			btnCss: "",
			btnNextCss: "btn-primary next-btn",
			btnPrevCss: "btn-light",
		},
		getContent: provideContent
	});

	// click to open accordion.
	$(".vz-accordion .vz-accordion-item .vz-accordion-item__button").on(
		"click",
		function () {
			var current_item = $(this).parents();
			$(".vz-accordion .vz-accordion-item .vz-accordion-item__content").each(
				function (i, el) {
					if ($(el).parent().is(current_item)) {
						$(el).prev().toggleClass("is-active");
						$(el).slideToggle();
						$(this).toggleClass("is-active");
					} else {
						$(el).prev().removeClass("is-active");
						$(el).slideUp();
						$(this).removeClass("is-active");
					}
				}
			);
		}
	);

	// Click to next step.
	$(document).on(
		"click",
		".btn-primary:not(.next-btn,.vz-create-page,.vz-subscribe)",
		function (e) {
			var stepNumber = $(this).data("step_number");
			
			switch (stepNumber) {
				case 1:
					if ($(".vz-radio-btn").is(":checked")) {
						$('#smartwizard').smartWizard('next');
					}
					break;
				case 3:
					if ( isLivePreview ) {
						$('#smartwizard').smartWizard('next');
					} else {
						var urlParams = new URLSearchParams(window.location.search);
						urlParams.set('preview_chart', Date.now());
						window.location.hash = "#step-3";
						window.location.search = urlParams;
					} 
					break;
				case 4:
					$('#step-4').find('.spinner').addClass('is-active');
					$('#step-4').find('.vz-error-notice').addClass('hidden');

					$.post(
						visualizerSetupWizardData.ajax.url,
						{
							action: 'visualizer_wizard_step_process',
							security: visualizerSetupWizardData.ajax.security,
							slug: 'optimole-wp',
							step: 'step_4',
						},
						function (response) {
							if (1 === response.status) {
								$('#smartwizard').smartWizard('next');
							} else if ( 'undefined' !== typeof response.message ) {
								$('#step-4')
								.find('.vz-error-notice')
								.html("<p>" + response.message + "</p>");
								$('#step-4').find('.vz-error-notice').removeClass('hidden');
							}
							$('#step-4').find('.spinner').removeClass('is-active');
						}
					).fail(function () {
						$('#step-4').find('.spinner').removeClass('is-active');
					});
					e.preventDefault();
					break;
				default:
					e.preventDefault();
					break;
			}
		}
	);

	// Create draft page.
	$(document).on("click", ".vz-create-page", function (e) {
		var _this = $(this);
		_this.next(".spinner").addClass("is-active");
		$.post(
			visualizerSetupWizardData.ajax.url,
			{
				action: "visualizer_wizard_step_process",
				security: visualizerSetupWizardData.ajax.security,
				step: "create_draft_page",
				basic_shortcode: $("#basic_shortcode").val(),
				add_basic_shortcode: $("#insert_shortcode").is(":checked"),
			},
			function (res) {
				if (res.status > 0) {
					if ( isLivePreview ) {
						goToDraftPage();
					} else {
						$("#smartwizard").smartWizard("next");
					}
				}
				_this.next(".spinner").removeClass("is-active");
			}
		).fail(function () {
			_this.next(".spinner").removeClass("is-active");
		});
		e.preventDefault();
	});

	// Enable performance feature.
	$("#step-4").on("change", "input:checkbox", function () {
		if ($(this).is(":checked")) {
			$(".skip-improvement").hide();
			$(".vz-wizard-install-plugin").show();
		} else {
			$(".skip-improvement").show();
			$(".vz-wizard-install-plugin").hide();
		}
	});

	// Step: 4 Skip and subscribe process.
	$(document).on( 'click', '.vz-subscribe', function (e) {
		var withSubscribe = $(this).data("vz_subscribe");
		var postData = {
			action: "visualizer_wizard_step_process",
			security: visualizerSetupWizardData.ajax.security,
			step: "step_subscribe",
		};
		var emailElement = $("#vz_subscribe_email");
		// Remove error message.
		emailElement.next(".vz-field-error").remove();

		if (withSubscribe) {
			var subscribeEmail = emailElement.val();
			var EmailTest = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
			var errorMessage = "";

			if ("" === subscribeEmail) {
				errorMessage = visualizerSetupWizardData.errorMessages.requiredEmail;
			} else if (!EmailTest.test(subscribeEmail)) {
				errorMessage = visualizerSetupWizardData.errorMessages.invalidEmail;
			}
			if ("" !== errorMessage) {
				$('<span class="vz-field-error">' + errorMessage + "</span>").insertAfter(emailElement);
				return false;
			}

			postData.email = subscribeEmail;
			postData.with_subscribe = withSubscribe;
		}
		var currentStep = $( '.vz-wizard-wrap .tab-pane:last-child' );
		currentStep.find(".spinner").addClass("is-active");

		goToDraftPage( postData );
		e.preventDefault();
	});

	// Click to copy.
	var clipboard = new ClipboardJS(".vz-copy-code-btn");
	clipboard.on("success", function (e) {
		var inputElement = $(e.trigger).prev("input:text");
	});

	// Remove disabled class from get started button.
	$("#step-1").on("change", "input:radio", function () {
		$("#step-1").find('[data-step_number="1"]').removeClass("disabled");
	});

	// Change button text.
	$(document).on("change", "#insert_shortcode", function () {
		if ($(this).is(":checked")) {
			$(".vz-create-page").html(
				visualizerSetupWizardData.draftPageButtonText.firstButtonText +
					' <span class="dashicons dashicons-arrow-right-alt"></span>'
			);
		} else {
			$(".vz-create-page").html(
				visualizerSetupWizardData.draftPageButtonText.secondButtonText +
					' <span class="dashicons dashicons-arrow-right-alt"></span>'
			);
		}
	});

	$('.vz-chart-list > ul').slick({
		dots: false,
		infinite: false,
		speed: 400,
		slidesToShow: 1,
		centerMode: false,
		variableWidth: true
	})
	.on( 'afterChange', function(event, slick, currentSlide) {
		// Disable next buttion.
		if( currentSlide === 4 ) {
			$('.slick-next').attr('disabled', true).css('pointer-events', 'none');
		} else {
			$('.slick-next').removeAttr('disabled').css('pointer-events', 'all');
		}
		// Disable prev buttion.
		if( currentSlide === 0 ) {
			$('.slick-prev').attr('disabled', true).css('pointer-events', 'none');
		} else {
			$('.slick-prev').removeAttr('disabled').css('pointer-events', 'all');
		}
	} );

	$(window).bind('pageshow', function() {
		if ( jQuery('.vz-chart-option input').is(':checked') ) {
			$('#step-1').find('button.disabled').removeClass('disabled');
		}
	});
});
