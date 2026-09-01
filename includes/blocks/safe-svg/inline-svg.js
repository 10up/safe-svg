/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element';

/**
 * The styles applied inside the shadow root.
 */
const SHADOW_STYLES =
	'svg{fill:currentColor;width:100%;height:100%;max-width:100%;max-height:100%}';

/**
 * Check whether an SVG carries its own stylesheet.
 *
 * @param {string} markup The SVG markup.
 * @return {boolean} True if the SVG contains a style element.
 */
const hasStylesheet = (markup) =>
	/<\s*(?:[a-z0-9_.\-]+:)?style\b/i.test(markup);

/**
 * Render an SVG inline, isolating it when it brings its own CSS.
 *
 * @param {Object} props        The component props.
 * @param {string} props.src    The SVG URL.
 * @param {number} props.width  Width to render the SVG at, in pixels.
 * @param {number} props.height Height to render the SVG at, in pixels.
 * @return {Function} The SVG host element.
 */
const InlineSvg = ({ src, width, height }) => {
	const hostRef = useRef(null);
	const attachedRef = useRef(false);
	const [markup, setMarkup] = useState('');

	useEffect(() => {
		if (!src) {
			setMarkup('');
			return undefined;
		}

		const controller = new AbortController();

		fetch(src, { signal: controller.signal })
			.then((response) => (response.ok ? response.text() : ''))
			.then(setMarkup)
			.catch(() => {
				// Aborted, or the file couldn't be read. Leave the preview empty.
			});

		return () => controller.abort();
	}, [src]);

	useEffect(() => {
		const host = hostRef.current;

		if (!host) {
			return;
		}

		// A shadow root can't be detached once attached, so once this host has
		// one it keeps rendering through it whatever the next SVG looks like.
		const shadow =
			host.shadowRoot ||
			(hasStylesheet(markup) ? host.attachShadow({ mode: 'open' }) : null);

		// The file was sanitized on upload, which is what makes it safe to inline
		// here, exactly as the front end inlines the same bytes.
		if (shadow) {
			attachedRef.current = true;
			shadow.innerHTML = `<style>${SHADOW_STYLES}</style>${markup}`;
			host.innerHTML = '';
		} else {
			host.innerHTML = markup;
		}

		const svg = (shadow || host).querySelector('svg');

		if (svg && width && height) {
			svg.setAttribute('style', `width: ${width}px; height: ${height}px;`);
		}
	}, [markup, width, height]);

	const isolated = attachedRef.current || hasStylesheet(markup);

	return (
		<span
			className="safe-svg-shadow-guard"
			style={{
				display: 'block',
				height: '100%',
				contain: isolated ? 'paint' : undefined,
			}}
		>
			<span
				ref={hostRef}
				className="safe-svg-shadow-host"
				style={{ display: 'block', height: '100%' }}
			/>
		</span>
	);
};

InlineSvg.propTypes = {
	src: PropTypes.string,
	width: PropTypes.number,
	height: PropTypes.number,
};

export default InlineSvg;
