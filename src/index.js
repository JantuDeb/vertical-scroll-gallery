import { registerBlockVariation } from "@wordpress/blocks";
import { addFilter } from "@wordpress/hooks";
import { createHigherOrderComponent } from "@wordpress/compose";
import { InspectorControls } from "@wordpress/block-editor";
import { PanelBody, SelectControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import "./style.scss";

// Extend core/gallery block attributes
function addAttributes(settings, name) {
  if (name === "core/gallery") {
    settings.attributes = {
      ...settings.attributes,
      displayMode: {
        type: "string",
        default: "default",
      },
    };
  }
  return settings;
}

addFilter(
  "blocks.registerBlockType",
  "vertical-scroll-gallery/add-attributes",
  addAttributes
);

const addInspectorControls = (BlockEdit) => {
  return (props) => {
    const { attributes, setAttributes, name } = props;
    const { className, displayMode } = attributes;

    // Only show controls for our specific gallery variation
    if (
      name === "core/gallery" /* &&
      className &&
      className.includes("is-style-vertical-scroll-gallery") */
    ) {
      return (
        <>
          <BlockEdit {...props} />
          <InspectorControls>
            <PanelBody
              title={__("Vertical Scroll Settings", "vertical-scroll-gallery")}
            >
              <SelectControl
                label={__("Display Mode", "vertical-scroll-gallery")}
                value={displayMode}
                options={[
                  {
                    label: __("Scroll", "vertical-scroll-gallery"),
                    value: "scroll",
                  },
                  {
                    label: __("Individual", "vertical-scroll-gallery"),
                    value: "individual",
                  },
                  {
                    label: __("Default", "vertical-scroll-gallery"),
                    value: "default",
                  },
                ]}
                onChange={(displayMode) => setAttributes({ displayMode })}
                help={__(
                  "Choose how to display the images in the gallery.",
                  "vertical-scroll-gallery"
                )}
              />
            </PanelBody>
          </InspectorControls>
        </>
      );
    }

    return <BlockEdit {...props} />;
  };
};

addFilter(
  "editor.BlockEdit",
  "vertical-scroll-gallery/with-inspector-controls",
  addInspectorControls
);

registerBlockVariation("core/gallery", {
  name: "vertical-scroll-gallery",
  title: "Vertical Scroll Image List",
  icon: "images-alt2",
  description:
    "Displays gallery images in a custom vertically scrollable list.",
  attributes: {
    className: "is-style-vertical-scroll-gallery",
    linkTo: "none",
    columns: 1,
    displayMode: "scroll",
  },
  scope: ["block", "inserter"],
  isActive: (blockAttributes) => {
    return (
      blockAttributes.className &&
      blockAttributes.className.includes("is-style-vertical-scroll-gallery")
    );
  },
});
