import { useBlockProps } from '@wordpress/block-editor';

/**
 * @return string Dynamic SVG icon block HTML.
 */
const SafeSVGBlockSave = ( { attributes } ) => {
    const {
        svgURL,
        imageWidth,
        imageHeight,
        dimensionWidth,
        dimensionHeight,
        alignment
    } = attributes;
    const blockProps = useBlockProps.save();
    return (
        <div { ...blockProps }
             style={{
                 maxWidth: '100%',
                 textAlign: alignment
             }}
        >
            {svgURL &&
            <svg
                style={{
                    width: dimensionWidth,
                    height: dimensionHeight,
                }}
            >
                <image
                    xlinkHref={svgURL}
                    src={svgURL}
                    width="100%"
                />
            </svg>
            }
        </div>
    );
};

export default SafeSVGBlockSave;
