/* global safe-svg_personalizer_params */
/* eslint-disable camelcase */
import './frontend.scss';

function setupRewardCall( blockId ) {
	const contentLinks = document.querySelectorAll(
		`#${ blockId } .safe-svg-send-reward`
	);
	contentLinks.forEach( function ( contentLink ) {
		contentLink.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			const eventId = this.getAttribute( 'data-eventid' );
			const isRewarded = this.getAttribute( 'data-rewarded' );
			const restURL = `${ safe_svgpersonalizer_params?.reward_endpoint }`;
			/* Send Reward to personalizer */
			fetch( restURL.replace( '{eventId}', eventId ), {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify( { rewarded: isRewarded } ),
			} ).catch( ( err ) => {
				// eslint-disable-next-line no-console
				console.error( err );
			} );
			window.location = this.href;
		} );
	} );
}

function safeSvgSessionGet( key ) {
	const cacheString = window.sessionStorage.getItem( key );
	if ( cacheString !== null ) {
		const cacheData = JSON.parse( cacheString );
		if ( new Date( cacheData.expiresAt ) > new Date() ) {
			return cacheData.value;
		}
	}
	return null;
}

function safeSvgSessionSet( key, value, expirationInMin ) {
	window.sessionStorage.setItem(
		key,
		JSON.stringify( {
			expiresAt: new Date(
				new Date().getTime() + 60000 * expirationInMin
			),
			value,
		} )
	);
}

document.addEventListener( 'DOMContentLoaded', function () {
	const safeSvg = document.querySelectorAll(
		'.safe-svg-recommended-block-wrap'
	);
	safeSvg.forEach( function ( safeSvgBlock ) {
		const blockId = safeSvgBlock.getAttribute( 'id' );
		const cached = safeSvgSessionGet( blockId );
		if ( cached !== null ) {
			safeSvgBlock.innerHTML = cached;
			setupRewardCall( blockId );
			return;
		}

		// Prepare request.
		const attrKey = safeSvgBlock.getAttribute( 'data-attr_key' );
		const ajaxURL = safe_svgpersonalizer_params?.ajax_url;
		const data = JSON.parse(
			document.getElementById( `attributes-${ attrKey }` ).textContent
		);
		data.action = 'safe_svgrender_recommended_content';
		data.security = safe_svgpersonalizer_params?.ajax_nonce;
		const payload = new URLSearchParams( data );
		if ( data.taxQuery && Object.keys( data.taxQuery ) ) {
			payload.delete( 'taxQuery' );
			Object.keys( data.taxQuery ).forEach( ( key ) => {
				if ( data.taxQuery[ key ] ) {
					data.taxQuery[ key ].forEach( ( ele ) => {
						payload.append( `taxQuery[${ key }][]`, ele );
					} );
				}
			} );
		}

		// Get recommended content.
		fetch( ajaxURL, {
			method: 'POST',
			headers: {
				'Content-Type':
					'application/x-www-form-urlencoded; charset=UTF-8',
			},
			body: payload,
		} )
			.then( ( response ) => {
				if ( ! response.ok ) {
					throw new Error( 'Something went wrong' );
				}
				return response.text();
			} )
			.then( ( result ) => {
				if ( result ) {
					safeSvgSessionSet( blockId, result, 60 ); // 1 hour expiry time.
				}
				document.getElementById( blockId ).innerHTML = result;
				setupRewardCall( blockId );
			} )
			.catch( ( error ) => {
				document.getElementById( blockId ).innerHTML = '';
				// eslint-disable-next-line no-console
				console.error( error );
			} );
	} );
} );
