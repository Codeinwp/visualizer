describe('Test Free - gutenberg', function() {
    before(function(){
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        // login to WP
        cy.visit(Cypress.env('host') + 'wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    it.skip('temp test', function() {
    });

    it('Create charts', function() {
        cy.create_available_charts(Cypress.env('chart_types').free, 'GoogleCharts');
    });

    it('Verify insertion of charts', function() {
        cy.visit('/post-new.php');

        cy.clear_welcome();

        var charts = Array.from({ length: parseInt(Cypress.env('chart_types').free) }, function(_item, index) {
            return index + 1;
        });

        cy.wrap(charts).each((value, i, array) => {
            // insert a visualizer block
            cy.get('div.edit-post-header__toolbar button.edit-post-header-toolbar__inserter-toggle').click();
            cy.get('.edit-post-layout__inserter-panel-content').then(function ($popup) {
                cy.wrap($popup).find('.block-editor-inserter__search-input').type('visua');
                cy.wrap($popup).find('.block-editor-block-types-list .editor-block-list-item-visualizer-chart').should('have.length', 1);
                cy.wrap($popup).find('.block-editor-block-types-list .editor-block-list-item-visualizer-chart').click();
            });

            // see the block has the correct elements.
            cy.get('div[data-type="visualizer/chart"]').should('have.length', (i + 1));

            cy.get('div[data-type="visualizer/chart"]:nth-child(' + (i + 1) + ')').then( ($block) => {
                // 2 rows - create and insert
                cy.wrap($block).find('.visualizer-settings__content-option').should('have.length', 2);
                
                // click insert
                cy.wrap($block).find('.visualizer-settings__content-option').last().click({force:true});

                // insert chart
                cy.wrap($block).find('.visualizer-settings .visualizer-settings__charts-single:nth-child(' + (i + 1) + ')').then( ($chart_block) => {
                    cy.log('Inserting chart: ' + Cypress.$($chart_block).attr('data-chart-type'));
                });
                cy.wrap($block).find('.visualizer-settings .visualizer-settings__charts-single:nth-child(' + (i + 1) + ') .visualizer-settings__charts-controls').click();

                cy.wrap($block).find('.visualizer-settings .visualizer-settings__chart').should('have.length', 1);

                // log a line to show which chart we are trying to insert.
                cy.wrap($block).find('.visualizer-settings .visualizer-settings__chart').then( ($chart_block) => {
                    cy.log('Processing chart: ' + Cypress.$($chart_block).attr('data-chart-type'));
                });

                // chart and footer divs
                cy.wrap($block).find('.visualizer-settings .visualizer-settings__chart > div').should('have.length', 2);

                // 2 buttons, one of them "done"
                cy.wrap($block).find('.visualizer-settings .components-button-group button').should('have.length', 2);
                cy.wrap($block).find('.visualizer-settings .components-button-group button.visualizer-bttn-done').should('have.length', 1);

                // make the settings block appear.
                cy.wrap($block).type('{ctrl}{shift},');

                // click advanced options.
                cy.get('.visualizer-advanced-options button.components-button').click({force:true});

                // done button disappears, save button appears
                cy.wrap($block).find('.visualizer-settings .components-button-group button.visualizer-bttn-done').should('have.length', 0);
                cy.wrap($block).find('.visualizer-settings .components-button-group button.visualizer-bttn-save').should('have.length', 1);

                // click save button
                cy.wrap($block).find('.visualizer-settings .components-button-group button.visualizer-bttn-save').click({force:true});
            });
        });
    });

})
