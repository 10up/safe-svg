const path = require('path');
const { WP_USERNAME = 'admin', WP_PASSWORD = 'password' } = process.env;

async function login(page) {
  await page.goto('/wp-admin/');

  if (page.url().includes('wp-login.php')) {
    await page.locator('#user_login').fill(WP_USERNAME);
    await page.locator('#user_pass').fill(WP_PASSWORD);
    await Promise.all([
      page.waitForURL(/\/wp-admin\/?/),
      page.locator('#wp-submit').click(),
    ]);
  }
}

function fixturePath(relativePath) {
  return path.resolve(__dirname, '..', 'fixtures', relativePath);
}

async function uploadFromMediaNew(page, filePath) {
  await page.goto('/wp-admin/media-new.php');

  await Promise.all([
    page.waitForResponse((response) => {
      return response.url().includes('/wp-admin/async-upload.php') && response.request().method() === 'POST';
    }),
    page.locator('input[type="file"]').first().setInputFiles(filePath),
  ]);
}

async function uploadFromMediaGrid(page, filePath) {
  await page.goto('/wp-admin/upload.php?mode=grid');

  const uploadResponsePromise = page.waitForResponse((response) => {
    return response.url().includes('/wp-admin/async-upload.php') && response.request().method() === 'POST';
  });

  await page.locator('input[type="file"]').first().setInputFiles(filePath);

  const uploadResponse = await uploadResponsePromise;
  return uploadResponse.headers()['x-wp-upload-attachment-id'] || '';
}

module.exports = {
  fixturePath,
  login,
  uploadFromMediaGrid,
  uploadFromMediaNew,
};
