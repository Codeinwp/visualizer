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
    });

    it('Verify insertion of charts', function() {
        cy.visit('/post-new.php');

        // get rid of that irritating popup
        cy.get('.nux-dot-tip__disable').click();

        // insert a visualizer block
        cy.get('div.edit-post-header-toolbar .block-editor-inserter button').click();
        cy.get('.components-popover__content').then(function ($popup) {
            cy.wrap($popup).find('.block-editor-inserter__search').type('visua');
            cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li').should('have.length', 1);
            cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li button').click();
        });

        // see the block has the correct elements.
        cy.get('div[data-type="visualizer/chart"]').should('have.length', 1);
        cy.get('.visualizer-settings__content-option').should('have.length', 2);

        cy.get('.visualizer-settings__content-option').last().click();
        cy.wait( Cypress.env('wait') );

        // add all charts one by one - insert, go back, insert, go back...
        var $pages = [];
        for(var i = 0; i < Math.ceil(parseInt(Cypress.env('chart_types').free)/6); i++){
            $pages.push(i);
        }
        cy.wrap($pages).each((page, i, array) => {
            var charts = [];
            cy.wrap(page).then( () => {
                for(var i = page * 6; i < ( ( (page * 6) + 6 ) > Cypress.env('chart_types').free ? Cypress.env('chart_types').free : ( (page * 6) + 6 ) ); i++){
                    charts.push(i + 1);
                }
            }).then( () => {
                cy.wrap(charts).each((num, i, array) => {
                    if(page > 0){
                        // click load more for every page except the first.
                        cy.get('.visualizer-settings').then( ($div) => {
                            cy.wrap($div).find('.visualizer-settings__charts .components-button').click();
                        });
                        cy.wait( Cypress.env('wait') );
                    }

                    cy.get('.visualizer-settings').then( ($div) => {
                        // insert the chart.
                        cy.wrap($div).find('.visualizer-settings__charts-single:nth-child(' + num + ')').then( ($chart) => {
                            cy.wrap($chart).find('.visualizer-settings__charts-controls').click();
                        });
                    })
                    .then( () => {
                        cy.wait( Cypress.env('wait') );
                        cy.get('.visualizer-settings').then( ($div) => {
                            // check if inserted
                            cy.wrap($div).find('.visualizer-settings__chart').should('have.length', 1);
                            cy.wrap($div).find('.visualizer-settings__chart > div').should('have.length', 1);
                            cy.wrap($div).find('.components-button-group button').should('have.length', 2);

                            // go back to insert another one.
                            cy.wrap($div).find('.components-button-group button').first().click();
                            cy.wait( Cypress.env('wait') );
                        });
                    });
                });
            });
        });

    });

})
