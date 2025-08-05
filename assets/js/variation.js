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
        displayMode: "individual",
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
