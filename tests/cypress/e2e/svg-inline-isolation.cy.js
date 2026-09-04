/**
 * A `<style>` element inside an inline SVG is not scoped to the SVG: the browser
 * applies its rules to the whole document. The Inline SVG block therefore renders
 * SVGs that carry a stylesheet inside a shadow root, which the browser does scope,
 * and wraps the shadow host in an element with paint containment, so that CSS in
 * the shadow root can't reposition its own host over the page either.
 *
 * These tests use tests/cypress/fixtures/hostileStyle.svg, which tries both.
 *
 * The post is created through the REST API rather than the block editor UI. These
 * tests are about what the block renders on the front end, not how it is inserted,
 * and driving the editor's inserter couples them to Gutenberg markup that shifts
 * between WordPress versions.
 *
 * Note on the file name: Cypress runs specs alphabetically, and
 * tests/cypress/test-plugin/e2e-test-plugin.php refers to attachment ID 6, so the
 * assertions at the end of safe-svg.cy.js depend on how many files earlier specs
 * uploaded. This spec uploads media, so it has to sort after safe-svg.cy.js.
 */
describe( 'Inline SVG CSS isolation', () => {
	beforeEach( () => {
		cy.login();

		// Plugin activation persists between runs, and the SVGO optimizer rewrites
		// the CSS inside an SVG (dropping rules whose selectors match nothing within
		// it). Pin both test plugins off so these tests exercise the default setup.
		cy.deactivatePlugin( 'safe-svg-cypress-test-plugin' );
		cy.deactivatePlugin( 'safe-svg-cypress-optimizer-test-plugin' );
	} );

	/**
	 * Upload an SVG and publish a post containing the Inline SVG block for it.
	 *
	 * @param {string} title   Post title.
	 * @param {string} fixture Path to the SVG fixture to upload.
	 * @return {Cypress.Chainable} Wraps the created post (REST response body).
	 */
	const publishPostWithBlock = ( title, fixture ) =>
		cy.uploadMediaThroughGrid( fixture ).then( ( attachmentId ) =>
			// A REST request needs the cookie nonce; admin-ajax hands one out.
			cy
				.request( '/wp-admin/admin-ajax.php?action=rest-nonce' )
				.then( ( nonceResponse ) =>
					cy
						.request( {
							method: 'POST',
							url: '/wp-json/wp/v2/posts',
							headers: { 'X-WP-Nonce': nonceResponse.body },
							body: {
								title,
								status: 'publish',
								content: `<!-- wp:safe-svg/svg-icon {"imageID":${ attachmentId },"dimensionWidth":120,"dimensionHeight":120} /-->`,
							},
						} )
						.then( ( postResponse ) => postResponse.body )
				)
		);

	it( 'SVG CSS does not escape the block', () => {
		publishPostWithBlock(
			'Inline SVG isolation',
			'tests/cypress/fixtures/hostileStyle.svg'
		).then( ( post ) => {
			// The CSS is still served. It is isolated, not stripped, so this is what
			// proves the isolation is doing the work rather than the sanitizer.
			cy.request( `/?p=${ post.id }` )
				.its( 'body' )
				.should( 'contain', 'safe-svg-css-leak-probe' );

			cy.visit( `/?p=${ post.id }` );

			// The page is intact: the title is still rendered, so `body *
			// { display: none }` did not leak out of the SVG.
			cy.contains( 'Inline SVG isolation' ).should( 'be.visible' );

			cy.document().then( ( doc ) => {
				const win = doc.defaultView;

				expect(
					win.getComputedStyle( doc.body ).backgroundColor,
					'body background'
				).to.not.equal( 'rgb(1, 2, 3)' );
				expect(
					win.getComputedStyle( doc.body, '::before' ).content,
					'injected body::before'
				).to.not.contain( 'SAFE-SVG-CSS-LEAK' );
			} );
		} );
	} );

	it( 'SVG renders inside a shadow root', () => {
		publishPostWithBlock(
			'Inline SVG shadow root',
			'tests/cypress/fixtures/hostileStyle.svg'
		).then( ( post ) => {
			cy.visit( `/?p=${ post.id }` );

			cy.get( '.safe-svg-shadow-host' ).should( ( $host ) => {
				const host = $host[ 0 ];

				expect( host.shadowRoot, 'shadow root' ).to.not.equal( null );
				expect(
					host.shadowRoot.querySelector( 'svg' ),
					'SVG inside the shadow root'
				).to.not.equal( null );

				// Nothing is left in the light DOM to leak.
				expect(
					host.querySelector( 'template' ),
					'leftover template'
				).to.equal( null );
				expect(
					host.querySelector( 'svg' ),
					'SVG in the light DOM'
				).to.equal( null );
			} );

			cy.get( '.safe-svg-shadow-guard' )
				.should( 'have.attr', 'style' )
				.and( 'contain', 'contain: paint' );
		} );
	} );

	it( 'SVG cannot paint outside the space the block gave it', () => {
		publishPostWithBlock(
			'Inline SVG containment',
			'tests/cypress/fixtures/hostileStyle.svg'
		).then( ( post ) => {
			cy.visit( `/?p=${ post.id }` );

			cy.document().then( ( doc ) => {
				const win = doc.defaultView;
				const host = doc.querySelector( '.safe-svg-shadow-host' );
				const guard = doc.querySelector( '.safe-svg-shadow-guard' );
				const box = guard.getBoundingClientRect();

				// The host's layout box covers the viewport, because `:host` gave it
				// `position: fixed`. Paint containment on the guard means none of it is
				// actually drawn beyond the space the block gave the SVG.
				const hits = [];
				for ( let y = 0; y < win.innerHeight; y += 8 ) {
					for ( let x = 0; x < win.innerWidth; x += 8 ) {
						if ( doc.elementFromPoint( x, y ) === host ) {
							hits.push( [ x, y ] );
						}
					}
				}

				hits.forEach( ( [ x, y ] ) => {
					expect(
						x >= Math.floor( box.left ) &&
							x <= Math.ceil( box.right ) &&
							y >= Math.floor( box.top ) &&
							y <= Math.ceil( box.bottom ),
						`paint at ${ x },${ y } is inside the guard`
					).to.equal( true );
				} );
			} );
		} );
	} );

	it( 'SVG without a stylesheet is left in the light DOM', () => {
		publishPostWithBlock(
			'Inline SVG no stylesheet',
			'tests/cypress/fixtures/custom.svg'
		).then( ( post ) => {
			cy.visit( `/?p=${ post.id }` );

			// Nothing to isolate, so nothing changes: themes can still style the SVG.
			cy.get( '.wp-block-safe-svg-svg-icon svg' ).should( 'exist' );
			cy.get( '.safe-svg-shadow-host' ).should( 'not.exist' );
			cy.get( '.safe-svg-shadow-guard' ).should( 'not.exist' );
		} );
	} );
} );
