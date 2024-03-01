describe('Gutenberg Block create via Wizard', function() {
    before(function(){

        // login to WP
        cy.visit('/wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    /**
     * Check if the chart created with Wizard does not have crashes in the block sidebar/inspector components when editing it.
     */
    it('Create a chart and check its block Inspector components', function() {
        // Mark as fresh install to allow the wizard to start.
        cy.updateWPSetting('visualizer_fresh_install', 'yes').then(() => {
            
            // Enter the Wizard
            cy.visit('/wp-admin/admin.php?page=visualizer-setup-wizard#step-1');
            
            // STEP 1
            // Check main step 1 components are visible
            cy.get('#step-1 .vz-accordion-item__title h2').should('be.visible');
            cy.get('#step-1 .vz-chart-option').should('be.visible');
            cy.get('#step-1 [data-step_number="1"]').should('be.visible');

            // Select a chart the go to the next step.
            cy.get('.vz-chart-option').first().click();
            cy.get('#step-1 [data-step_number="1"]').click({ force: true});

            // STEP 2
            // Check if we can go to the next step
            cy.get('#step-2 > .vz-accordion-item > .vz-accordion-item__content > .vz-form-wrap > :nth-child(2) > .btn', { timeout: 30000 }).should('be.visible');
            cy.get('#step-2 > .vz-accordion-item > .vz-accordion-item__content > .vz-form-wrap > :nth-child(2) > .btn').should('not.be.disabled');
            
            // Go to the next step
            cy.get('#step-2 > .vz-accordion-item > .vz-accordion-item__content > .vz-form-wrap > :nth-child(2) > .btn').click({ force: true});

            // STEP 3
            // Check if creating the draft is checked.
            cy.get('#insert_shortcode').should('be.checked');

            // Go to the next step
            cy.get('#step-3 > :nth-child(1) > .border-top > .vz-form-wrap > :nth-child(2) > .btn').click();

            // STEP 4
            // Do not install optimization plugin
            cy.get('.vz-accordion-item > .vz-accordion-item__title > .vz-checkbox > .vz-checkbox-btn').click();

            // Go to the final step
            cy.get(':nth-child(2) > .next-btn').click({ force: true });

            // STEP 5
            // Complete the wizard
            cy.get('.btn-outline-primary').click({ force: true });

            // POST PAGE WITH THE CREATED CHART
            // Wait for the redirect to load and check if the block is visible
            cy.get('.edit-post-welcome-guide .components-modal__header button').click(); // Close the welcome modal.
            cy.get('.visualizer-settings__title', {timeout: 30000}).should('be.visible');
            cy.get('.visualizer-settings__title').click();

            // Select 'Block' tab in Inspector
            cy.get('ul > :nth-child(2) > .components-button').click();

            // Open tab 'Import from file'
            cy.get(':nth-child(1) > .components-panel__body-title > .components-button').click();

            // Open tab 'Import from URL'
            cy.get(':nth-child(2) > .components-panel__body-title > .components-button').click();
          
            // Select Advanced Options tab
            cy.get('.visualizer-advanced-options > .components-panel__body-title > .components-button').click();

            // Open sub-tab 'General Settings'
            cy.get(':nth-child(2) > :nth-child(3) > :nth-child(1) > .components-button').click();

            // Open sub-tab 'Title'
            cy.get('.visualizer-advanced-panel.is-opened > :nth-child(2) > .components-panel__body-title > .components-button').click();

            // Check if the block is still visible
            cy.get('.visualizer-settings__title', {timeout: 30000}).should('be.visible');
        });
    });

})
