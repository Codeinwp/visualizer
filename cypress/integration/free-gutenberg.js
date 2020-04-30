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
        cy.visit('/post-new.php');
        cy.clear_welcome();
    });

    it('Create all charts', function() {
        cy.create_available_charts(Cypress.env('chart_types').free);
    });

    it('Verify insertion of charts', function() {
        cy.visit('/post-new.php');

        cy.clear_welcome();

        var charts = [];
        for(var i = 1; i <= parseInt(Cypress.env('chart_types').free); i++){
            charts.push(i);
        }

        cy.wrap(charts).each((value, i, array) => {
            // insert a visualizer block
            cy.get('div.edit-post-header-toolbar .block-editor-inserter button').click();
            cy.get('.components-popover__content').then(function ($popup) {
                cy.wrap($popup).find('.block-editor-inserter__search').type('visua');
                cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li').should('have.length', 1);
                cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li button').click();
            });

            // see the block has the correct elements.
            cy.get('div[data-type="visualizer/chart"]').should('have.length', (i + 1));

            cy.get('div[data-type="visualizer/chart"]:nth-child(' + (i + 1) + ')').then( ($block) => {
                cy.wrap($block).find('.visualizer-settings__content-option').should('have.length', 2);
                cy.wrap($block).find('.visualizer-settings__content-option').last().click({force:true});

                cy.wrap($block).find('.visualizer-settings .visualizer-settings__charts-single:nth-child(' + (i + 1) + ') .visualizer-settings__charts-controls').click();
                cy.wrap($block).find('.visualizer-settings .visualizer-settings__chart').should('have.length', 1);
                cy.wrap($block).find('.visualizer-settings .visualizer-settings__chart > div').should('have.length', 2);
                cy.wrap($block).find('.visualizer-settings .components-button-group button').should('have.length', 2);
            });
        });
    });

})
