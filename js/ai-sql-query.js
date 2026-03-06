(function($) {
    'use strict';

    $(document).ready(function() {
        if (typeof visualizerAI === 'undefined' || !visualizerAI.nonce_sql) {
            return;
        }

        // Tracks the last AI-generated query so follow-up prompts have context.
        var lastGeneratedQuery = '';

        // Generate on button click
        $(document).on('click', '#visualizer-ai-sql-generate', function(e) {
            e.preventDefault();
            generateSQL();
        });

        // Generate on Enter key (Shift+Enter for newline)
        $(document).on('keydown', '#visualizer-ai-sql-prompt', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                generateSQL();
            }
        });

        // Click a suggestion chip to use it as prompt
        $(document).on('click', '.visualizer-ai-sql-suggestion', function(e) {
            e.preventDefault();
            var suggestion = $(this).text();
            $('#visualizer-ai-sql-prompt').val(suggestion);
            generateSQL();
        });

        // Insert generated query into the SQL editor
        $(document).on('click', '#visualizer-ai-sql-use-query', function(e) {
            e.preventDefault();
            var query = $(this).data('query');
            if (!query) return;

            // Use the event system that frame.js listens for — it sets the CodeMirror value
            // and clears history. Falls back to direct textarea update for non-pro.
            $('body').trigger('visualizer:db:query:setvalue', { value: query });

            // Also set the textarea directly as a fallback
            var textarea = document.querySelector('.visualizer-db-query');
            if (textarea) {
                textarea.value = query;
                if (textarea.CodeMirror) {
                    textarea.CodeMirror.setValue(query);
                    textarea.CodeMirror.save();
                }
            }

            $('#visualizer-ai-sql-result').slideUp(150);
            $('#visualizer-ai-sql-prompt').val('');

            var btn = $(this);
            btn.text('Applied!').prop('disabled', true);
            setTimeout(function() {
                btn.text('Use This Query').prop('disabled', false);
            }, 2000);
        });

        function generateSQL() {
            var prompt = $('#visualizer-ai-sql-prompt').val().trim();
            if (!prompt) return;

            var model     = $('#visualizer-ai-sql-model').val() || 'openai';
            var chartType = '';
            var tables    = {};

            if (typeof visualizer !== 'undefined' && visualizer.charts && visualizer.charts.canvas) {
                chartType = visualizer.charts.canvas.type || '';
            }

            if (typeof visualizer !== 'undefined' && visualizer.db_query && visualizer.db_query.tables) {
                tables = visualizer.db_query.tables;
            }

            $('#visualizer-ai-sql-generate').prop('disabled', true);
            $('#visualizer-ai-sql-loading').show();
            $('#visualizer-ai-sql-result').hide();
            $('#visualizer-ai-sql-error').hide();

            $.ajax({
                url: visualizerAI.ajaxurl,
                type: 'POST',
                data: {
                    action:        'visualizer-ai-generate-sql',
                    nonce:         visualizerAI.nonce_sql,
                    prompt:        prompt,
                    model:         model,
                    chart_type:    chartType,
                    tables:        JSON.stringify(tables),
                    current_query: lastGeneratedQuery
                },
                success: function(response) {
                    $('#visualizer-ai-sql-generate').prop('disabled', false);
                    $('#visualizer-ai-sql-loading').hide();

                    if (response.success && response.data) {
                        var data = response.data;

                        var explanation = data.explanation || '';
                        if (explanation) {
                            $('#visualizer-ai-sql-explanation').text(explanation).show();
                        } else {
                            $('#visualizer-ai-sql-explanation').hide();
                        }

                        lastGeneratedQuery = data.query || '';
                        $('#visualizer-ai-sql-query-preview').text(lastGeneratedQuery);
                        $('#visualizer-ai-sql-use-query').data('query', lastGeneratedQuery);

                        var suggestions = data.suggestions || [];
                        if (suggestions.length > 0) {
                            var html = '';
                            $.each(suggestions, function(i, s) {
                                html += '<button type="button" class="button visualizer-ai-sql-suggestion" style="margin:3px 3px 3px 0;font-size:11px;">' + escapeHtml(s) + '</button>';
                            });
                            $('#visualizer-ai-sql-suggestions').html(html).show();
                            $('#visualizer-ai-sql-suggestions-label').show();
                        } else {
                            $('#visualizer-ai-sql-suggestions').hide();
                            $('#visualizer-ai-sql-suggestions-label').hide();
                        }

                        $('#visualizer-ai-sql-result').slideDown(200);
                    } else {
                        var errMsg = (response.data && response.data.message) ? response.data.message : 'An error occurred. Please try again.';
                        $('#visualizer-ai-sql-error').text(errMsg).show();
                    }
                },
                error: function(xhr) {
                    $('#visualizer-ai-sql-generate').prop('disabled', false);
                    $('#visualizer-ai-sql-loading').hide();
                    var errMsg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errMsg = xhr.responseJSON.data.message;
                    }
                    $('#visualizer-ai-sql-error').text(errMsg).show();
                }
            });
        }

        function escapeHtml(text) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    });

})(jQuery);
