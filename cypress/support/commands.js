// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This is will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })


import 'cypress-file-upload';


// generates a random number upto max
Cypress.Commands.add( 'get_random_int', (max) => {
    var num = Math.floor(Math.random() * Math.floor(max));
    // 0 is 'empty', so generate a 
    if(num === 0){
        num = 1;
    }
    return num;
});


// generates a random color
Cypress.Commands.add( 'get_random_color', () => {
    var $color = Math.floor(Math.random()*16777215).toString(16);
    // if somehow the color does not contain 6 characters.
    if($color.length < 6){
        $color += '0';
    }
    return '#' + $color;
});

// populate a random value
Cypress.Commands.add( 'populate_random', ($element) => {
    var $type = $element.attr('type') || $element.prop('tagName');
    var $class = $element.attr('class');

    // scroll to the element to prevent it from being hidden by the frame/screen.
    $element.get(0).scrollIntoView();

    // do not touch disabled elements.
    if($element.is(':disabled')){
        return;
    }
    switch($type.toLowerCase()){
        case 'select':
            // second index.
            $element.prop('selectedIndex', 2);
            break;
        case 'textarea':
            cy.wrap($element).clear().type('{}');
            break;
        case 'text':
            if($class.indexOf('color-picker') !== -1){
                cy.get_random_color().then( ($value) => {
                    cy.wrap($element).clear({force: true}).type($value, {force: true});
                });
                break;
            }
        case 'number':
            cy.get_random_int(50).then( ($value) => {
                cy.wrap($element).clear().type($value);
            });
            break;
        case 'radio':
        case 'checkbox':
            cy.wrap($element).check();
            break;
    }
});

// check if a value is populated.
Cypress.Commands.add( 'check_populated', ($element) => {
    var $type = $element.prop('type') || $element.prop('tagName');
    var $val = '';

    // do not touch disabled elements.
    if($element.is(':disabled')){
        return;
    }

    switch($type.toLowerCase()){
        case 'select':
            expect($element.prop('selectedIndex')).to.equal(2);
            break;
        case 'textarea':
        case 'text':
        case 'number':
            $val = $element.val();
            break;
        case 'radio':
        case 'checkbox':
            $val = $element.is(':checked') ? 'yes' : '';
            break;
        default:
            $val = 'not-expected-element';
            break;
    }
    expect($val).to.not.equal('');
});


