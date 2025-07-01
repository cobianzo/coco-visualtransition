

/**
 * Depending on the context, the editor for Gutenberg, (where the blocs are edited)
 * can be inside an ifram or can be inside a dic.editor-styles-wrapper
 *
 * We evaluate both cases
 */
export function getEditorCanvas(): HTMLDivElement | HTMLBodyElement | null {
    // Try to get the editor canvas from an iframe first
    const iframe = document.querySelector('iframe[name="editor-canvas"]');
    const iframeDocument = (iframe as HTMLIFrameElement)?.contentDocument || (iframe as HTMLIFrameElement)?.contentWindow?.document;

    if (iframeDocument) {
        return iframeDocument.querySelector('.editor-styles-wrapper') as HTMLBodyElement; // returns the body HTML element
    }

    // If no iframe is found, look for the editor in the main document
    const mainEditor = document.querySelector('.editor-styles-wrapper');
    if (!mainEditor) {
        console.error('Editor container not found');
        return null;
    }

    return mainEditor as HTMLDivElement;

}