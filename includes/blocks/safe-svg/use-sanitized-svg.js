/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';

/**
 * Nothing requested yet. A stable reference so setting it twice does not re-render.
 */
const EMPTY_SVG = { markup: '', url: '', error: '', isLoading: false };

/**
 * Fetch sanitized markup and the source URL for an attachment.
 *
 * @param {number} imageID Attachment ID of the SVG.
 * @return {{markup: string, url: string, error: string, isLoading: boolean}} The request state.
 */
const useSanitizedSvg = ( imageID ) => {
	const [ svg, setSvg ] = useState( EMPTY_SVG );

	useEffect( () => {
		if ( ! imageID ) {
			setSvg( EMPTY_SVG );
			return;
		}

		let cancelled = false;

		const apply = ( next ) => {
			if ( ! cancelled ) {
				setSvg( { ...EMPTY_SVG, ...next } );
			}
		};

		setSvg( { ...EMPTY_SVG, isLoading: true } );

		apiFetch( { path: `/safe-svg/v1/svg/${ imageID }` } )
			.then( ( data ) =>
				apply( {
					markup: data?.markup || '',
					url: data?.url || '',
				} )
			)
			.catch( ( requestError ) =>
				apply( { error: requestError?.code || 'safe_svg_request_failed' } )
			);

		return () => {
			cancelled = true;
		};
	}, [ imageID ] );

	return svg;
};

export default useSanitizedSvg;
