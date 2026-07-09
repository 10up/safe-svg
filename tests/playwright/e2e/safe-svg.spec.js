const path = require('path');
const { test, expect } = require('@wordpress/e2e-test-utils-playwright');
const { fixturePath, login, uploadFromMediaGrid, uploadFromMediaNew } = require('./helpers');

const SVG_ICON_FILE = path.resolve(__dirname, '../../../.wordpress-org/icon.svg');
const CUSTOM_SVG_FILE = fixturePath('custom.svg');
const BAD_XML_FILE = fixturePath('badXmlTestOne.svg');
const FILTER_TEST_PLUGIN = 'safe-svg-playwright-test-plugin';
const OPTIMIZER_TEST_PLUGIN = 'safe-svg-playwright-optimizer-test-plugin';

test.describe('Safe SVG Tests', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('Admin can upload SVG image via add new media file', async ({ page }) => {
    await uploadFromMediaNew(page, SVG_ICON_FILE);

    await expect(page.locator('.media-item .media-list-title, .media-item .title')).toContainText('icon');
    await expect(page.locator('.media-item a.edit-attachment')).toContainText('Edit');
  });

  test('Admin can upload SVG image via the media grid', async ({ page }) => {
    const attachmentId = await uploadFromMediaGrid(page, SVG_ICON_FILE);

    await expect(page.locator(`.attachments .attachment[data-id="${attachmentId}"]`)).toBeVisible();
  });

  test('Admin can add SVG block to a post', async ({ page, admin, editor, requestUtils }) => {
    await requestUtils.uploadMedia(SVG_ICON_FILE);

    await admin.createNewPost({ title: 'SVG Block Test' });
    await editor.insertBlock({ name: 'safe-svg/svg-icon' });

    await editor.canvas.getByRole('button', { name: 'Media Library' }).click();
    await page.locator('#menu-item-browse').click();
    await page.locator('.attachments-wrapper li:first-child .thumbnail').click();
    await page.locator('.media-modal .media-button-select').click();

    await expect(editor.canvas.locator('.wp-block-safe-svg-svg-icon')).toBeVisible();
  });

  test('SVG should be sanitized', async ({ page, requestUtils }) => {
    await requestUtils.deactivatePlugin(FILTER_TEST_PLUGIN);

    const media = await requestUtils.uploadMedia(CUSTOM_SVG_FILE);
    const response = await page.request.get(media.source_url);
    const svgBody = await response.text();

    expect(svgBody).not.toContain('customTestTag');
    expect(svgBody).not.toContain('customTestAttribute');
  });

  test('Plugin should allow modify allowed tags and attributes', async ({ page, requestUtils }) => {
    await requestUtils.activatePlugin(FILTER_TEST_PLUGIN);

    const media = await requestUtils.uploadMedia(CUSTOM_SVG_FILE);
    const response = await page.request.get(media.source_url);
    const svgBody = await response.text();

    expect(svgBody).toContain('customTestTag');
    expect(svgBody).toContain('customTestAttribute');

    await requestUtils.deactivatePlugin(FILTER_TEST_PLUGIN);
  });

  test('Bad formatted SVG should not upload and should give an error', async ({ page }) => {
    await uploadFromMediaNew(page, BAD_XML_FILE);
    await expect(page.locator('.media-item .error-div.error')).toContainText('has failed to upload');
  });

  test('Plugin should not break the block editor when optimizer enabled', async ({ admin, requestUtils }) => {
    await requestUtils.deactivatePlugin(FILTER_TEST_PLUGIN);
    await requestUtils.activatePlugin(OPTIMIZER_TEST_PLUGIN);

    await admin.createNewPost({ title: 'Hello World' });
  });

  test('Output of wp_get_attachment_image should use full svg dimensions', async ({ page, requestUtils }) => {
    await requestUtils.activatePlugin(FILTER_TEST_PLUGIN);
    const media = await requestUtils.uploadMedia(SVG_ICON_FILE);

    await page.goto(`/?safe_svg_attachment_id=${media.id}`);

    await expect(page.locator('#thumbnail-image')).toHaveAttribute('width', '256');
    await expect(page.locator('#thumbnail-image')).toHaveAttribute('height', '256');
    await expect(page.locator('#medium-image')).toHaveAttribute('width', '256');
    await expect(page.locator('#medium-image')).toHaveAttribute('height', '256');
    await expect(page.locator('#large-image')).toHaveAttribute('width', '256');
    await expect(page.locator('#large-image')).toHaveAttribute('height', '256');
    await expect(page.locator('#full-image')).toHaveAttribute('width', '256');
    await expect(page.locator('#full-image')).toHaveAttribute('height', '256');
    await expect(page.locator('#custom-image')).toHaveAttribute('width', '256');
    await expect(page.locator('#custom-image')).toHaveAttribute('height', '256');

    await requestUtils.deactivatePlugin(FILTER_TEST_PLUGIN);
  });

  test('Output of get_image_tag should use custom dimensions', async ({ page, requestUtils }) => {
    await requestUtils.activatePlugin(FILTER_TEST_PLUGIN);
    const media = await requestUtils.uploadMedia(SVG_ICON_FILE);

    await page.goto(`/?safe_svg_attachment_id=${media.id}`);

    await expect(page.locator(`.size-thumbnail.wp-image-${media.id}`)).toHaveAttribute('width', '150');
    await expect(page.locator(`.size-thumbnail.wp-image-${media.id}`)).toHaveAttribute('height', '150');
    await expect(page.locator(`.size-medium.wp-image-${media.id}`)).toHaveAttribute('width', '300');
    await expect(page.locator(`.size-medium.wp-image-${media.id}`)).toHaveAttribute('height', '300');
    await expect(page.locator(`.size-large.wp-image-${media.id}`)).toHaveAttribute('width', '1024');
    await expect(page.locator(`.size-large.wp-image-${media.id}`)).toHaveAttribute('height', '1024');
    await expect(page.locator(`.size-full.wp-image-${media.id}`)).toHaveAttribute('width', '256');
    await expect(page.locator(`.size-full.wp-image-${media.id}`)).toHaveAttribute('height', '256');
    await expect(page.locator(`.size-100x120.wp-image-${media.id}`)).toHaveAttribute('width', '100');
    await expect(page.locator(`.size-100x120.wp-image-${media.id}`)).toHaveAttribute('height', '100');

    await requestUtils.deactivatePlugin(FILTER_TEST_PLUGIN);
  });
});
