const { addFilter } = wp.hooks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;
const { createHigherOrderComponent } = wp.compose;

// Function to add attributes to the block variation
function addAttributes(settings, name) {
  if (name === "core/gallery" && settings.variations) {
    const variation = settings.variations.find(
      (v) => v.name === "vertical-scroll-gallery"
    );
    if (variation) {
      variation.attributes = {
        ...variation.attributes,
        desktopHeight: {
          type: "string",
          default: "100vh", // Default value
        },
        tabletHeight: {
          type: "string",
          default: "80vh", // Default value
        },
        mobileHeight: {
          type: "string",
          default: "60vh", // Default value
        },
      };
    }
  }
  return settings;
}

addFilter(
  "blocks.registerBlockType",
  "vsg/addAttributes",
  addAttributes
);

// Higher-order component to add inspector controls
const withInspectorControls = createHigherOrderComponent((BlockEdit) => {
  return (props) => {
    if (
      props.name === "core/gallery" &&
      props.attributes.className &&
      props.attributes.className.includes("is-style-vertical-scroll-gallery")
    ) {
      const { attributes, setAttributes } = props;
      const { desktopHeight, tabletHeight, mobileHeight } = attributes;

      return (
        <>
          <BlockEdit {...props} />
          <InspectorControls>
            <PanelBody title="Gallery Heights" initialOpen={true}>
              <TextControl
                label="Desktop Height"
                value={desktopHeight}
                onChange={(value) => setAttributes({ desktopHeight: value })}
                help="e.g., 100vh, 500px"
              />
              <TextControl
                label="Tablet Height"
                value={tabletHeight}
                onChange={(value) => setAttributes({ tabletHeight: value })}
                help="e.g., 80vh, 400px"
              />
              <TextControl
                label="Mobile Height"
                value={mobileHeight}
                onChange={(value) => setAttributes({ mobileHeight: value })}
                help="e.g., 60vh, 300px"
              />
            </PanelBody>
          </InspectorControls>
        </>
      );
    }
    return <BlockEdit {...props} />;
  };
}, "withInspectorControls");

addFilter(
  "editor.BlockEdit",
  "vsg/withInspectorControls",
  withInspectorControls
);

wp.domReady(() => {
  if (wp.blocks && wp.blocks.registerBlockVariation) {
    wp.blocks.registerBlockVariation("core/gallery", {
      name: "vertical-scroll-gallery",
      title: "Vertical Scroll Image List", // User-friendly title
      icon: "images-alt2", // WordPress Dashicon
      description:
        "Displays gallery images in a custom vertically scrollable list.",
      attributes: {
        // This className is crucial for our PHP render_callback to identify the variation
        className: "is-style-vertical-scroll-gallery",
        linkTO: "none",
        columns: 1,
        // Initial attributes for heights will be handled by addAttributes filter
      },
      scope: ["block", "inserter"], // Where this variation appears
      isActive: (blockAttributes) => {
        // Determines if this variation is currently active for the selected block
        return (
          blockAttributes.className &&
          blockAttributes.className.includes("is-style-vertical-scroll-gallery")
        );
      },
      //   isDefault: true, // Set to true if this should be the default style for new galleries
    });
  }
});
