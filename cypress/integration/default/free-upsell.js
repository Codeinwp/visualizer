describe('Test Free - upsells', function() {
    before(function () {

        // login to WP
        cy.visit('wp-login.php');
        cy.get('#user_login').clear().type(Cypress.env('login'));
        cy.get('#user_pass').clear().type(Cypress.env('pass'));
        cy.get('#wp-submit').click();
    });

    beforeEach(() => {
        cy.visit(Cypress.env('urls').library );
        cy.get('.add-new-h2.add-new-chart').first().click();
        cy.wait( Cypress.env('wait') );
    });

    it('Check upsell links', function() {
        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                cy.wrap($body).find('#toolbar input[type="submit"]').click();
                cy.wait( Cypress.env('wait') );
            });

        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                // Check Import form other chart upsell.
                const other_import = cy.wrap($body).find('.viz-import-from-other');
                other_import.click();
                other_import.should('have.class', 'only-pro-feature');
                const other_import_upsell_url = cy.wrap($body).find('.viz-import-from-other .viz-group-content .only-pro-content .only-pro-container .only-pro-inner a').should('have.attr', 'href').then((href) => {
                    const other_import_search_params = new URLSearchParams(href);
                    expect(other_import_search_params.get('utm_campaign')).to.equal('import-chart');
                });
            });

        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                // Check import form WordPress upsell.
                const wp_import = cy.wrap($body).find('.visualizer_source_query_wp');
                wp_import.click();
                wp_import.should('have.class', 'only-pro-feature');
                const wp_import_upsell_url = cy.wrap($body).find('.visualizer_source_query_wp .viz-group-content .only-pro-content .only-pro-container .only-pro-inner a').should('have.attr', 'href').then((href) => {
                    const wp_import_search_params = new URLSearchParams(href);
                    expect(wp_import_search_params.get('utm_campaign')).to.equal('import-wp');
                });
            });

        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                // Check import form database upsell.
                const db_import = cy.wrap($body).find('.visualizer_source_query');
                db_import.click();
                db_import.should('have.class', 'only-pro-feature');
                const db_import_upsell_url = cy.wrap($body).find('.visualizer_source_query .viz-group-content .only-pro-content .only-pro-container .only-pro-inner a').should('have.attr', 'href').then((href) => {
                    const db_import_search_params = new URLSearchParams(href);
                    expect(db_import_search_params.get('utm_campaign')).to.equal('db-query');
                });
            });

        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                // Check import form manual data upsell.
                const manual_import = cy.wrap($body).find('.visualizer_source_manual');
                manual_import.click();
                manual_import.should('have.class', 'only-pro-feature');
                const manual_import_upsell_url = cy.wrap($body).find('.visualizer_source_manual .viz-group-content .only-pro-content .only-pro-container .only-pro-inner a').should('have.attr', 'href').then((href) => {
                    const manual_import_search_params = new URLSearchParams(href);
                    expect(manual_import_search_params.get('utm_campaign')).to.equal('manual-data');
                });
            });
    });

    it('Check Settings upsell links', function() {
        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                cy.wrap($body).find('#toolbar input[type="submit"]').click();
                cy.wait( Cypress.env('wait') );
            });

        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                cy.wrap($body).find('#viz-tab-advanced').click();
                cy.wait( Cypress.env('wait') );
            });

        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                // Check setting chart data filter configuration upsell.
                const data_control = cy.wrap($body).find('#vz-data-controls');
                data_control.click();
                data_control.should('have.class', 'only-pro-feature');
                const data_control_upsell_url = cy.wrap($body).find('#vz-data-controls .viz-group-content .only-pro-content .only-pro-container .only-pro-inner a').should('have.attr', 'href').then((href) => {
                    const data_control_search_params = new URLSearchParams(href);
                    expect(data_control_search_params.get('utm_campaign')).to.equal('data-filter-configuration');
                });
            });

        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                // Check setting frontend actions upsell.
                const frontend_actions = cy.wrap($body).find('#vz-frontend-actions');
                frontend_actions.click();
                frontend_actions.should('have.class', 'only-pro-feature');
                const frontend_actions_upsell_url = cy.wrap($body).find('#vz-frontend-actions .viz-group-content .only-pro-content .only-pro-container .only-pro-inner a').should('have.attr', 'href').then((href) => {
                    const frontend_actions_search_params = new URLSearchParams(href);
                    expect(frontend_actions_search_params.get('utm_campaign')).to.equal('frontend-actions');
                });
            });

        cy.get('iframe')
            .then(function ($iframe) {
                const $body = $iframe.contents().find('body');
                // Check setting permissions upsell.
                const permissions = cy.wrap($body).find('#vz-permissions');
                permissions.click();
                permissions.should('have.class', 'only-pro-feature');
                const permissions_upsell_url = cy.wrap($body).find('#vz-permissions .viz-group-content .only-pro-content .only-pro-container .only-pro-inner a').should('have.attr', 'href').then((href) => {
                    const permissions_search_params = new URLSearchParams(href);
                    expect(permissions_search_params.get('utm_campaign')).to.equal('chart-permissions');
                });
            });
    });
});
