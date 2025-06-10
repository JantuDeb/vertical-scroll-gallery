const { registerBlockVariation } = wp.blocks;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, ToggleControl } = wp.components;
const { Fragment } = wp.element;

wp.domReady(() => {
  // Register the variation
  if (registerBlockVariation) {
    registerBlockVariation("core/gallery", {
      name: "vertical-scroll-gallery",
      title: "Vertical Scroll Image List",
      icon: "format-gallery",
      description: "Displays gallery images in a vertical scroll layout.",
      attributes: {
        verticalScroll: true,
        linkTo: "none",
        columns: 1,
      },
      isActive: (attributes) => !!attributes.verticalScroll,
      scope: ["block", "inserter"],
    });
  }
});

/**
 * Add `verticalScroll` attribute to core/gallery block
 */
function addVerticalScrollAttribute(settings, name) {
  if (name !== "core/gallery") return settings;

  settings.attributes = {
    ...settings.attributes,
    verticalScroll: {
      type: "boolean",
      default: false,
    },
  };

  return settings;
}
addFilter(
  "blocks.registerBlockType",
  "vsg/add-gallery-vertical-scroll-attribute",
  addVerticalScrollAttribute
);

/**
 * Add inspector control (sidebar toggle)
 */
const withVerticalScrollToggle = createHigherOrderComponent((BlockEdit) => {
  return (props) => {
    if (props.name !== "core/gallery") {
      return <BlockEdit {...props} />;
    }

    const { attributes, setAttributes } = props;
    const { verticalScroll } = attributes;

    return (
      <Fragment>
        <BlockEdit {...props} />
        <InspectorControls>
          <PanelBody title="Gallery Layout Options">
            <ToggleControl
              label="Enable Vertical Scroll Layout"
              checked={!!verticalScroll}
              onChange={(value) => setAttributes({ verticalScroll: value })}
            />
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  };
}, "withVerticalScrollToggle");

addFilter(
  "editor.BlockEdit",
  "vsg/gallery-vertical-scroll-toggle",
  withVerticalScrollToggle
);
