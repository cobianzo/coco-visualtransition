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
		useEffect(() => {
			const customClass = checkBoxOn ? `coco-has-visualtransition coco-visualtransition-${visualTransitionName}` : 'coco-nothing';
			const newClassName = `${props.attributes.className || ''} ${customClass}`.trim();

			if (props.attributes.className !== newClassName) {
				props.attributes.className = newClassName;
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
								options={ [ { 'label': '---', value: '' },  ...patterns]}
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
