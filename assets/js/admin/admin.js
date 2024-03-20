/* global safeSvgParams */
import {optimize} from 'svgo/lib/svgo';
import {select, subscribe} from '@wordpress/data';

(function () {
    const ajaxUrl = new URL(safeSvgParams.ajaxUrl);
    const svgoParams = safeSvgParams.svgoParams;

    if (!ajaxUrl || !svgoParams) {
        return;
    }

    const context = safeSvgParams?.context;
    const safeSvgCookie = 'safesvg-optimize';

    /**
     * Optimizes the SVG and prepares the parameters for the AJAX call.
     *
     * @param {string} svgUrl - The URL of the SVG file.
     * @param {int} svgId - The ID of the SVG file.
     * @param {string} data - The SVG contents.
     * @returns {object}
     */
    const ajaxUrlParams = (svgUrl, data, svgId = 0) => {
        // Run the SVGO optimizer to get the optimized SVG contents.
        const optimized = optimize(data, svgoParams);
        const optimizedString = optimized?.data;
        // Do not proceed if the optimized string is empty or the same as the initial data, and therefore already optimized.
        if (!optimizedString || data === optimizedString) {
            return null;
        }

        // Prepare the parameters for the AJAX Call.
        return {
            action: 'safe_svg_optimize',
            svg_url: svgUrl,
            svg_id: svgId ?? 0,
            optimized_svg: optimizedString,
            svg_nonce: safeSvgParams.nonce,
        };
    };

    /**
     * Trigger a refresh on the uploader window to update the file size.
     */
    const refreshMediaUploaderWindow = () => {
       if(typeof wp.media === 'undefined') {
           return;
       }
        if (wp.media.frame.content.get() !== null && wp.media.frame.content.get() !== undefined) {
            wp.media.frame.content
                .get()
                .collection?.props?.set({ignore: +new Date()});
            if (wp.media.frame.content.get().options.selection !== undefined) {
                wp.media.frame.content.get().options.selection.reset();
            }
        } else {
            wp.media.frame.library.props.set({ignore: +new Date()});
        }
    }

    /**
     * Wait for the elements to be available in the DOM.
     * @param selectors
     * @returns {Promise<unknown>}
     */
    const waitForElements = (selectors) => {
        return new Promise(resolve => {
            const elements = selectors.map(selector => document.querySelector(selector));
            if (elements.some(element => element !== null)) {
                return resolve(elements);
            }
            const observer = new MutationObserver(mutations => {
                const elements = selectors.map(selector => document.querySelector(selector));
                if (elements.some(element => element !== null)) {
                    resolve(elements);
                    observer.disconnect();
                }
            });
            observer.observe(document.body, {childList: true, subtree: true});
        });
    }

    /**
     * If we are on the media library page, and the user has just uploaded an SVG file using the standard browser uploader, optimize it.
     */
    if ('upload.php' === context) {
        const shouldOptimizeSvg = wpCookies.get(safeSvgCookie);

        // Check if the cookie exists and is set to 1.
        if (shouldOptimizeSvg && '1' === shouldOptimizeSvg) {
            // Check selectors for both Grid and List view.
            const imageSelectors = ['.attachment img', 'table.media tbody tr:first-child img'];
            waitForElements(imageSelectors).then(unfilteredImages => {
                // Only the one selector can be found, while the other will return `null`, so we filter out the null values.
                const images = unfilteredImages.filter(item => item !== null);
                const image = images[0];
                // Proceed only if the image is an SVG.
                if (image?.src && image.src.endsWith('.svg')) {
                    fetch(image.src, {method: 'GET'})
                        .then((response) => response.text())
                        .then((response) => {
                            const params = ajaxUrlParams(image.src, response);
                            if (!params) {
                                return;
                            }
                            // Make an AJAX call to update the SVG file with the optimized contents.
                            ajaxUrl.search = new URLSearchParams(params);
                            fetch(ajaxUrl, {method: 'GET'})
                                .then((ajaxResponse) => ajaxResponse)
                                .then((ajaxResponse) => {
                                    if (ajaxResponse?.status !== 200) {
                                        return;
                                    }
                                    // Remove the cookie.
                                    wpCookies.remove(safeSvgCookie, '/', '', true);
                                    refreshMediaUploaderWindow();
                                });
                        });
                }
            });
        }
    }

    /**
     * Optimize SVGs uploaded from the "Upload New Media" screen (`wp-admin/media-new.php`).
     * @param {Element} form
     */
    const optimizeFromUploadNewMedia = (form) => {
        if (form && form.classList.contains('html-uploader')) {
            // We are on the browser uploader, so it's not possible to optimize on the fly. Instead, set a cookie to be checked after submission.
            form.addEventListener('submit', function (event) {
                const fileInput = document.querySelector('#async-upload');
                if (fileInput.files[0].type === 'image/svg+xml' || fileInput.files[0].name.endsWith('.svg')) {
                    wpCookies.set(safeSvgCookie, '1', 'Session', '/', '', true);
                }
            });
        } else {
            // We are on the multi-file uploader, so we observe for new entries and if there is an SVG file, we optimize it.
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach((node) => {
                            if (node.nodeType === Node.ELEMENT_NODE && node.matches('.media-item-wrapper')) {
                                const image = node.querySelector('img');
                                if (image?.src && image.src.endsWith('.svg')) {
                                    fetch(image.src, {method: 'GET'})
                                        .then((response) => response.text())
                                        .then((response) => {
                                            const params = ajaxUrlParams(image.src, response);
                                            if (!params) {
                                                return;
                                            }
                                            // Make an AJAX call to update the SVG file with the optimized contents.
                                            ajaxUrl.search = new URLSearchParams(params);
                                            fetch(ajaxUrl, {method: 'GET'})
                                                .then((ajaxResponse) => ajaxResponse)
                                        });
                                }
                            }
                        });
                    }
                });
            });
            observer.observe(document.body, {childList: true, subtree: true});
        }
    }

    /**
     * If we are on the "Upload New Media" page (`wp-admin/media-new.php`), and the user has just uploaded an SVG file, optimize it.
     */
    if ('media-new.php' === context) {
        const form = document.querySelector('#file-form');
        optimizeFromUploadNewMedia(form);

        // If the user switches between the browser uploader and the multi-file uploader, we need to re-run the optimization.
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.attributeName === 'class') {
                    optimizeFromUploadNewMedia(form);
                }
            });
        });
        observer.observe(form, {attributes: true});
    }

    /**
     * Hook into the WordPress Uploader and optimize the SVG.
     */
    if (wp.Uploader !== undefined) {
        Object.assign(wp.Uploader.prototype, {
            // Run on a successful upload.
            success(attachment) {
                const svgUrl = attachment?.attributes?.url;
                const svgId = attachment?.attributes?.id;
                if (!svgUrl || attachment?.attributes?.subtype !== 'svg+xml') {
                    return;
                }

                // Get the SVG data from the file's URL and optimize.
                fetch(svgUrl, {method: 'GET'})
                    .then((response) => response.text())
                    .then((response) => {
                        const params = ajaxUrlParams(svgUrl, response, svgId);
                        if (!params) {
                            return;
                        }
                        // Make an AJAX call to update the SVG file with the optimized contents.
                        ajaxUrl.search = new URLSearchParams(params);
                        fetch(ajaxUrl, {method: 'GET'})
                            .then((ajaxResponse) => ajaxResponse)
                            .then((ajaxResponse) => {
                                if (ajaxResponse?.status !== 200) {
                                    return;
                                }
                                refreshMediaUploaderWindow();
                            });
                    });
            },
        });
    }

    /**
     * Check if a given URL is an SVG which should be optimized.
     *
     * @param {string} url - The URL of the SVG file.
     * @param {string} originalContent - The original content of the block.
     * @returns {string|*}
     */
    const maybeUnoptimizedSvg = (url, originalContent) => {
        const isSvg = url && url.endsWith('.svg');
        const isOptimized = undefined !== originalContent && originalContent.includes(url);
        // Run only if the image is an SVG that hasn't been optimized yet.
        if (!isSvg || isOptimized) {
            return '';
        }
        return url;
    };

    /**
     * Optimize the SVGs inserted in the editor.
     * This takes care of SVGs uploaded via direct upload, without using the Media Library.
     */
    const editorStore = select('core/editor');
    const validBlocks = ['core/image', 'core/media-text'];
    subscribe(() => {
        if (editorStore.isSavingPost()) {
            const changes = editorStore.getPostEdits();
            // make sure we have blocks to process
            if (!Array.isArray(changes.blocks) || !changes.blocks.length) {
                return;
            }
            for (const changedBlock of changes.blocks) {
                const blockName = changedBlock?.name ?? '';
                const innerBlocks = changedBlock?.innerBlocks ?? [];

                // Check if a block is a nested block and contains images.
                const isNestedWithImages =
                    innerBlocks.length &&
                    Array.isArray(innerBlocks) &&
                    innerBlocks.some((block) => block.name === 'core/image');

                if (validBlocks.includes(blockName) || isNestedWithImages) {
                    let svgUrls = [];
                    if (validBlocks.includes(blockName)) {
                        const url =
                            blockName === 'core/media-text'
                                ? changedBlock?.attributes?.mediaUrl
                                : changedBlock?.attributes?.url;
                        svgUrls.push(maybeUnoptimizedSvg(url, changedBlock?.originalContent));
                    }
                    if (innerBlocks) {
                        for (const innerBlock of innerBlocks) {
                            if (innerBlock?.name === 'core/image') {
                                svgUrls.push(
                                    maybeUnoptimizedSvg(
                                        innerBlock?.attributes?.url,
                                        innerBlock?.originalContent,
                                    ),
                                );
                            }
                        }
                    }

                    svgUrls = svgUrls.filter((n) => n); // Remove empty values.
                    if (svgUrls.length) {
                        for (const svgUrl of svgUrls) {
                            // Get the SVG data from the file's URL and optimize.
                            fetch(svgUrl, {method: 'GET', cache: 'no-store'})
                                .then((response) => response.text())
                                .then((response) => {
                                    const params = ajaxUrlParams(svgUrl, response);
                                    if (!params) {
                                        return;
                                    }
                                    // Make an AJAX call to update the SVG file with the optimized contents.
                                    ajaxUrl.search = new URLSearchParams(params);
                                    fetch(ajaxUrl, {method: 'GET'}).then(
                                        (ajaxResponse) => ajaxResponse,
                                    );
                                });
                        }
                    }
                }
            }
        }
    });
})();
