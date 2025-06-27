import apiFetch from '@wordpress/api-fetch';


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

	return css;
}

/* delete the style css and the svg that modify the core/block group, given the clientId  */
export function deleteInlineCss( id ) {
	const iframe = document.querySelector('iframe');
	const iframeDocument = iframe?.contentDocument || iframe?.contentWindow?.document;
	if (!iframeDocument) {
			console.error('Editor iframe document not found');
			return;
	}

	// If the style and svg existed, we delete it
	iframeDocument.getElementById(getIdContainer(id))?.remove();
}

export function appendInlineCss( id: string, cssAndSvg: string) {

	// enter in the context of the editor, which is an iframe.
	const iframe = document.querySelector('iframe');
	const iframeDocument = iframe?.contentDocument || iframe?.contentWindow?.document;
	if (!iframeDocument) {
			console.error('Editor iframe document not found');
			return;
	}


	// If the style and svg existed, we delete it
	deleteInlineCss(id);

	// Create a new div element
	const div = document.createElement('div');
	div.id = getIdContainer(id);
	div.innerHTML = cssAndSvg;

	//append the div with the <style> and the <svg>
	iframeDocument.querySelector('html body')?.appendChild(div);

}

function getIdContainer(id:string): string {
	return `coco-vt-${id}`;
}