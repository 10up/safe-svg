describe('Admin can login and make sure plugin is activated', () => {
  before(() => {
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
});
