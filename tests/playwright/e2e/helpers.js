const path = require('path');
const { WP_USERNAME = 'admin', WP_PASSWORD = 'password' } = process.env;

async function login(requestUtils) {
  await requestUtils.login({
    username: WP_USERNAME,
    password: WP_PASSWORD,
  });
}

function fixturePath(relativePath) {
  return path.resolve(__dirname, '..', 'fixtures', relativePath);
}

async function uploadFromMediaNew(admin, page, filePath) {
  await admin.visitAdminPage('media-new.php');

  await Promise.all([
    page.waitForResponse((response) => {
      return response.url().includes('/wp-admin/async-upload.php') && response.request().method() === 'POST';
    }),
    page.locator('input[type="file"]').first().setInputFiles(filePath),
  ]);
}

async function uploadFromMediaGrid(admin, page, filePath) {
  await admin.visitAdminPage('upload.php', 'mode=grid');

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
