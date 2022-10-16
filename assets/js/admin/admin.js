import {optimize} from "svgo/lib/svgo";
import {select, subscribe} from '@wordpress/data';

(function () {
    const ajaxUrl = new URL(safeSvgParams.ajaxUrl);
    const svgoParams = JSON.parse(safeSvgParams.svgoParams);

    if (!ajaxUrl || !svgoParams) {
        return;
    }

    /**
     * Optimizes the SVG and prepares the parameters for the AJAX call.
     * @param svgUrl - The URL of the SVG file.
     * @param data - The SVG contents.
     */
    const ajaxUrlParams = (svgUrl, data) => {
        // Run the SVGO optimizer to get the optimized SVG contents.
        const optimized = optimize(data, svgoParams);
        const optimizedString = optimized?.data;
        // Do not proceed if the optimized string is empty or the same as the initial data, and therefore already optimized.
        if (!optimizedString || (data === optimizedString)) {
            return null;
        }

        // Prepare the parameters for the AJAX Call.
        return {
            action: 'safe_svg_optimize',
            svg_url: svgUrl,
            optimized_svg: optimizedString,
            svg_nonce: safeSvgParams.nonce
        };
    };

    /**
     * Hook into the WordPress Uploader and optimize the SVG.
     */
    Object.assign(
        wp.Uploader.prototype, {
            // Run on a successful upload.
            success: function (attachment) {

                const svgUrl = attachment?.attributes?.url;
                if (!svgUrl || 'svg+xml' !== attachment?.attributes?.subtype) {
                    return;
                }

                // Get the SVG data from the file's URL and optimize.
                fetch(svgUrl, {method: 'GET'})
                    .then((response) => response.text())
                    .then((response) => {

                        const params = ajaxUrlParams(svgUrl, response);
                        if (!params) {
                            return;
                        }
                        // Make an AJAX call to update the SVG file with the optimized contents.
                        ajaxUrl.search = new URLSearchParams(params);
                        fetch(ajaxUrl, {method: 'GET'})
                            .then((ajaxResponse) => ajaxResponse)
                            .then((ajaxResponse) => {
                                if (200 !== ajaxResponse?.status) {
                                    return;
                                }

                                // Refresh the uploader window to update the file size.
                                if (wp.media.frame.content.get() !== null) {
                                    wp.media.frame.content.get().collection.props.set({ignore: (+new Date())});
                                    wp.media.frame.content.get().options.selection.reset();
                                } else {
                                    wp.media.frame.library.props.set({ignore: (+new Date())});
                                }

                            });
                    });
            }
        }
    );

    /**
     * Optimize the SVGs inserted in the editor.
     * This takes care of SVGs uploaded via direct upload, without using the Media Library.
     */
    const {isSavingPost, getPostEdits} = select('core/editor');

    subscribe(() => {
        if (isSavingPost()) {
            let changes = getPostEdits();
            for (const changedBlock of changes.blocks) {

                // Run only if this is a new core image block.
                if ('core/image' === changedBlock.name && !changedBlock?.originalContent) {
                    const svgUrl = changedBlock?.attributes?.url;

                    // Run only if the image is an SVG that has not been optimized yet.
                    if (!svgUrl || !svgUrl.endsWith('.svg')) {
                        return;
                    }

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
                            fetch(ajaxUrl, {method: 'GET'})
                                .then((ajaxResponse) => ajaxResponse);
                        });
                }
            }
        }
    });
})()
