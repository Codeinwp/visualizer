describe('Test Free - gutenberg', function() {
    before(function(){
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        // login to WP
        cy.visit(Cypress.env('host') + 'wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    it('Create all charts', function() {
        cy.create_available_charts(Cypress.env('chart_types').free);
        //cy.create_available_charts(1);
    });

    it('Verify insertion of charts', function() {
        cy.visit('/post-new.php');

        // get rid of that irritating popup
        cy.get('.nux-dot-tip__disable').click();

        var charts = [];
        for(var i = 1; i <= parseInt(Cypress.env('chart_types').free); i++){
            charts.push(i);
        }
        //charts.push(1);charts.push(2);

        cy.wrap(charts).each((num, i, array) => {
            // insert a visualizer block
            cy.get('div.edit-post-header-toolbar .block-editor-inserter button').click();
            cy.get('.components-popover__content').then(function ($popup) {
                cy.wrap($popup).find('.block-editor-inserter__search').type('visua');
                cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li').should('have.length', 1);
                cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li button').click();
            });

            // see the block has the correct elements.
            cy.get('div[data-type="visualizer/chart"]').should('have.length', num);
            cy.get('div[data-type="visualizer/chart"]:nth-child(' + num + ')').then( ($block) => {
                cy.wrap($block).find('.visualizer-settings__content-option').should('have.length', 2);

                cy.wrap($block).find('.visualizer-settings__content-option').last().click();
                cy.wait( Cypress.env('wait') );

                cy.wrap($block).find('.visualizer-settings .visualizer-settings__charts-single:nth-child(' + num + ') .visualizer-settings__charts-controls').click();
                cy.wait( Cypress.env('wait') );
                cy.wrap($block).find('.visualizer-settings .visualizer-settings__chart').should('have.length', 1);
                cy.wrap($block).find('.visualizer-settings .visualizer-settings__chart > div').should('have.length', 1);
                cy.wrap($block).find('.visualizer-settings .components-button-group button').should('have.length', 2);
            });
        });
    });

})
