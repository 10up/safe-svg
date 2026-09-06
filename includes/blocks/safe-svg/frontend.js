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
 * Upgrade every unattached template within a root.
 *
 * @param {ParentNode} root The subtree to search.
 */
const upgrade = ( root ) => {
	if ( root.matches && root.matches( SELECTOR ) ) {
		attach( root );
	}

	if ( root.querySelectorAll ) {
		root.querySelectorAll( SELECTOR ).forEach( attach );
	}
};

/**
 * Upgrade what's on the page, then watch for anything added later.
 */
const start = () => {
	upgrade( document );

	// Content can be injected at any point in a page's life, so keep watching.
	new MutationObserver( ( mutations ) => {
		mutations.forEach( ( { addedNodes } ) => {
			addedNodes.forEach( ( node ) => {
				if ( node.nodeType === Node.ELEMENT_NODE ) {
					upgrade( node );
				}
			} );
		} );
	} ).observe( document.documentElement, {
		childList: true,
		subtree: true,
	} );
};

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', start );
} else {
	start();
}
