import { optimize } from 'svgo/lib/svgo';
import { select, subscribe } from '@wordpress/data';

(function () {
	const ajaxUrl = new URL(safeSvgParams.ajaxUrl); // eslint-disable-line no-undef
	const svgoParams = JSON.parse(safeSvgParams.svgoParams); // eslint-disable-line no-undef

	if (!ajaxUrl || !svgoParams) {
		return;
	}

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
