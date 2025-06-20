import { addFilter } from '@wordpress/hooks';
import type { BlockConfiguration } from '@wordpress/blocks';


// ads two new attributes to the core/group block.
const customAttributes = {
	// dropdown
	visualTransitionName: {
		type: 'string',
		default: '',
	},
};

/**
 *
 */
function extendGroupBlockSettings(
	settings: BlockConfiguration,
	name: string
): BlockConfiguration {

	// only for Group block.
	if (name !== 'core/group') {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			...customAttributes,
		},
	};

}

// applies the modifcator with the new attributes.
addFilter(
	'blocks.registerBlockType',
	'coco-visualtransition/extend-core-group',
	extendGroupBlockSettings
);
