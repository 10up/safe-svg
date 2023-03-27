describe('Safe SVG Tests', () => {
  before(() => {
    cy.login();
  });

  it('Admin can upload SVG image', () => {
    cy.uploadMedia('.wordpress-org/icon.svg');
    cy.get('.media-item .media-list-title, .media-item .title').should('exist').contains('icon');
    cy.get('.media-item a.edit-attachment').should('exist').contains('Edit');
  });

  /**
   * Flow for verify SVG sanitization.
   *
   * fixtures folder contains custom.svg file
   * It contains custom tag (customTestTag) with custom attribute (customTestAttribute).
   * 
   * This test upload custom.svg to WP, get svg file content by URL and verify
   * that it's not containing customTestTag and customTestAttribute.
   */ 
  it('SVG should be sanitized', () => {
    // Deactivate Test Plugin if it is active.
    cy.deactivatePlugin('safe-svg-cypress-test-plugin');

    // Test
    cy.uploadMedia('tests/cypress/fixtures/custom.svg');
    cy.get('#media-items .media-item a.edit-attachment').invoke('attr', 'href').then(editLink => {
			cy.visit(editLink);
		} );
    cy.get('input#attachment_url').invoke('val')
      .then(url => {
        cy.request(url)
          .then((response) => {
            cy.wrap(response?.body)
              .should('not.contain', 'customTestTag')
              .should('not.contain', 'customTestAttribute');
          });
      });
  });

  /**
   * Test plugin uses `svg_allowed_tags` and `svg_allowed_attributes` filters
   * to modify allowed tags and attributes.
   */
  it('Plugin should allow modify allowed tags and attributes', () => {
    // Activate Test Plugin
    cy.activatePlugin('safe-svg-cypress-test-plugin');

    // Test
    cy.uploadMedia('tests/cypress/fixtures/custom.svg');

    cy.get('#media-items .media-item a.edit-attachment').invoke('attr', 'href').then(editLink => {
			cy.visit( editLink );
		});
    cy.get('input#attachment_url').invoke('val')
      .then(url => {
        cy.request(url)
          .then((response) => {
            cy.wrap(response?.body)
              .should('contain', 'customTestTag')
              .should('contain', 'customTestAttribute');
          });
      });

    // Deactivate Test Plugin
    cy.deactivatePlugin('safe-svg-cypress-test-plugin');
  });

  it('Bad formatted SVG shouldn\'t upload and give an error.', () => {
    cy.fixture('badXmlTestOne.svg').as('badXmlTestOne');
    cy.uploadMedia('@badXmlTestOne');

    cy.get('.media-item .error-div.error').should('exist').contains('has failed to upload');
  });
});
