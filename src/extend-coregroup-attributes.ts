import { addFilter } from '@wordpress/hooks';
import type { BlockConfiguration } from '@wordpress/blocks';


// ads  new attributes to the core/group block.
const customAttributes = {
	// dropdown
	visualTransitionName: {
		type: 'string',
		default: '',
	},
	patternHeight: {
		type: 'float',
		default: 0.08,
	},
	patternWidth: {
		type: 'float',
		default: 0.3333,
	},
	YOffset: {
		type: 'integer',
		default: 0,
	},
	typePattern: {
		type: 'string',
		default: '%', // Allowed: '%' or 'px'
	},
	onlyDesktop: {
		type: 'boolean',
		default: false,
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
