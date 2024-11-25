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
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

Cypress.Commands.add('uploadMedia', (filePath) => {
    cy.visit('/wp-admin/media-new.php');
    cy.get('.drag-drop').should('exist');
    cy.get('#drag-drop-area').should('exist');
    cy.get('#drag-drop-area').selectFile(filePath, { action: 'drag-drop' });
});

Cypress.Commands.add('uploadMediaThroughGrid', (filePath) => {
    cy.visit('/wp-admin/upload.php?mode=grid');
    cy.get('.supports-drag-drop').should('exist');
    cy.get('.uploader-window').should('exist');
    // Intercept the upload request
    cy.intercept('POST', '/wp-admin/async-upload.php').as('upload');
    cy.get('.supports-drag-drop').selectFile(filePath, { action: 'drag-drop', force: true, waitForAnimations: true });
    return cy.wait('@upload')
        .then(({ request, response }) => {
            cy.get('.uploader-window').trigger('dropzone:leave');
            return cy.wrap(response.headers['x-wp-upload-attachment-id'] ?? 0);
        })
});
