describe('Test Free - lifecycle', function() {
    before(function(){
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        // login to WP
        cy.visit(Cypress.env('host') + 'wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    it('Verify library', function() {
        cy.visit(Cypress.env('urls').library );

        // chart types
        cy.get('li.visualizer-list-item').should( "have.length", parseInt( Cypress.env('chart_types').free ) + parseInt( Cypress.env('chart_types').pro ) + 1 );

        // pro chart types
        cy.get('li.visualizer-list-item a.visualizer-pro-only').should( "have.length", parseInt( Cypress.env('chart_types').pro ) );

        // upsell
        cy.get('.visualizer-sidebar-box').should( "have.length", 1 );

    });

    var first_chart_exists = '';
    var first_chart_created = '';

    it('Create default chart', function() {
        cy.visit(Cypress.env('urls').library ).then(() => {
            first_chart_exists = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
        });

        cy.get('.add-new-h2.add-new-chart').first().click();

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');

            // chart selection screen - types
            cy.wrap($body).find('.type-box').should( "have.length", 14 );

            // chart selection screen - pro types
            cy.wrap($body).find('.type-box span.visualizder-pro-label').should( "have.length", 6 );

            // toolbar
            // cancel button
            cy.wrap($body).find('#toolbar input[type="button"]').should( "have.length", 1 );
            // next button
            cy.wrap($body).find('#toolbar input[type="submit"]').should( "have.length", 1 );
            // library combo
            cy.wrap($body).find('#toolbar select.viz-select-library').should( "have.length", 1 );

            // create the default chart.
            cy.wrap($body).find('#toolbar input[type="submit"]').click();
        });

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');
            // create the default chart.
            cy.wrap($body).find('#toolbar input#settings-button').click();
        });

        cy.wait( Cypress.env('wait') );

        // first chart is now different from the first chart before.
        cy.visit(Cypress.env('urls').library ).then(() => {
            first_chart_created = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            expect(first_chart_created).to.not.equal(first_chart_exists);
        });
    });

    it('Clone chart', function() {
        cy.visit(Cypress.env('urls').library );

        cy.get('.visualizer-chart-action.visualizer-chart-clone').first().click({force:true});

        cy.wait( Cypress.env('wait') );

        var first_chart_now = '';
        cy.visit(Cypress.env('urls').library ).then(() => {
            first_chart_now = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            expect(first_chart_now).to.not.equal(first_chart_created);
            expect(first_chart_now).to.not.equal(first_chart_exists);
        });

        // TODO: maybe check how well "cloned" the chart is compared to its parent.
        // Note: In google charts some parts are different for identical charts.
    });

    it('Delete chart', function() {
        cy.visit(Cypress.env('urls').library );

        cy.get('.visualizer-chart-action.visualizer-chart-delete').first().click({force:true}).then(() => {
            cy.on('window:confirm', (str) => {
                expect(str).to.contain('permanently delete');
                return true;
            });
        });

        cy.wait( Cypress.env('wait') );

        var first_chart_now = '';
        cy.visit(Cypress.env('urls').library ).then(() => {
            first_chart_now = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            expect(first_chart_now).to.equal(first_chart_created);
        });

    });

    it('Pro features locked', function() {
        cy.get('.visualizer-chart-action.visualizer-chart-edit').first().click({force:true});

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');

            cy.wrap($body).find('.only-pro-feature').should( 'have.length', parseInt( Cypress.env('features_locked').free ) );

        });
    });

    it('Advanced settings', function() {
        cy.test_advanced_settings(true);
    });

})
