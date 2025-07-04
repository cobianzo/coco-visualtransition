import { addFilter } from '@wordpress/hooks';
import { BlockAttributes } from '@wordpress/blocks';

/**
 * Frontend;
 * Adds the classes for the core/group block.
 * Only on the Frontend.
 * The backend class (in the editor) is added with the controls.
 */

interface ExtraProps {
	className?: string;
	[key: string]: string | undefined;
}

const newClassesForCoreGroup = (
	extraProps: ExtraProps,
	blockType: { name: string },
	attributes: BlockAttributes
): ExtraProps => {
	if (blockType.name === 'core/group') {
		if (attributes.visualTransitionName && attributes.visualTransitionName !== '') {

			// adding the extra class(es)
			extraProps.className = (extraProps.className || '')
				+ ` coco-has-visualtransition`
				+ ` coco-visualtransition-${attributes.visualTransitionName}`;
		}
	}

	return extraProps;
};

addFilter(
	'blocks.getSaveContent.extraProps',
	'coco/add-custom-class-core-group',
	newClassesForCoreGroup
);
