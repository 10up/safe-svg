import {optimize} from "svgo/lib/svgo";

(function () {
    const ajaxUrl = new URL(safeSvgParams.ajaxUrl);
    const svgoParams = JSON.parse(safeSvgParams.svgoParams);

    if (!ajaxUrl || !svgoParams) {
        return;
    }

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

                // Get the SVG data from its URL and optimize.
                fetch(svgUrl, {method: 'GET'})
                    .then((response) => response.text())
                    .then((response) => {

                        // Run the SVGO optimizer to get the optimized SVG contents.
                        const optimized = optimize(response, svgoParams);
                        const optimizedString = optimized?.data;
                        if (!optimizedString) {
                            return;
                        }

                        // Make an AJAX call to update the SVG file with the optimized contents.
                        ajaxUrl.search = new URLSearchParams({
                            action: 'safe_svg_optimize',
                            svg_url: svgUrl,
                            optimized_svg: optimizedString,
                        });
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

})();