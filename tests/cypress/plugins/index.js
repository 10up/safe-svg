/// <reference types="cypress" />
// ***********************************************************
// This example plugins/index.js can be used to load plugins
//
// You can change the location of this file or turn off loading
// the plugins file with the 'pluginsFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/plugins-guide
// ***********************************************************

// This function is called when a project is opened or re-opened (e.g. due to
// the project's config changing)

const path = require( 'path' );
const { loadConfig } = require( '@wordpress/env/lib/config' );
const configDirectoryPath = path.resolve( __dirname, '../../..' );

/**
 * @type {Cypress.PluginConfig}
 */
// eslint-disable-next-line no-unused-vars
module.exports = async (on, config) => {
  const wpEnvConfig = await loadConfig( configDirectoryPath );

  if (wpEnvConfig) {
    const port = wpEnvConfig.env.tests.port || null;

    if (port) {
      config.baseUrl = wpEnvConfig.env.tests.config.WP_SITEURL;
    }
  }

  return config;
};
