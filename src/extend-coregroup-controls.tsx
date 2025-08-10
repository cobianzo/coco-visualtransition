// generic to avoid warnings about missing types
import * as React from '@wordpress/element';
import { useState, useEffect, useRef } from "@wordpress/element";

// WordPress dependencies
import { addFilter } from "@wordpress/hooks";
import { createHigherOrderComponent } from "@wordpress/compose";
import { InspectorControls } from "@wordpress/block-editor";
import { PanelBody, CheckboxControl, SelectControl, RangeControl, Notice } from "@wordpress/components";
import { Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

// Ensure translations are loaded for this domain
// @wordpress/i18n will automatically load translations from the JSON files

// Internal dependencies
import patterns from './patterns.json';
import { getInlineCssSvg, appendInlineCss, deleteInlineCss } from './add-cssinline-editor';

// Types
interface CustomAttributes {
	visualTransitionName?: string;
	tagName?: string;
	patternHeight?: number;
	patternWidth?: number;
	YOffset?: number;
	typePattern?: string; // px or %
	onlyDesktop?: boolean; // true or false
}

// Crear componente HOC para extender el panel de Inspector
const newCoreBlock = createHigherOrderComponent(
	(BlockEdit) =>
		(props) => {

		if (props.name !== "core/group") {
			return <BlockEdit {...props} />;
		}

		// init variables for the controls. tagName is a Gutenberg native attr, set in Advanded tab.
		const { attributes, setAttributes } = props;
		const { visualTransitionName, tagName = 'div', typePattern = '%' } = attributes;

		// Check if tagName is different from 'div'
		const isTagNameDiv = tagName === 'div';

		// internal state for checkbox
		const [checkBoxOn, setCheckBoxOn] = useState(
			visualTransitionName !== "",
		);

		// COMPUTED:
		// Compute whether to show pattern width control based on if pattern contains 'y_size'
		const showPatternHeightControl = usePatternData(props.attributes.visualTransitionName)?.pattern?.includes('y_size') ?? false;
		const showPatternWidthControl = usePatternData(props.attributes.visualTransitionName)?.pattern?.includes('x_size') ?? false;

		// WATCH changes in YOffset and update native attribute style.margin.top.
		useSyncYOffsetWithMarginTop({ attributes, setAttributes });

		// WATCH and UPDATE:
		// now the extra classes in the editor depending on the attributes.
		// Only update className if it has changed to avoid unnecessary re-renders
		useEffect( () => {

			const patternName = props.attributes.visualTransitionName;
			if (checkBoxOn && patternName && patternName.length && isTagNameDiv) {
				getInlineCssSvg(patternName, props.clientId, props.attributes).then( (inlineCSSandSVG: string) => {
					/**
					 * adds <div id="" >
					 * 				<style>.selector-block{ clip - path: url(#idunico) ... </style>
					 * 				<svg> <clipPath id=#idunico  ... </svg>
					 */
					appendInlineCss(props.clientId, inlineCSSandSVG);
				});
			}else {
				// visual transition deactivated: we delete the styles, if any was applied.
				deleteInlineCss(props.clientId);
			}


		}, [props.attributes, checkBoxOn, visualTransitionName, props.clientId, isTagNameDiv]); // WATCH changes in attrs.

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls __experimentalGroup="styles">
					<PanelBody title="Visual Transition" initialOpen={true}>
						{!isTagNameDiv && (
							<Notice status="warning" isDismissible={false}>
								{__("Visual transitions are only available when the Group block uses a 'div' element. Current tag: ", "coco-visualtransition")}
								<code>{tagName}</code>
							</Notice>
						)}

						<CheckboxControl
							label={__("Enable Visual Transition", "coco-visualtransition")}
							checked={checkBoxOn}
							disabled={!isTagNameDiv}
							onChange={(value: boolean) => setCheckBoxOn(value)}
						/>

						{checkBoxOn && isTagNameDiv && (
							<>
								<SelectControl
									label={__("Select Transition Effect", "coco-visualtransition")}
									value={visualTransitionName}
									options={ [ { 'label': '---', value: '' },  ...patterns.map( ( pattern ) => ( { 'label': pattern.label, value: pattern.value } ))]}
									onChange={(value: string) =>
										setAttributes({ visualTransitionName: value })
									}
								/>

								{/* Radio control for typePattern */}
								<div style={{ marginBottom: '16px' }}>
									<label style={{ fontWeight: 'bold', display: 'block', marginBottom: 4 }}>{__("Pattern Height Unit", "coco-visualtransition")}</label>
									<label>
										<input
											type="radio"
											checked={typePattern === 'px'}
											value="px"
											onChange={() => setAttributes({
												typePattern: 'px',
												patternHeight: 35
											})}
										/>
										{' px (prepend cropped edge)'}
									</label>
									<br />
									<label style={{ marginLeft: 0 }}>
										<input
											type="radio"
											checked={typePattern === '%'}
											value="%"
											onChange={() => setAttributes({
												typePattern: '%',
												patternHeight: 0.2
											})}
										/>
										{' % (clip container, experimental) '}
									</label>
								</div>

								{ showPatternHeightControl &&  <RangeControl
									label={__(`Pattern Height (${typePattern})`, "coco-visualtransition")}
									value={attributes.patternHeight || 0.1}
									onChange={(value) => setAttributes({ patternHeight: value })}
									min={typePattern === '%' ? 0.0 : 0}
									max={typePattern === '%' ? 1.0 : 200}
									step={typePattern === '%' ? 0.01 : 1}
									help={__(`Adjust the height of the transition pattern (${typePattern})`, "coco-visualtransition")}
								/>
								}

								{ showPatternWidthControl &&  <RangeControl
										label={__("Pattern Width %", "coco-visualtransition")}
										value={attributes.patternWidth || 0.2}
										onChange={(value) => setAttributes({ patternWidth: value })}
										min={0.0}
										max={1.0}
										step={0.01}
										help={__("Adjust the width of the transition pattern", "coco-visualtransition")}
								/>
								}

								<RangeControl
										label={__("Negative offset Y (px)", "coco-visualtransition")}
										value={attributes.YOffset}
										onChange={(value) => setAttributes({ YOffset: value })}
										min={-150}
										max={0}
										step={1}
										help={  __("Overlaps the group with the precendent group by translating it on the Y axis. This control overwrites the margin top. It allows you to assign negative value to it. Note that the margin top will change the value to fit this one in pixels", "coco-visualtransition") }
								/>
								<CheckboxControl
									label={__("Only Desktop", "coco-visualtransition")}
									checked={attributes.onlyDesktop || false}
									onChange={(value: boolean) => setAttributes({ onlyDesktop: value })}
									help={__("If checked, the visual transition will only apply on desktop devices.", "coco-visualtransition")}
								/>
							</>
						)}
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	},
	"cocoCustomCoreBlock",
);

addFilter("editor.BlockEdit", "coco/extend-group-inspector", newCoreBlock);



// Helper: Custom hook to get pattern data based on selected pattern name
const usePatternData = (visualTransitionName: string) => {
	/** returns
	 * {
    "label": "Triangles",
    "value": "triangles",
    "pattern": "{x_size} {y_size}, {2*x_size} 0",
    "patternRepeat": "repeat-x"
  }
	 */
	return React.useMemo(() => {
		return patterns.find(pattern => pattern.value === visualTransitionName);
	}, [visualTransitionName]);
};


/**
 * Custom hook.
 * Updates the margin top of the block when YOffset is changed.
 *
 */
export function useSyncYOffsetWithMarginTop({ attributes, setAttributes }: { attributes: CustomAttributes; setAttributes: (attributes: Partial<CustomAttributes>) => void }) {
	const { YOffset, style = {} }: { YOffset?: number; style?: { spacing?: { margin?: { top?: string } } } } = attributes;
	const previousYOffset = useRef<number | undefined>(YOffset);
	const previousMarginTop = useRef<string | undefined>(style?.spacing?.margin?.top);

	useEffect(() => {
		const newAttributes: { YOffset?: number; style?: object } = {};
		const mt = style?.spacing?.margin?.top;
		if (mt && mt !== previousMarginTop.current) {
			newAttributes.YOffset = 0;
		}
		if (YOffset !== previousYOffset.current) {

			// update the values that we use to know if there is a modification
			previousYOffset.current = YOffset;
			previousMarginTop.current = YOffset !== 0 ? `${YOffset}px` : undefined;

			// Update the style
			const newStyle = {
				...style,
				spacing: {
					...(style?.spacing || {}),
					margin: {
						...(style?.spacing?.margin || {}),
						top: previousMarginTop.current, // <--- this is the update of the margin top with the YOffset px value
					},
				},
			};
			newAttributes.style = newStyle;

		}

		// Update the attributes
		setAttributes(newAttributes);

	}, [YOffset, setAttributes, style]);
}
