describe('Admin can login and make sure plugin is activated', () => {
  beforeEach(() => {
    cy.login();
  });

  it('Open dashboard', () => {
    cy.visit('/wp-admin');
    cy.get('h1').should('contain', 'Dashboard');
  });

  it('Can activate plugin if it is deactivated', () => {
    cy.activatePlugin('safe-svg');
    cy.deactivatePlugin('safe-svg-cypress-test-plugin');
  });

  it('Can enable user role', () => {
    cy.visit('/wp-admin/options-media.php');
	cy.get('[name="safe_svg_upload_roles[]"]').first().check();
	cy.get('#submit').click()
  });
});
