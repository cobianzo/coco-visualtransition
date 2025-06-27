// generic to avoid warnings about missing types
import * as React from '@wordpress/element';
import { useState, useEffect } from "@wordpress/element";

// WordPress dependencies
import { addFilter } from "@wordpress/hooks";
import { createHigherOrderComponent } from "@wordpress/compose";
import { InspectorControls } from "@wordpress/block-editor";
import { PanelBody, CheckboxControl, SelectControl } from "@wordpress/components";
import { Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

// Internal dependencies
import patterns from './patterns.json';
import { getInlineCssSvg, appendInlineCss, deleteInlineCss } from './add-cssinline-editor';

// Types
import { BlockEditProps } from "@wordpress/block-editor";
interface CustomAttributes {
	visualTransitionName?: string;
}

// Crear componente HOC para extender el panel de Inspector
const newCoreBlock = createHigherOrderComponent(
	(BlockEdit: BlockEditProps<CustomAttributes>) =>
		(props: BlockEditProps<CustomAttributes>) => {

		if (props.name !== "core/group") {
			return <BlockEdit {...props} />;
		}

		// init variables for the controls
		const { attributes, setAttributes } = props;
		const { visualTransitionName } = attributes;

		// internal state for checkbox
		const [checkBoxOn, setCheckBoxOn] = useState(
			visualTransitionName !== "",
		);

		// now the extra classes in the editor depending on the attributes.
		// Only update className if it has changed to avoid unnecessary re-renders
		useEffect( () => {

			console.log('%c TODELETE: we create/update the style for the block', 'font-size:1rem;color:pink', props.clientId, props.attributes)

			const patternName = props.attributes.visualTransitionName;
			if (checkBoxOn && patternName.length) {
				getInlineCssSvg(patternName, props.clientId, props.attributes).then( (inlineCSSandSVG) => {
					appendInlineCss(props.clientId, inlineCSSandSVG);
				});
			}else {
				deleteInlineCss(props.clientId);
			}


		}, [props.attributes, checkBoxOn, visualTransitionName]);

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody title="Visual Transition" initialOpen={true}>
						<CheckboxControl
							label={__("Enable Visual Transition", "coco-visualtransition")}
							checked={checkBoxOn}
							onChange={(value: boolean) => setCheckBoxOn(value)
							}
						/>

						{checkBoxOn && (
							<SelectControl
								label={__("Select Transition Effect", "coco-visualtransition")}
								value={visualTransitionName}
								options={ [ { 'label': '---', value: '' },  ...patterns.map( ( pattern ) => ( { 'label': pattern.label, value: pattern.value } ))]}
								onChange={(value: string) =>
									setAttributes({ visualTransitionName: value })
								}
							/>
						)}
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	},
	"cocoCustomCoreBlock",
);

addFilter("editor.BlockEdit", "coco/extend-group-inspector", newCoreBlock);
