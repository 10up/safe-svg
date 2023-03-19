import { optimize } from 'svgo/lib/svgo';
import { select, subscribe } from '@wordpress/data';

(function () {
	const ajaxUrl = new URL(safeSvgParams.ajaxUrl); // eslint-disable-line no-undef
	const svgoParams = JSON.parse(safeSvgParams.svgoParams); // eslint-disable-line no-undef

    if (!ajaxUrl || !svgoParams) {
        return;
    }

    const context = safeSvgParams?.context; // eslint-disable-line no-undef
    const safeSvgCookie = 'safesvg-optimize';
    const safeSvgCookieAttr = 'Secure;SameSite=strict;path=/';

	/**
	 * Optimizes the SVG and prepares the parameters for the AJAX call.
	 *
	 * @param {string} svgUrl - The URL of the SVG file.
	 * @param {string} data - The SVG contents.
	 * @returns {object}
	 */
	const ajaxUrlParams = (svgUrl, data) => {
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
			optimized_svg: optimizedString,
			svg_nonce: safeSvgParams.nonce, // eslint-disable-line no-undef
		};
	};

    /**
     * If we are on the media library page, and the user has just uploaded an SVG file using the standard browser uploader, optimize it.
     */
	if ('upload.php' === context) {
		const shouldOptimizeSvg = document.cookie
			.split("; ")
			.find((row) => row.startsWith(safeSvgCookie + '='))
			?.split("=")[1];
        // Check if the cookie exists and is set to 1.
		if (shouldOptimizeSvg && '1' === shouldOptimizeSvg) {
            // Add a delay to make sure that, if we are on the Grid view, the markup has been updated.
			setTimeout(() => {
                // Check if we are on the Grid view or the List view (if drag and drop is supported, then we are on the Grid view).
				const imageSelector = document.body.classList.contains('supports-drag-drop') ? '.attachment img' : 'table.media tbody tr:first-child img';
				const image = document.querySelector(imageSelector);
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
									document.cookie = safeSvgCookie + '=;expires=Thu, 01 Jan 1970 00:00:00 UTC;' + safeSvgCookieAttr;
								});
						});
				}
			}, 500);


		}
	}

    /**
     * If we are on the "Upload New Media" page (`wp-admin/media-new.php`), and the user has just uploaded an SVG file, optimize it.
     */
	if ('media-new.php' === context) {
		const form = document.querySelector('#file-form');
		if (form.classList.contains('html-uploader')) {
			// We are on the browser uploader, so it's not possible to optimize on the fly. Instead, set a cookie to be checked after submission.
			form.addEventListener('submit', function (event) {
				const fileInput = document.querySelector('#async-upload');
				if (fileInput.files[0].type === 'image/svg+xml' || fileInput.files[0].name.endsWith('.svg')) {
					document.cookie = safeSvgCookie + '=1;' + safeSvgCookieAttr;
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
	 * Hook into the WordPress Uploader and optimize the SVG.
	 */
    if(wp.Uploader !== undefined) {
        Object.assign(wp.Uploader.prototype, {
            // Run on a successful upload.
            success(attachment) {
                const svgUrl = attachment?.attributes?.url;
                if (!svgUrl || attachment?.attributes?.subtype !== 'svg+xml') {
                    return;
                }

                // Get the SVG data from the file's URL and optimize.
                fetch(svgUrl, { method: 'GET' })
                    .then((response) => response.text())
                    .then((response) => {
                        const params = ajaxUrlParams(svgUrl, response);
                        if (!params) {
                            return;
                        }
                        // Make an AJAX call to update the SVG file with the optimized contents.
                        ajaxUrl.search = new URLSearchParams(params);
                        fetch(ajaxUrl, { method: 'GET' })
                            .then((ajaxResponse) => ajaxResponse)
                            .then((ajaxResponse) => {
                                if (ajaxResponse?.status !== 200) {
                                    return;
                                }

                                // Refresh the uploader window to update the file size.
                                if (wp.media.frame.content.get() !== null && wp.media.frame.content.get() !== undefined) {
                                    wp.media.frame.content
                                        .get()
                                        .collection?.props?.set({ ignore: +new Date() });
                                    if(wp.media.frame.content.get().options.selection !== undefined) {
                                        wp.media.frame.content.get().options.selection.reset();
                                    }
                                } else {
                                    wp.media.frame.library.props.set({ ignore: +new Date() });
                                }
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
							fetch(svgUrl, { method: 'GET', cache: 'no-store' })
								.then((response) => response.text())
								.then((response) => {
									const params = ajaxUrlParams(svgUrl, response);
									if (!params) {
										return;
									}
									// Make an AJAX call to update the SVG file with the optimized contents.
									ajaxUrl.search = new URLSearchParams(params);
									fetch(ajaxUrl, { method: 'GET' }).then(
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
