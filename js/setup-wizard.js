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
	const emailRegex = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;

	var showNewsletterError = function ($input, message) {
		var $wrap = $input.closest('.vz-option-input');
		$wrap.next('.vz-field-error').remove();
		if (message) {
			$wrap.after('<span class="vz-field-error">' + message + '</span>');
		}
	};

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
		var $currentStep = $('#step-3');

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
			$currentStep.find('.spinner').removeClass('is-active');
		}).fail(function () {
			$('.redirect-popup').hide();
			$currentStep.find('.spinner').removeClass('is-active');
		});
	}

	var runImport = function () {
		var chartType = $(".vz-radio-btn:checked").val();
		var percentBarWidth = 0;
		var loaderDelay;
		var $step = $('#step-1');
		var $status = $step.find('.vz-import-status');
		var $progress = $status.find('.vz-progress-bar');
		var $message = $status.find('[data-import_message]');
		var $error = $status.find('.vz-error-notice');
		var $spinner = $step.find('.spinner');
		var $button = $step.find('[data-step_number="1"]');

		$status.show();
		$progress.css({ width: '0' });
		if ($message.length) {
			$message
				.text($message.data('import_initial'))
				.removeClass('import_message');
		}
		$error.addClass('hidden').empty();
		$spinner.addClass('is-active');
		$button.addClass('disabled');

		loaderDelay = setInterval(function () {
			percentBarWidth = Math.min(percentBarWidth + 4, 90);
			$progress.css({ width: percentBarWidth + '%' });
		}, 120);

		$.ajax({
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
				clearInterval(loaderDelay);
				$progress.css({ width: '100%' });

				if (1 === data.success) {
					if (data.chart_id) {
						var $shortcode = $('#basic_shortcode');
						if ($shortcode.length) {
							$shortcode.val(
								$shortcode.val().replace('{{chart_id}}', data.chart_id)
							);
						}
					}
					if ($message.length) {
						$message
							.text($message.data('import_message'))
							.addClass('import_message');
					}

					setTimeout(function () {
						$spinner.removeClass('is-active');
						$('#smartwizard').smartWizard('next');
					}, 200);
				} else if (2 === data.status && '' !== data.message) {
					$error.html('<p>' + data.message + '</p>').removeClass('hidden');
					$spinner.removeClass('is-active');
					$button.removeClass('disabled');
				} else {
					$spinner.removeClass('is-active');
					$button.removeClass('disabled');
					$('#smartwizard').smartWizard('reset');
				}
			},
			error: function () {
				clearInterval(loaderDelay);
				$progress.css({ width: '0' });
				$spinner.removeClass('is-active');
				$button.removeClass('disabled');
			}
		});
	};

	var collectInstallSlugs = function () {
		var slugs = [];
		var $optimole = $('#enable_performance');
		if ($optimole.length && $optimole.is(':checked') && !$optimole.is(':disabled')) {
			slugs.push('optimole-wp');
		}
		if ($('#enable_otter_blocks').is(':checked') && !$('#enable_otter_blocks').is(':disabled')) {
			slugs.push('otter-blocks');
		}
		if ($('#enable_page_cache').is(':checked') && !$('#enable_page_cache').is(':disabled')) {
			slugs.push('wp-cloudflare-page-cache');
		}
		return slugs;
	};

	var setInstallStatus = function (slug, statusClass) {
		var $status = $('[data-install-status="' + slug + '"]');
		$status.removeClass('is-installing is-done is-error').addClass(statusClass);
	};

	var installPluginsSequentially = function (slugs, onDone) {
		if (!slugs.length) {
			onDone(true);
			return;
		}
		var slug = slugs.shift();
		setInstallStatus(slug, 'is-installing');
		$.post(
			visualizerSetupWizardData.ajax.url,
			{
				action: 'visualizer_wizard_step_process',
				security: visualizerSetupWizardData.ajax.security,
				slug: slug,
				step: 'step_4',
			},
			function (response) {
				if (1 === response.status) {
					setInstallStatus(slug, 'is-done');
					installPluginsSequentially(slugs, onDone);
				} else {
					setInstallStatus(slug, 'is-error');
					onDone(false, response.message);
				}
			}
		).fail(function () {
			setInstallStatus(slug, 'is-error');
			onDone(false, '');
		});
	};
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
		}
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
						runImport();
					}
					break;
				case 2:
					if ( isLivePreview ) {
						$('#smartwizard').smartWizard('next');
					} else {
						var urlParams = new URLSearchParams(window.location.search);
						urlParams.set('preview_chart', Date.now());
						window.location.hash = "#step-2";
						window.location.search = urlParams;
					} 
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
	// Step: 4 Skip and subscribe process.
	$(document).on( 'click', '.vz-subscribe', function (e) {
		var $btn = $(this);

		// Prevent double-clicks while the async flow is running.
		if ( $btn.prop('disabled') ) {
			e.preventDefault();
			return;
		}

		var withSubscribe = $btn.data("vz_subscribe");
		var postData = {
			action: "visualizer_wizard_step_process",
			security: visualizerSetupWizardData.ajax.security,
			step: "step_subscribe",
		};
		var emailElement = $("#vz_subscribe_email");
		// Remove error message.
		showNewsletterError(emailElement, '');
		var newsletterEnabled = $("#enable_newsletter").is(":checked");

		if (withSubscribe && newsletterEnabled) {
			var subscribeEmail = emailElement.val().trim();
			var errorMessage = "";

			if ("" === subscribeEmail) {
				errorMessage = visualizerSetupWizardData.errorMessages.requiredEmail;
			} else if (!emailRegex.test(subscribeEmail)) {
				errorMessage = visualizerSetupWizardData.errorMessages.invalidEmail;
			}
			if ("" !== errorMessage) {
				showNewsletterError(emailElement, errorMessage);
				return false;
			}

			postData.email = subscribeEmail;
			postData.with_subscribe = withSubscribe;
		} else {
			postData.with_subscribe = false;
		}

		$btn.prop('disabled', true);
		var $step = $('#step-3');
		$step.find(".spinner").addClass("is-active");
		var $error = $step.find('.vz-error-notice').first();
		$error.addClass('hidden').empty();

		var slugs = collectInstallSlugs();
		installPluginsSequentially(slugs.slice(), function (ok, message) {
			if (!ok) {
				if (message) {
					$error.html('<p>' + message + '</p>').removeClass('hidden');
				}
				$step.find(".spinner").removeClass("is-active");
				$btn.prop('disabled', false);
				return;
			}
			goToDraftPage(postData);
		});
		e.preventDefault();
	});

	$(document).on('click', '.vz-change-email', function () {
		var $card = $(this).closest('.vz-option-card--newsletter');
		var $inputWrap = $card.find('.vz-option-input');
		$inputWrap.show();
		$card.find('#vz_subscribe_email').focus();
		validateNewsletterEmail();
	});

	var finalizeNewsletterEmail = function ($card) {
		var $input = $card.find('#vz_subscribe_email');
		var $text = $card.find('.vz-email-text');
		var email = $input.val();
		if (email) {
			$text.text(email);
		}
		$card.find('.vz-option-input').hide();
	};

	$(document).on('click', '.vz-save-email', function () {
		var $card = $(this).closest('.vz-option-card--newsletter');
		if (!validateNewsletterEmail()) {
			return;
		}
		finalizeNewsletterEmail($card);
	});

	var validateNewsletterEmail = function () {
		var $input = $("#vz_subscribe_email");
		if (!$input.length) {
			return true;
		}
		var $card = $input.closest('.vz-option-card--newsletter');
		var $saveButton = $card.find('.vz-save-email');
		var email = $input.val().trim();
		showNewsletterError($input, '');
		if (!email) {
			$saveButton.prop('disabled', false);
			return true;
		}
		if (!emailRegex.test(email)) {
			showNewsletterError($input, visualizerSetupWizardData.errorMessages.invalidEmail);
			$saveButton.prop('disabled', true);
			return false;
		}
		$saveButton.prop('disabled', false);
		return true;
	};

	$(document).on('input blur', '#vz_subscribe_email', function () {
		validateNewsletterEmail();
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
	if ( $('#step-1 .vz-radio-btn:checked').length ) {
		$('#step-1').find('[data-step_number="1"]').removeClass('disabled');
	}

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

	$(window).bind('pageshow', function() {
		if ( jQuery('.vz-chart-option input').is(':checked') ) {
			$('#step-1').find('button.disabled').removeClass('disabled');
		}
	});
});
