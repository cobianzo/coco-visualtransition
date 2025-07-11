import apiFetch from '@wordpress/api-fetch';
import { getEditorCanvas } from './utils';


declare global {
	interface Window {
			cocoVisualTransition?: {
					nonce: string;
			};
	}
}

interface PatternAttributes {
    patternHeight?: number;
    patternWidth?: number;
    YOffset?: number;
}



/**
 * Makes an AJAX call to get inline CSS for SVG elements fro mthe php class
 *
 * @returns string Promise that resolves with the processed CSS
 */
export async function getInlineCssSvg(patternName: string, blockId: string, atts: PatternAttributes): Promise<string> {

	try {
			// check the PHP endpoint
			const css: string = await apiFetch({
					path: 'coco/v1/vtstyle',
					method: 'POST',
					data: {
							pattern_name: patternName,
							block_id: blockId,
							pattern_atts: atts //  ie. { patternHeight: 0.08, patternWidth: 0.2, YOffset: -40}
					}
			});

			if ( ! css || css === '') {
				throw new Error('Failed to generate visual transition styles');
			}

			return css;
	} catch (error) {
			console.error('Failed to fetch `coco/v1/vtstyle` inline CSS:', error, patternName, blockId, atts);
			throw new Error('Failed to generate visual transition styles');
	}
}

/* delete the style css and the svg that modify the core/block group, given the clientId  */
export function deleteInlineCss( id ) {
	const gutenbergEditor = getEditorCanvas();

	if (!gutenbergEditor){
			return;
	}

	// If the style and svg existed, we delete it
	gutenbergEditor.querySelector(`#${getIdContainer(id)}`)?.remove();
}

/**
 * Appends inline CSS and SVG styles to the editor iframe document
 *
 * @param {string} id - The unique identifier for the block
 * @param {string} cssAndSvg - The CSS and SVG markup to be injected
 * @returns {void} Nothing
 *
 *
 * @example
 * appendInlineCss('block-123', '<style>.my-style{}</style><svg>...</svg>');
 */
export function appendInlineCss( id: string, cssAndSvg: string) {

	// enter in the context of the editor, which is an iframe.
	const gutenbergEditor = getEditorCanvas();

	if (!gutenbergEditor) {
		console.error('Editor iframe document not found. Cant append Inline CSS');
		return;
	}
	// If the style and svg existed, we delete it
	deleteInlineCss(id);

	// Create a new div element
	const div = document.createElement('div');
	div.id = getIdContainer(id);
	div.innerHTML = cssAndSvg;

	//append the div with the <style> and the <svg>
	gutenbergEditor.appendChild(div);

}

function getIdContainer(id:string): string {
	return `coco-vt-${id}`;
}