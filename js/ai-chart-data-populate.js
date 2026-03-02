(function($) {
    'use strict';

    $(document).ready(function() {
        // Check if there's AI-generated chart data in sessionStorage
        var chartDataStr = sessionStorage.getItem('visualizer_ai_chart_data');

        // Check if there's pending styling to apply (after chart data upload)
        var pendingStyling = sessionStorage.getItem('visualizer_ai_pending_styling');

        if (pendingStyling && !chartDataStr) {
            // Chart data was already uploaded, now apply the styling
            console.log('Found pending styling to apply');
            setTimeout(function() {
                applyStoredStyling();
            }, 1500);
            return;
        }

        if (!chartDataStr) {
            return;
        }

        console.log('AI Chart Data found in sessionStorage');

        try {
            var chartData = JSON.parse(chartDataStr);
            console.log('Parsed chart data:', chartData);

            // Clear the sessionStorage
            sessionStorage.removeItem('visualizer_ai_chart_data');

            // Wait for the page to fully load before populating
            // The editor needs some time to initialize
            // Also check if vizUpdateHTML function exists (it's set when editor is ready)
            var attempts = 0;
            var maxAttempts = 20;

            function waitForEditor() {
                attempts++;
                console.log('Waiting for editor... attempt', attempts);

                if (typeof window.vizUpdateHTML !== 'undefined' || attempts >= maxAttempts) {
                    console.log('Editor ready or max attempts reached. Starting data population...');
                    populateChartData(chartData);
                } else {
                    setTimeout(waitForEditor, 300);
                }
            }

            setTimeout(waitForEditor, 1000);
        } catch (e) {
            console.error('Error parsing AI chart data:', e);
            sessionStorage.removeItem('visualizer_ai_chart_data');
        }
    });

    function applyStoredStyling() {
        var stylingStr = sessionStorage.getItem('visualizer_ai_pending_styling');
        if (!stylingStr) {
            console.log('No pending styling to apply');
            return;
        }

        console.log('Applying stored styling...');

        try {
            var styling = JSON.parse(stylingStr);
            var formatted = JSON.stringify(styling, null, 2);

            var manualConfigTextarea = $('#visualizer-manual-config, textarea[name="manual"]');
            console.log('Found manual config textarea:', manualConfigTextarea.length);

            if (manualConfigTextarea.length) {
                manualConfigTextarea.val(formatted);
                console.log('Set manual config with styling');

                // Clear the pending styling
                sessionStorage.removeItem('visualizer_ai_pending_styling');

                // Trigger events to update preview
                setTimeout(function() {
                    try {
                        manualConfigTextarea.trigger('change');
                        manualConfigTextarea.trigger('keyup');
                        $('textarea[name="manual"]').trigger('change');
                        console.log('Triggered preview update');

                        showNotification('Chart colors and styling from image applied successfully!', 'success');
                    } catch (e) {
                        console.warn('Error triggering preview update:', e);
                        // Still show success since the styling was set
                        showNotification('Chart colors and styling applied!', 'success');
                    }
                }, 500);
            } else {
                console.error('Manual config textarea not found');
            }
        } catch (e) {
            console.error('Error applying styling:', e);
            sessionStorage.removeItem('visualizer_ai_pending_styling');
        }
    }

    function populateChartData(chartData) {
        console.log('Populating chart data...', chartData);

        // Clean chart data - remove markdown code blocks if present
        if (chartData.csv_data) {
            chartData.csv_data = chartData.csv_data.replace(/^```[a-z]*\n?/m, '').replace(/\n?```$/m, '').trim();
            console.log('Cleaned chart data:', chartData.csv_data.substring(0, 200));
        }

        // Set the chart title if there's a title field
        if (chartData.title && $('input[name="title"]').length) {
            $('input[name="title"]').val(chartData.title);
            console.log('Set title:', chartData.title);
        }

        // Store styling for later (after chart data upload completes)
        if (chartData.styling && chartData.styling !== '{}') {
            sessionStorage.setItem('visualizer_ai_pending_styling', chartData.styling);
            console.log('Stored styling for later application');
        }

        // Import chart data first - styling will be applied after this completes
        if (chartData.csv_data) {
            importCSVData(chartData.csv_data);
        } else {
            console.warn('No chart data to import');
            // If there's no chart data but there's styling, apply it now
            applyStoredStyling();
        }
    }

    function importCSVData(csvData) {
        console.log('Importing chart data from image...');
        console.log('Data length:', csvData.length);
        console.log('Chart data:', csvData.substring(0, 200));

        // Check if we're using the simple editor
        var editedText = $('#edited_text');
        console.log('Found #edited_text:', editedText.length);

        if (editedText.length) {
            // Use the text editor
            console.log('Using text editor method');
            editedText.val(csvData);

            // Check if the editor button needs to be clicked first to show the editor
            var editorButton = $('#editor-button');
            console.log('Found editor button:', editorButton.length);
            console.log('Editor button current state:', editorButton.attr('data-current'));

            // Mimic the exact behavior from simple-editor.js
            setTimeout(function() {
                console.log('Setting chart-data, chart-data-src, and editor-type values');
                $('#chart-data').val(csvData);
                // Set source to 'text' to indicate manual data input
                $('#chart-data-src').val('text');

                // CRITICAL: Set editor-type - this is required by uploadData()
                // There might be TWO elements with this name:
                // 1. A dropdown select#viz-editor-type (when PRO with simple-editor feature)
                // 2. A hidden input[name="editor-type"]

                // First, check for the dropdown
                var editorTypeDropdown = $('#viz-editor-type');
                var editorTypeHidden = $('input[name="editor-type"]');

                console.log('Found editor type dropdown:', editorTypeDropdown.length);
                console.log('Found editor type hidden input:', editorTypeHidden.length);

                // For AI CSV upload, we ALWAYS want 'text' editor-type
                // because we're providing CSV string format, not JSON array

                // Update dropdown if it exists
                if (editorTypeDropdown.length) {
                    console.log('Current dropdown value:', editorTypeDropdown.val());
                    editorTypeDropdown.val('text');
                    console.log('Set dropdown to: text');
                }

                // Update or create hidden input
                if (editorTypeHidden.length) {
                    console.log('Current hidden input value:', editorTypeHidden.val());
                    editorTypeHidden.val('text');
                    console.log('Updated hidden input to: text');
                } else {
                    // Create it if it doesn't exist
                    editorTypeHidden = $('<input type="hidden" name="editor-type" value="text">');
                    $('#editor-form').append(editorTypeHidden);
                    console.log('Created hidden input with value: text');
                }

                console.log('Chart data length:', csvData.length);
                console.log('Chart data source set to: text');
                console.log('Editor type set to: text');

                // Lock the canvas (shows loading spinner)
                var canvas = $('#canvas');
                console.log('Found canvas:', canvas.length);
                if (canvas.length && typeof canvas.lock === 'function') {
                    console.log('Locking canvas');
                    canvas.lock();
                }

                // Submit the form
                console.log('Submitting editor form');
                var editorForm = $('#editor-form');
                console.log('Found editor form:', editorForm.length);

                if (editorForm.length) {
                    console.log('Form action:', editorForm.attr('action'));
                    console.log('Form method:', editorForm.attr('method'));
                    console.log('Form target:', editorForm.attr('target'));

                    // Watch for the iframe to load (form submission complete)
                    var iframe = $('#thehole, iframe[name="thehole"]');
                    console.log('Found iframe:', iframe.length);

                    if (iframe.length) {
                        // Watch for iframe to load and check its content
                        iframe.one('load', function() {
                            console.log('Iframe loaded after form submission');

                            try {
                                var iframeContent = iframe.contents();
                                var iframeBody = iframeContent.find('body').html();
                                console.log('Iframe content length:', iframeBody ? iframeBody.length : 0);
                                console.log('Iframe body (first 1000 chars):', iframeBody ? iframeBody.substring(0, 1000) : 'empty');

                                // Check for specific error patterns
                                if (iframeBody) {
                                    if (iframeBody.indexOf('error') > -1 || iframeBody.indexOf('critical error') > -1) {
                                        console.error('Error detected in iframe response');
                                        showNotification('Data upload failed. Please check your data format and try again. Check browser console for details.', 'error');
                                    } else if (iframeBody.indexOf('<br') > -1 && iframeBody.indexOf('<b>') > -1) {
                                        console.error('PHP warning/error detected in response:', iframeBody.substring(0, 200));
                                        showNotification('Server error occurred. Check console for details.', 'error');
                                    } else if (iframeBody.trim().length < 10) {
                                        console.log('Response appears to be empty or minimal - might be success');
                                    } else {
                                        console.log('Response contains content but no obvious errors');
                                    }
                                }
                            } catch (e) {
                                console.warn('Could not read iframe content (cross-origin?):', e);
                            }
                        });

                        // Watch for vizUpdateHTML to be called
                        var originalVizUpdateHTML = window.vizUpdateHTML;
                        var vizUpdateCalled = false;

                        window.vizUpdateHTML = function(editor, sidebar) {
                            console.log('vizUpdateHTML called - data processed successfully!');
                            console.log('Editor:', editor);
                            console.log('Sidebar:', sidebar);
                            vizUpdateCalled = true;

                            // Call the original function
                            if (originalVizUpdateHTML) {
                                originalVizUpdateHTML(editor, sidebar);
                            }

                            // Unlock canvas and show success
                            setTimeout(function() {
                                if (canvas.length && typeof canvas.unlock === 'function') {
                                    console.log('Unlocking canvas');
                                    canvas.unlock();
                                }
                                showNotification('Chart created successfully from image!', 'success');

                                // Restore original function
                                window.vizUpdateHTML = originalVizUpdateHTML;
                            }, 500);
                        };

                        // Fallback: If vizUpdateHTML isn't called within 5 seconds, check what happened
                        setTimeout(function() {
                            if (!vizUpdateCalled) {
                                console.warn('vizUpdateHTML not called after 5 seconds');
                                // Restore original function
                                window.vizUpdateHTML = originalVizUpdateHTML;

                                // Check if data was actually uploaded by inspecting the page
                                var hasData = $('#edited_text').val().length > 0;
                                console.log('Has data in edited_text:', hasData);

                                if (hasData) {
                                    console.log('Data exists, reloading page to display chart...');
                                    window.location.reload();
                                } else {
                                    console.error('Form submitted but no data found. Check server logs.');
                                    if (canvas.length && typeof canvas.unlock === 'function') {
                                        canvas.unlock();
                                    }
                                    showNotification('Data upload may have failed. Check browser console and server logs.', 'error');
                                }
                            }
                        }, 5000);
                    }

                    console.log('About to submit form with data:', {
                        'chart-data': $('#chart-data').val().substring(0, 100),
                        'chart-data-src': $('#chart-data-src').val(),
                        'editor-type': $('input[name="editor-type"]').val(),
                        'chart-data-full-length': $('#chart-data').val().length,
                        'form-action': editorForm.attr('action')
                    });

                    // Log the full CSV data for debugging
                    console.log('Full CSV data being submitted:');
                    console.log($('#chart-data').val());

                    // Check if any other hidden fields exist that might interfere
                    console.log('All hidden inputs in form:');
                    editorForm.find('input[type="hidden"]').each(function() {
                        console.log('  -', $(this).attr('name'), '=', $(this).val().substring(0, 50));
                    });

                    editorForm.submit();
                } else {
                    console.error('Editor form not found!');
                    console.log('Available forms:', $('form').map(function() {
                        return $(this).attr('id') || $(this).attr('name') || 'unnamed';
                    }).get());

                    // Unlock canvas if form not found
                    if (canvas.length && typeof canvas.unlock === 'function') {
                        canvas.unlock();
                    }
                }
            }, 500);

            showNotification('Chart data loaded successfully! Processing chart...', 'success');
        } else {
            console.warn('Text editor not found. Trying alternative methods...');

            // Try direct chart-data field
            var chartDataField = $('#chart-data');
            console.log('Found #chart-data:', chartDataField.length);

            if (chartDataField.length) {
                console.log('Using direct chart-data field');
                chartDataField.val(csvData);

                // Try to submit the form
                var form = chartDataField.closest('form');
                if (form.length) {
                    console.log('Submitting form');
                    form.submit();
                }
            } else {
                console.error('Could not find any data input method');
                console.log('Available inputs:', $('input, textarea').map(function() {
                    return $(this).attr('id') || $(this).attr('name');
                }).get());

                showNotification('Chart type selected, but please manually add this chart data:\n\n' + csvData, 'warning');
            }
        }
    }

    function showNotification(message, type) {
        type = type || 'info';

        var bgColor = '#0073aa';
        if (type === 'success') {
            bgColor = '#46b450';
        } else if (type === 'warning') {
            bgColor = '#ffb900';
        } else if (type === 'error') {
            bgColor = '#dc3232';
        }

        var notification = $('<div>')
            .css({
                'position': 'fixed',
                'top': '32px',
                'left': '50%',
                'transform': 'translateX(-50%)',
                'background': bgColor,
                'color': 'white',
                'padding': '15px 20px',
                'border-radius': '4px',
                'box-shadow': '0 2px 10px rgba(0,0,0,0.2)',
                'z-index': '999999',
                'max-width': '80%',
                'text-align': 'center',
                'font-size': '14px',
                'white-space': 'pre-wrap'
            })
            .text(message)
            .appendTo('body');

        setTimeout(function() {
            notification.fadeOut(function() {
                notification.remove();
            });
        }, 5000);
    }

})(jQuery);
