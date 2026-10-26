import './frontend.scss';

/**
 * Attach shadow roots the HTML parser didn't.
 */
const SELECTOR = '.safe-svg-shadow-host > template[shadowrootmode]';

/**
 * Move a template's content into a shadow root on its parent.
 *
 * @param {HTMLTemplateElement} template The declarative shadow root template.
 */
const attach = ( template ) => {
	const host = template.parentElement;

	if ( ! host || host.shadowRoot ) {
		return;
	}

	try {
		const mode =
			template.getAttribute( 'shadowrootmode' ) === 'closed'
				? 'closed'
				: 'open';

		host.attachShadow( { mode } ).appendChild( template.content );
		template.remove();
	} catch ( e ) {
		// The host can't take a shadow root. Leave the template as it is rather
		// than moving the SVG into the light DOM, where its CSS would leak.
	}
};

/**
 * Attach the templates the server rendered, in browsers that don't do it natively.
 */
const start = () => {
	document.querySelectorAll( SELECTOR ).forEach( attach );
};

if (
	! Object.prototype.hasOwnProperty.call(
		window.HTMLTemplateElement.prototype,
		'shadowRootMode'
	)
) {
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}
