(function($) {
    'use strict';

    var chatHistory = [];
    var currentConfig = null;

    $(document).ready(function() {
        if (typeof visualizerAI !== 'undefined') {
            // Show welcome message with animation
            var libraryName = visualizerAI.chart_library && visualizerAI.chart_library.toLowerCase() === 'chartjs' ? 'Chart.js' : 'Google Charts';
            setTimeout(function() {
                addAIMessage('👋 Hello! I\'m your AI chart assistant. I can help you customize this ' + visualizerAI.chart_type + ' chart (' + libraryName + ').\n\n✨ Try a Quick Action above, choose a Preset, or ask me anything!');
            }, 300);
        } else {
            console.error('visualizerAI is not defined!');
        }

        // Initialize collapsible sections
        initCollapsibleSections();

        // Handle send message
        $('#visualizer-ai-send-message').on('click', function(e) {
            e.preventDefault();
            sendMessage();
        });

        // Handle enter key in textarea
        $('#visualizer-ai-prompt').on('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Handle clear chat
        $('#visualizer-ai-clear-chat').on('click', function(e) {
            e.preventDefault();
            clearChat();
        });

        // Handle show suggestions
        $('#visualizer-ai-show-suggestions').on('click', function(e) {
            e.preventDefault();
            $('#visualizer-ai-prompt').val('What can I customize for this chart? Please give me some suggestions.');
            sendMessage();
        });
    });

    function initCollapsibleSections() {
        // Make all section titles collapsible
        $('.viz-group-title').on('click', function() {
            var $group = $(this).parent('.viz-group');
            $group.toggleClass('collapsed');

            // Save state in localStorage
            var groupId = $group.attr('id');
            if (groupId) {
                var collapsed = $group.hasClass('collapsed');
                localStorage.setItem('visualizer_section_' + groupId, collapsed);
            }
        });

        // Restore collapsed state from localStorage
        $('.viz-group').each(function() {
            var groupId = $(this).attr('id');
            if (groupId) {
                var isCollapsed = localStorage.getItem('visualizer_section_' + groupId);
                if (isCollapsed === 'true') {
                    $(this).addClass('collapsed');
                }
            }
        });
    }

    function sendMessage() {
        var prompt = $('#visualizer-ai-prompt').val().trim();
        var model = $('.visualizer-ai-model-select').val();

        if (!prompt) {
            return;
        }

        // Add user message to chat
        addUserMessage(prompt);

        // Clear input
        $('#visualizer-ai-prompt').val('');

        // Show loading
        $('.visualizer-ai-loading').show();
        $('#visualizer-ai-send-message').prop('disabled', true);

        // Get current manual configuration
        var currentManualConfig = $('#visualizer-manual-config').val().trim();

        var requestData = {
            action: 'visualizer-ai-generate-config',
            nonce: visualizerAI.nonce,
            prompt: prompt,
            model: model || 'openai',
            chart_type: visualizerAI.chart_type,
            chart_library: visualizerAI.chart_library,
            chat_history: JSON.stringify(chatHistory),
            current_config: currentManualConfig
        };

        $.ajax({
            url: visualizerAI.ajaxurl,
            type: 'POST',
            data: requestData,
            success: function(response) {
                $('.visualizer-ai-loading').hide();
                $('#visualizer-ai-send-message').prop('disabled', false);

                if (response.success) {
                    var data = response.data;

                    // Add AI response to chat
                    addAIMessage(data.message);

                    // Auto-apply configuration if provided (no preview, just apply)
                    if (data.configuration) {
                        currentConfig = data.configuration;

                        addConfigPreview(data.configuration);

                        // Auto-apply immediately
                        applyConfiguration(true); // Show success message
                    }

                    // Add to history
                    chatHistory.push({
                        role: 'user',
                        content: prompt
                    });
                    chatHistory.push({
                        role: 'assistant',
                        content: data.message
                    });
                } else {
                    addErrorMessage(data.message || 'An error occurred');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', {xhr: xhr, status: status, error: error});
                $('.visualizer-ai-loading').hide();
                $('#visualizer-ai-send-message').prop('disabled', false);

                var errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMsg = xhr.responseJSON.data.message;
                }

                addErrorMessage(errorMsg);
            }
        });
    }

    function addUserMessage(message) {
        var html = '<div style="margin-bottom: 15px; text-align: right;">' +
            '<div style="display: inline-block; background: #0073aa; color: white; padding: 10px 15px; border-radius: 18px; max-width: 80%; text-align: left;">' +
            '<strong>You:</strong><br>' + escapeHtml(message) +
            '</div></div>';

        $('#visualizer-ai-messages').append(html);
        scrollToBottom();
    }

    function addAIMessage(message) {
        var html = '<div style="margin-bottom: 15px;">' +
            '<div style="display: inline-block; background: white; border: 1px solid #ddd; padding: 10px 15px; border-radius: 18px; max-width: 80%;">' +
            '<strong>AI Assistant:</strong><br>' + escapeHtml(message).replace(/\n/g, '<br>') +
            '</div></div>';

        $('#visualizer-ai-messages').append(html);
        scrollToBottom();
    }

    function addConfigPreview(config) {
        try {
            var parsed = JSON.parse(config);
            var formatted = JSON.stringify(parsed, null, 2);

            var html = '<div style="margin-bottom: 15px;">' +
                '<div style="background: #f0f0f1; border: 1px solid #c3c4c7; padding: 10px; border-radius: 4px; max-width: 80%;">' +
                '<div style="font-size: 11px; color: #666; margin-bottom: 5px;">Configuration JSON:</div>' +
                '<pre style="margin: 0; font-size: 11px; max-height: 150px; overflow-y: auto; background: white; padding: 8px; border-radius: 3px;">' + escapeHtml(formatted) + '</pre>' +
                '</div></div>';

            $('#visualizer-ai-messages').append(html);
            scrollToBottom();
        } catch (e) {
            console.error('Error formatting config:', e);
        }
    }

    function addErrorMessage(message) {
        var html = '<div style="margin-bottom: 15px;">' +
            '<div style="display: inline-block; background: #dc3232; color: white; padding: 10px 15px; border-radius: 18px; max-width: 80%;">' +
            '<strong>Error:</strong><br>' + escapeHtml(message) +
            '</div></div>';

        $('#visualizer-ai-messages').append(html);
        scrollToBottom();
    }

    function clearChat() {
        chatHistory = [];
        currentConfig = null;
        $('#visualizer-ai-messages').empty();

        // Show welcome message again
        if (typeof visualizerAI !== 'undefined') {
            addAIMessage('Chat cleared. How can I help you customize your ' + visualizerAI.chart_type + ' chart?');
        }
    }

    function applyConfiguration(showMessage) {
        if (!currentConfig) {
            return;
        }

        // Default to showing message if not specified
        if (typeof showMessage === 'undefined') {
            showMessage = true;
        }

        var manualConfigTextarea = $('#visualizer-manual-config');

        if (manualConfigTextarea.length) {
            var existingConfig = manualConfigTextarea.val().trim();
            var finalConfig = currentConfig;
            var wasMerged = false;

            // If there's existing configuration, merge them
            if (existingConfig) {
                try {
                    var existing = JSON.parse(existingConfig);
                    var newConfig = JSON.parse(currentConfig);

                    // Deep merge
                    var merged = $.extend(true, {}, existing, newConfig);
                    finalConfig = JSON.stringify(merged, null, 2);
                    wasMerged = true;

                    if (showMessage) {
                        addAIMessage('✓ I\'ve merged the new configuration with your existing settings and applied it!');
                    }
                } catch (e) {
                    console.error('Error merging configurations:', e);
                    try {
                        var parsed = JSON.parse(currentConfig);
                        finalConfig = JSON.stringify(parsed, null, 2);
                    } catch (e2) {
                        finalConfig = currentConfig;
                    }
                }
            } else {
                try {
                    var parsed = JSON.parse(currentConfig);
                    finalConfig = JSON.stringify(parsed, null, 2);
                } catch (e) {
                    finalConfig = currentConfig;
                }

                if (showMessage) {
                    addAIMessage('✓ Configuration applied! Your chart preview should update automatically.');
                }
            }

            manualConfigTextarea.val(finalConfig);

            // Trigger events to update preview
            // Use setTimeout to ensure the value is set before triggering events
            setTimeout(function() {
                // Trigger on the ID selector
                manualConfigTextarea.trigger('change');
                manualConfigTextarea.trigger('keyup');
                manualConfigTextarea.trigger('input');

                // Also trigger on the name selector that preview.js uses
                $('textarea[name="manual"]').trigger('change');
                $('textarea[name="manual"]').trigger('keyup');
            }, 100);

            // Don't scroll if we're auto-applying
            if (showMessage) {
                // Scroll to manual configuration
                $('html, body').animate({
                    scrollTop: manualConfigTextarea.offset().top - 100
                }, 500);
            }
        } else {
            if (showMessage) {
                addErrorMessage('Manual configuration field not found.');
            }
        }
    }

    function scrollToBottom() {
        var container = $('#visualizer-ai-chat-container');
        if (container.length && container[0]) {
            container.scrollTop(container[0].scrollHeight);
        }
    }

    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

})(jQuery);
