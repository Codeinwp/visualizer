(function($) {
    'use strict';

    var selectedImage = null;

    $(document).ready(function() {
        console.log('AI Chart from Image loaded');

        // Handle choose image button click
        $('#ai-upload-chart-image-btn').on('click', function(e) {
            e.preventDefault();
            $('#ai-chart-image-upload').click();
        });

        // Drag and drop support
        var dropZone = $('#ai-image-drop-zone');

        dropZone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).css({
                'border-color': '#0073aa',
                'background': '#e8f4f8'
            });
        });

        dropZone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).css({
                'border-color': '#ddd',
                'background': '#fafafa'
            });
        });

        dropZone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).css({
                'border-color': '#ddd',
                'background': '#fafafa'
            });

            var files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelection(files[0]);
            }
        });

        // Handle file selection
        $('#ai-chart-image-upload').on('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                handleFileSelection(file);
            }
        });

        // Handle generate chart button click
        $('#ai-generate-from-image-btn').on('click', function(e) {
            e.preventDefault();
            generateChartFromImage();
        });
    });

    function handleFileSelection(file) {
        console.log('File selected:', file.name, file.type, file.size);

        // Validate file type
        if (!file.type.match('image.*')) {
            showError('Please select a valid image file.');
            return;
        }

        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            showError('Image file is too large. Please select an image smaller than 10MB.');
            return;
        }

        // Show filename
        $('#ai-selected-filename').text(file.name);

        // Read and preview image
        var reader = new FileReader();
        reader.onload = function(event) {
            selectedImage = event.target.result;

            // Show preview
            $('#ai-preview-img').attr('src', selectedImage);
            $('#ai-image-preview').show();

            // Show generate button
            $('#ai-generate-from-image-btn').show();

            // Hide any previous messages
            $('#ai-image-error').hide();
            $('#ai-image-success').hide();
        };
        reader.readAsDataURL(file);
    }

    function generateChartFromImage() {
        if (!selectedImage) {
            showError('Please select an image first.');
            return;
        }

        console.log('Generating chart from image...');

        // Hide messages
        $('#ai-image-error').hide();
        $('#ai-image-success').hide();

        // Show loading
        $('#ai-image-loading').show();
        $('#ai-generate-from-image-btn').prop('disabled', true);

        // Get selected AI model (check if there's a model selector)
        var model = 'openai'; // Default to OpenAI
        if ($('.visualizer-ai-model-select').length) {
            model = $('.visualizer-ai-model-select').val();
        } else {
            // Determine which API key is configured
            if (typeof visualizerAI !== 'undefined' && visualizerAI.has_gemini) {
                model = 'gemini';
            } else if (typeof visualizerAI !== 'undefined' && visualizerAI.has_claude) {
                model = 'claude';
            }
        }

        var requestData = {
            action: 'visualizer-ai-analyze-chart-image',
            nonce: visualizerAI.nonce_image,
            image: selectedImage,
            model: model
        };

        console.log('Sending request with model:', model);

        $.ajax({
            url: visualizerAI.ajaxurl,
            type: 'POST',
            data: requestData,
            success: function(response) {
                console.log('Response:', response);
                $('#ai-image-loading').hide();
                $('#ai-generate-from-image-btn').prop('disabled', false);

                if (response.success) {
                    var data = response.data;
                    console.log('Chart analysis data:', data);

                    showSuccess('Chart analyzed successfully! Creating chart...');

                    // Create the chart with the extracted data
                    createChartWithData(data);
                } else {
                    showError(data.message || 'Failed to analyze chart image.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', {xhr: xhr, status: status, error: error});
                $('#ai-image-loading').hide();
                $('#ai-generate-from-image-btn').prop('disabled', false);

                var errorMsg = 'Failed to analyze chart image. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMsg = xhr.responseJSON.data.message;
                }

                showError(errorMsg);
            }
        });
    }

    function createChartWithData(data) {
        console.log('Creating chart with data:', data);

        // Store the data for use in the next step
        var chartType = data.chart_type || 'column';

        // Check if chart type is available (not locked in free version)
        if (typeof visualizerAI !== 'undefined' && visualizerAI.chart_types) {
            var chartTypeInfo = visualizerAI.chart_types[chartType];
            console.log('Chart type info:', chartTypeInfo);

            if (chartTypeInfo && !chartTypeInfo.enabled) {
                // Chart type is locked - show PRO upgrade message
                showError('The "' + chartTypeInfo.name + '" chart type detected in your image is not available in the free version.');

                // Show upgrade button
                var upgradeHtml = '<div style="margin-top: 15px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">';
                upgradeHtml += '<p style="margin: 0 0 10px 0; color: #856404; font-weight: bold;">🔒 Premium Feature</p>';
                upgradeHtml += '<p style="margin: 0 0 10px 0; color: #856404;">This chart type requires Visualizer PRO. Upgrade now to use all chart types including ' + chartTypeInfo.name + '.</p>';
                upgradeHtml += '<a href="' + (visualizerAI.pro_url || '#') + '" target="_blank" class="button button-primary">';
                upgradeHtml += 'Upgrade to PRO';
                upgradeHtml += '</a>';
                upgradeHtml += '</div>';

                $('#ai-image-error').after(upgradeHtml);
                return;
            }
        }

        var chartData = {
            type: chartType,
            title: data.title || 'Untitled Chart',
            csv_data: data.csv_data || '',
            styling: data.styling || '{}'
        };

        // Store in sessionStorage for the next page
        sessionStorage.setItem('visualizer_ai_chart_data', JSON.stringify(chartData));
        console.log('Stored chart data in sessionStorage');

        // Select the chart type radio button
        var typeRadio = $('input[name="type"][value="' + chartType + '"]');
        console.log('Found type radio:', typeRadio.length);
        if (typeRadio.length) {
            typeRadio.prop('checked', true);
            typeRadio.closest('.type-label').addClass('type-label-selected');
            console.log('Selected chart type:', chartType);
        } else {
            console.error('Chart type radio not found:', chartType);
            // Try to find available chart types
            console.log('Available chart types:', $('input[name="type"]').map(function() { return $(this).val(); }).get());
        }

        // Select GoogleCharts as the library (NOT DataTable!)
        var librarySelect = $('select[name="chart-library"]');
        console.log('Found library select:', librarySelect.length);
        if (librarySelect.length) {
            var availableLibs = librarySelect.find('option').map(function() { return $(this).val(); }).get();
            console.log('Available libraries:', availableLibs);

            // Prefer GoogleCharts, then ChartJS, avoid DataTable
            var preferredLibrary = 'GoogleCharts';
            if (availableLibs.indexOf('GoogleCharts') !== -1) {
                preferredLibrary = 'GoogleCharts';
            } else if (availableLibs.indexOf('ChartJS') !== -1) {
                preferredLibrary = 'ChartJS';
            } else {
                preferredLibrary = availableLibs[0]; // fallback to first
            }

            console.log('Selecting library:', preferredLibrary);
            librarySelect.val(preferredLibrary);

            // Also try using the viz-select-library class
            $('.viz-select-library').val(preferredLibrary);
        } else {
            console.error('Library select not found');

            // Alternative: Try to find it by class
            librarySelect = $('.viz-select-library');
            if (librarySelect.length) {
                console.log('Found library select by class');
                var availableLibs = librarySelect.find('option').map(function() { return $(this).val(); }).get();
                var preferredLibrary = availableLibs.indexOf('GoogleCharts') !== -1 ? 'GoogleCharts' : availableLibs[0];
                console.log('Selecting library:', preferredLibrary);
                librarySelect.val(preferredLibrary);
            }
        }

        // Check form validity
        var form = $('#viz-types-form');
        console.log('Form found:', form.length);
        console.log('Selected type:', $('input[name="type"]:checked').val());
        console.log('Selected library:', $('select[name="chart-library"]').val());

        // Trigger the form submission to move to the next step
        showSuccess('Chart type detected: ' + chartType + '. Creating chart...');

        setTimeout(function() {
            console.log('Submitting form...');
            $('#viz-types-form').submit();
        }, 1500);
    }

    function showError(message) {
        $('#ai-image-error').text(message).show();
        setTimeout(function() {
            $('#ai-image-error').fadeOut();
        }, 5000);
    }

    function showSuccess(message) {
        $('#ai-image-success').text(message).show();
        setTimeout(function() {
            $('#ai-image-success').fadeOut();
        }, 3000);
    }

})(jQuery);
