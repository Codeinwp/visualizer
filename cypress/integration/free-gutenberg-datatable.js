describe('Test Free - gutenberg (datatable)', function() {
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

    it('Create charts charts', function() {
        cy.create_available_charts(1, 'DataTable');
    });

    it('Verify insertion of charts', function() {
        cy.visit('/post-new.php');

        cy.clear_welcome();

        var charts = Array.from({ length: 1 }, function(_item, index) {
            return index + 1;
        });

        cy.wrap(charts).each((value, i, array) => {
            // insert a visualizer block
            cy.get('div.edit-post-header-toolbar .block-editor-inserter button').click();
            cy.get('.components-popover__content').then(function ($popup) {
                cy.wrap($popup).find('.block-editor-inserter__search').type('visua');
                cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li').should('have.length', 1);
                cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li button').click();
            });

            // see the block has the correct elements.
            cy.get('div[data-type="visualizer/chart"]').should('have.length', (i + 2));

            cy.get('div[data-type="visualizer/chart"]:nth-child(' + (i + 1) + ')').then( ($block) => {
                // 2 rows - create and insert
                cy.wrap($block).find('.visualizer-settings__content-option').should('have.length', 2);
                
                // click insert
                cy.wrap($block).find('.visualizer-settings__content-option').last().click({force:true});

                // insert chart
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

                // click advanced options
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
