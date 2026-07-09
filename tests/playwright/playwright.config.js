const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './e2e',
  timeout: 60000,
  expect: {
    timeout: 10000,
  },
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: process.env.CI
    ? [['github'], ['html', { outputFolder: 'tests/playwright/reports/html', open: 'never' }]]
    : [['list'], ['html', { outputFolder: 'tests/playwright/reports/html', open: 'never' }]],
  use: {
    baseURL: process.env.WP_BASE_URL || 'http://localhost:8889',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  outputDir: 'tests/playwright/reports/test-results',
});