// test advanced settings.
Cypress.Commands.add( 'create_new_chart', () => {
    cy.visit(Cypress.env('urls').library ).then(() => {
        cy.get('.add-new-h2.add-new-chart').first().click();
    });

    cy.wait( Cypress.env('wait') );

    cy.get('iframe')
    .then(function ($iframe) {
        const $body = $iframe.contents().find('body');
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
});

Cypress.Commands.add( 'test_advanced_settings', ($create_new_chart) => {
    var first_chart = '';

    if($create_new_chart){
        // create the default chart.
        cy.visit(Cypress.env('urls').library );
        cy.get('.add-new-h2.add-new-chart').click();

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');
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
    }

    cy.visit(Cypress.env('urls').library ).then(() => {
        const id = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
        first_chart = Cypress.$('#' + id).html();
    });

    cy.get('.visualizer-chart-action.visualizer-chart-edit').first().click();

    cy.wait( Cypress.env('wait') );

    // set some values.
    cy.get('iframe')
    .then(function ($iframe) {
        const $body = $iframe.contents().find('body');

        // click the settings tab
        cy.wrap($body).find('#viz-tab-advanced').click();

        // cycle through each accordion and sub-accordion and set values in each input element.
        cy.wrap($body).find('#settings-form').within( ($form) => {
            // click non disabled sections
            cy.get('.viz-group:not(.only-pro-feature) .viz-group-title').each( ($section) => {
                cy.wrap($section).click().then( () => {
                    cy.wrap($section).siblings('.viz-group-content').first().then( ($tab) => {
                        if($tab.find('li.viz-subsection').length > 0){
                            cy.wrap($tab).find('.viz-section-title').each( ($subsection) => {
                                cy.wrap($subsection).click().then( () => {
                                    cy.wrap($subsection).siblings('.viz-section-items').first().find('input, textarea, select').each( ($element) => {
                                        cy.populate_random($element);
                                    });
                                });
                            });
                        }else{
                            cy.wrap($section).parent().find('.viz-section-items').first().find('input, textarea, select').each( ($element) => {
                                cy.populate_random($element);
                            });
                        }
                    });
                });
            });

            cy.wrap($body).find('#settings-button').click();

        });
    });

    cy.visit(Cypress.env('urls').library ).then(() => {
        const id = Cypress.$('div.visualizer-chart div.visualizer-chart-canvas').first().attr('id');
        var content = Cypress.$('#' + id).html();
        //expect(content).to.not.equal(first_chart);
    });

    cy.wait( Cypress.env('wait') * 2 );
    // if the settings cause the chart to be malformed, an error might show and click has to be forced
    cy.get('.visualizer-chart-action.visualizer-chart-edit').first().click({force:true});

    cy.wait( Cypress.env('wait') );
    // check if all values are set.
    cy.get('iframe')
    .then(function ($iframe) {
        const $body = $iframe.contents().find('body');

        // click the settings tab
        cy.wrap($body).find('#viz-tab-advanced').click();

        // cycle through each accordion and sub-accordion and set values in each input element.
        cy.wrap($body).find('#settings-form').within( ($form) => {
            cy.get('.viz-group:not(.only-pro-feature) .viz-group-title').each( ($section) => {
                cy.wrap($section).click().then( () => {
                    cy.wrap($section).siblings('.viz-group-content').first().then( ($tab) => {
                        if($tab.find('li.viz-subsection').length > 0){
                            cy.wrap($tab).find('.viz-section-title').each( ($subsection) => {
                                cy.wrap($subsection).click().then( () => {
                                    cy.wrap($subsection).siblings('.viz-section-items').first().find('input, textarea, select').each( ($element) => {
                                        cy.check_populated($element);
                                    });
                                });
                            });
                        }else{
                            cy.wrap($section).parent().find('.viz-section-items').first().find('input, textarea, select').each( ($element) => {
                                cy.check_populated($element);
                            });
                        }
                    });
                });
            });
        });
    });
});

// create the first N charts available
Cypress.Commands.add( 'create_available_charts', ($num, $lib = '') => {
    var charts = [];
    for(var i = 1; i <= parseInt($num); i++){
        charts.push(i);
    }

    // iterate through the first N charts in the types screen and create each one.
    cy.wrap(charts).each((chart, i, array) => {
        cy.visit(Cypress.env('urls').library ).then(() => {
            cy.get('.add-new-h2.add-new-chart').first().click();
        });

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');

            cy.wrap($body).then(function($body){
                // if we are targeting a particular library, remove charts that do not support it
                // like Google may support 2nd, 3rd and 10th charts - so we will remove all but these.
                if('' !== $lib){
                    $body.find('#type-picker .type-box:not(.type-lib-' + $lib + ')').remove();
                }
                // select the chart.
                cy.wrap($body).find('#type-picker .type-box:nth-child(' + chart + ') .type-radio').check();

                // if we are targeting a particular library, then select it in the toolbar
                if('' !== $lib){
                    cy.wrap($body).find('.viz-select-library').invoke('show').select($lib);
                }
                // create the chart.
                cy.wrap($body).find('#toolbar input[type="submit"]').click();
            });
        });

        cy.wait( Cypress.env('wait') );

        cy.get('iframe')
        .then(function ($iframe) {
            const $body = $iframe.contents().find('body');
            // create the chart.
            cy.wrap($body).find('#toolbar input#settings-button').click();
        });

        cy.wait( Cypress.env('wait') );
        // verify that the chart was created and the count increased by 1
        cy.visit(Cypress.env('urls').library ).then(() => {
            cy.get('#visualizer-library .visualizer-chart').should('have.length', chart);
        });

    });

    // verify that all charts have been created
    cy.visit(Cypress.env('urls').library ).then(() => {
        cy.get('#visualizer-library .visualizer-chart').should('have.length', $num);
    });
});

Cypress.Commands.add( 'clear_welcome', () => {
    cy.window().then(win => {
        win.wp
        && ( win.wp.data.select( "core/edit-post" ).isFeatureActive( "welcomeGuide" ) && win.wp.data.dispatch( "core/edit-post" ).toggleFeature( "welcomeGuide" ) )
        ;
    });
});

Cypress.Commands.add('updateWPSetting', (setting, value) => {
    // Navigate to an admin page that includes the wpApiSettings nonce
    cy.visit('/wp-admin').then(() => {
        // Retrieve the nonce from the window object
        cy.window().then((win) => {
            const nonce = win.wpApiSettings.nonce;

            // Make the REST API request to update the WordPress setting
            cy.request({
                method: 'POST',
                url: '/wp-json/wp/v2/settings',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
                },
                body: {
                    [setting]: value,
                },
            }).then((response) => {
                expect(response.status).to.eq(200);
            });
        });
    });
});