const { test, expect } = require('@wordpress/e2e-test-utils-playwright');
const { login } = require('./helpers');

test.describe('Admin can login and make sure plugin is activated', () => {
  test.beforeEach(async ({ requestUtils }) => {
    await login(requestUtils);
  });

  test('Open dashboard', async ({ admin, page }) => {
    await admin.visitAdminPage('index.php');
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
  });

  test('Can activate plugin if it is deactivated', async ({ requestUtils }) => {
    await requestUtils.activatePlugin('safe-svg');
    await requestUtils.deactivatePlugin('safe-svg-playwright-test-plugin');
  });

  test('Can enable user role', async ({ admin, page }) => {
    await admin.visitAdminPage('options-media.php');
    await page.locator('[name="safe_svg_upload_roles[]"]').first().check();
    await page.locator('#submit').click();
    await expect(page.locator('#setting-error-settings_updated')).toBeVisible();
  });

  test('Can toggle the large SVG setting', async ({ admin, page }) => {
    await admin.visitAdminPage('options-media.php');
    await page.locator('[name="safe_svg_large_svg"]').check();
    await page.locator('#submit').click();
    await expect(page.locator('#setting-error-settings_updated')).toBeVisible();
  });
});
