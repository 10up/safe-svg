import { useBlockProps } from '@wordpress/block-editor';

/**
 * See https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#save
 *
 * @return {null} Dynamic blocks do not save the HTML.
 */
const SafeSVGBlockSave = ( { attributes } ) => {
    // const blockProps = useBlockProps();
    const { svgURL } = attributes;
    console.log( svgURL );
    return (
        <div >
            okayyy
            <svg width="90" height="90">
                <image xlinkHref={svgURL} src={svgURL} width="90" height="90"/>
            </svg>
        </div>
    );
};

export default SafeSVGBlockSave;
