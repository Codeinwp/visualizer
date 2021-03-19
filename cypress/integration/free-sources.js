describe('Test Free - sources', function() {
    before(function(){
        Cypress.env('host','http://localhost/codeinwp/');
        Cypress.env('login','wordpress')
        Cypress.env('pass','wordpress')
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        // login to WP
        cy.visit(Cypress.env('host') + 'wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });


    var first_chart_content = '';

    it('Import from CSV', function() {
        cy.create_new_chart();

        cy.visit(Cypress.env('urls').library ).then(() => {
            const id = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            first_chart_content = Cypress.$('#' + id).html();
        });

        // edit the created chart
        cy.get('.visualizer-chart-action.visualizer-chart-edit').first().click({force:true});

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');

            cy.wrap($body).find('.viz-group-title.visualizer-src-tab').first().click();

            const fileName = 'pie.csv';
            // select file to upload
            cy.fixture(fileName).then(fileContent => {
                cy.wrap($body).find('#csv-file').upload({ fileContent, fileName, mimeType: 'text/csv' });
                cy.wrap($body).find('#vz-import-file').click({force:true}).then(() => {
                    cy.wrap($body).find('#settings-button').click({force:true});
                });
            });
        });

        cy.wait( Cypress.env('wait') );

        var content = '';
        cy.visit(Cypress.env('urls').library ).then(() => {
            const id = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            content = Cypress.$('#' + id).html();
            //expect(content).to.not.equal(first_chart_content);
        });
    });

    it('Import CSV from URL', function() {
        cy.visit(Cypress.env('urls').library ).then(() => {
            const id = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            first_chart_content = Cypress.$('#' + id).html();
        });

        cy.get('.visualizer-chart-action.visualizer-chart-edit').first().click();

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');

            cy.wrap($body).find('.viz-group-title.visualizer-src-tab').last().click().then( () => {
                cy.wrap($body).find('.viz-section-title').first().click();
            });

            const fileName = Cypress.env('urls').samples + 'area.csv';
            cy.wrap($body).find('input[type="url"]#vz-schedule-url').clear().type( fileName, {force:true} );
            cy.wrap($body).find('#view-remote-file').click().then(() => {
                cy.wait( Cypress.env('wait') );
                cy.wrap($body).find('#settings-button').click();
            });
        });

        cy.wait( Cypress.env('wait') );

        var content = '';
        cy.visit(Cypress.env('urls').library ).then(() => {
            const id = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            content = Cypress.$('#' + id).html();
            //expect(content).to.not.equal(first_chart_content);
        });
    });

    it('Import from JSON', function() {
        cy.create_new_chart();

        cy.visit(Cypress.env('urls').library ).then(() => {
            const id = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            first_chart_content = Cypress.$('#' + id).html();
        });

        cy.get('.visualizer-chart-action.visualizer-chart-edit').first().click({force:true});

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');

            const fileName = Cypress.env('urls').samples + 'test.json';

            cy.wrap($body).find('#visualizer-json-parse').should('not.be.visible');
            cy.wrap($body).find('#vz-import-json-root').should('not.be.visible');
            cy.wrap($body).find('#json-conclude-form').should('not.be.visible');

            cy.wrap($body).find('.viz-group-title.visualizer-src-tab').last().click().then( () => {
                cy.wrap($body).find('.viz-section-title.visualizer_source_json').first().click();
                cy.wrap($body).find('#json-chart-button').click();

                cy.wrap($body).find('input[type="url"]#vz-import-json-url').clear().type( fileName, {force:true} );
                cy.wrap($body).find('#visualizer-json-fetch').click({force:true}).then( () => {
                    cy.wait( Cypress.env('wait') );
                    cy.wrap($body).find('#visualizer-json-parse').should('be.visible');
                    cy.wrap($body).find('#vz-import-json-root').should('be.visible');
                    cy.wrap($body).find('#vz-import-json-root option').should('have.length', 2);
                    cy.wrap($body).find('#vz-import-json-root').invoke('prop', 'selectedIndex', 1);
                });

                cy.wrap($body).find('#visualizer-json-parse').click({force:true}).then( () => {
                    cy.wait( Cypress.env('wait') );
                    cy.wrap($body).find('#json-conclude-form').should('be.visible');
                    cy.wrap($body).find('.json-table tr').should('have.length', 15);

                    /*
                    // TODO: Alert does not get auto accepted
                    // check alert, if no columns are selected.
                    const stub = cy.stub();
                    cy.on('window:alert', stub);

                    cy.wrap($body).find('#json-chart-button').click().then( () => {
                        expect(stub.getCall(0)).to.be.calledWith('Please select a few columns to include in the chart.');
                    });
                    */

                    cy.wrap($body).find('.json-table tbody tr td select').each( function( el, index ) {
                        if(index === 0){
                            cy.wrap(el).select('date', {force: true});
                        }else{
                            cy.wrap(el).select('number', {force: true});
                        }
                    });
                    cy.wrap($body).find('#json-chart-button').click().then( () => {
                        cy.wait( Cypress.env('wait') );
                        cy.wrap($body).find('#settings-button').click();
                    });
                });
            });
        });

        cy.visit(Cypress.env('urls').library ).then(() => {
            const id = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            var content = Cypress.$('#' + id).html();
            //expect(content).to.not.equal(first_chart_content);
        });
    });

    it('Manual Data', function() {
        cy.visit(Cypress.env('urls').library ).then(() => {
            const id = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
            first_chart_content = Cypress.$('#' + id).html();
        });

        cy.get('.visualizer-chart-action.visualizer-chart-edit').first().click({force:true});

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');

            cy.wrap($body).find('.viz-simple-editor-type.viz-table-editor').should('not.be.visible');
            cy.wrap($body).find('#viz-editor-type').should('not.be.visible');

            cy.wrap($body).find('.viz-group-title.visualizer-editor-tab').click().then( () => {
                // check how many editors.
                cy.wrap($body).find('#viz-editor-type option').should('have.length', Cypress.env('editors').free);
                
                // select an editor, save chart and then check if the chart is reloaded with it
                cy.wrap($body).find('#viz-editor-type').select(Cypress.env('editors').selected);
                cy.wrap($body).find('#editor-button').click();

                cy.wrap($body).find('.viz-simple-editor-type.viz-table-editor').should('be.visible');

                cy.wrap($body).find('#editor-button').click().then( () => {
                    cy.wrap($body).find('.viz-simple-editor-type.viz-table-editor').should('not.be.visible');
                    cy.wrap($body).find('#settings-button').click({force:true});
                });
            });
        });

        cy.wait( Cypress.env('wait') );

        // reload the same chart, it should open in the edit tab with the correct editor selected
        cy.get('.visualizer-chart-action.visualizer-chart-edit').first().click({force:true});

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');

            cy.wrap($body).find('.viz-simple-editor-type.viz-table-editor').should('not.be.visible');

            cy.wrap($body).find('#viz-editor-type').should('be.visible');
            cy.wrap($body).find('#viz-editor-type').invoke('val').should('contain', Cypress.env('editors').selected);
        });
    });

})
