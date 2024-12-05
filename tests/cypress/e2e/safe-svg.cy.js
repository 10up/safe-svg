describe('Safe SVG Tests', () => {
  beforeEach(() => {
    cy.login();
  });

  it('Admin can upload SVG image via add new media file', () => {
    cy.uploadMedia('.wordpress-org/icon.svg');
    cy.get('.media-item .media-list-title, .media-item .title').should('exist').contains('icon');
    cy.get('.media-item a.edit-attachment').should('exist').contains('Edit');
  });

  it('Admin can upload SVG image via the media grid', () => {
    cy.uploadMediaThroughGrid('.wordpress-org/icon.svg').then((attachmentId) => {
      cy.get(`.attachments .attachment[data-id="${attachmentId}"]`).should('exist');
    });
  });

  it('Admin can add SVG block to a post', () => {
    cy.uploadMedia('.wordpress-org/icon.svg');

    cy.createPost( {
        title: 'SVG Block Test',
        beforeSave: () => {
            cy.insertBlock( 'safe-svg/svg-icon' );
            cy.getBlockEditor().find( '.block-editor-media-placeholder' ).contains( 'button', 'Media Library' ).click();
            cy.get( '#menu-item-browse' ).click();
            cy.get( '.attachments-wrapper li:first .thumbnail' ).click();
            cy.get( '.media-modal .media-button-select' ).click();
        },
    } ).then( post => {
        cy.visit( `/wp-admin/post.php?post=${post.id}&action=edit` );
        cy.getBlockEditor().find( '.wp-block-safe-svg-svg-icon' );
    } );
  } );

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


  // Test plugin doesn't break the block editor when no blocks are added
  it('Plugin should not break the block editor when optimizer enabled', () => {
    // Activate Test Plugin
    cy.deactivatePlugin('safe-svg-cypress-test-plugin');
    cy.activatePlugin('safe-svg-cypress-optimizer-test-plugin');
    cy.createPost('Hello World');
  });

  it('Output of wp_get_attachment_image should use full svg dimensions', () => {
    // Activate test plugin.
    cy.activatePlugin('safe-svg-cypress-test-plugin');

    // Visit the home page.
    cy.visit('/');

    // Verify that the SVG images are displayed with the correct dimensions.
    cy.get('#thumbnail-image').should('have.attr', 'width', '256').should('have.attr', 'height', '256');
    cy.get('#medium-image').should('have.attr', 'width', '256').should('have.attr', 'height', '256');
    cy.get('#large-image').should('have.attr', 'width', '256').should('have.attr', 'height', '256');
    cy.get('#full-image').should('have.attr', 'width', '256').should('have.attr', 'height', '256');
    cy.get('#custom-image').should('have.attr', 'width', '256').should('have.attr', 'height', '256');

    // Deactivate the test plugin.
    cy.deactivatePlugin('safe-svg-cypress-test-plugin');
  });

  it('Output of get_image_tag should use custom dimensions', () => {
    // Activate test plugin.
    cy.activatePlugin('safe-svg-cypress-test-plugin');

    // Visit the home page.
    cy.visit('/');

    // Verify that the SVG images are displayed with the correct dimensions.
    // TODO: these are the sizes returned but seems they are not correct. get_image_tag_override needs to be fixed.
    cy.get('.size-thumbnail.wp-image-6').should('have.attr', 'width', '150').should('have.attr', 'height', '150');
    cy.get('.size-medium.wp-image-6').should('have.attr', 'width', '300').should('have.attr', 'height', '300');
    cy.get('.size-large.wp-image-6').should('have.attr', 'width', '1024').should('have.attr', 'height', '1024');
    cy.get('.size-full.wp-image-6').should('have.attr', 'width', '256').should('have.attr', 'height', '256');
    cy.get('.size-100x120.wp-image-6').should('have.attr', 'width', '100').should('have.attr', 'height', '100');

    // Deactivate the test plugin.
    cy.deactivatePlugin('safe-svg-cypress-test-plugin');
  });
});
