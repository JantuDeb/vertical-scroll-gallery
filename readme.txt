=== Vertical Scroll Gallery Variation ===
Contributors:      Jantu
Donate link:       https://thestudypath.com
Tags:              gallery, image, scroll, vertical, block, variation
Requires at least: 5.8
Tested up to:      6.8
Stable tag:        1.0.2
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Adds a "Vertical Scroll Image List" variation to the core WordPress Gallery block, allowing images to be displayed in a custom, vertically scrollable container.

== Description ==

This plugin extends the functionality of the native WordPress Gallery block (`core/gallery`) by adding a new style variation. When you select the "Vertical Scroll Image List" style for a gallery, the images will be rendered on the frontend in a dedicated scrollable container.

Features:
*   Registers a new block style variation for the `core/gallery` block.
*   Custom frontend rendering for the selected variation.
*   Uses semantic `<figure>` and `<img>` tags for each image.
*   Supports image `alt` text, `srcset`, `sizes`, `width`, `height`, `loading="lazy"`, `decoding="async"`, and `fetchpriority="high"` (for the first image).
*   Displays image captions if they are set in the Media Library and the gallery is configured to show captions.
*   Includes CSS for the scrollable container and image presentation.
*   Does not affect default gallery block rendering if the custom variation is not selected.

This is useful for displaying long lists of images, such as solution pages or step-by-step guides, in a compact, scrollable view.

== Installation ==

1.  Upload the `vertical-scroll-gallery` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  When editing a post or page, add a Gallery block.
4.  In the block inspector (sidebar), under "Styles", select the "Vertical Scroll Image List" variation.
5.  Add your images to the gallery as usual.
6.  The frontend will display the gallery in the custom scrollable format.

== Frequently Asked Questions ==

= Does this affect the default gallery block? =
No, this plugin only changes the rendering for Gallery blocks where you have specifically selected the "Vertical Scroll Image List" style variation. All other galleries will render as usual.

= Does the editor show the scrollable view? =
The block editor will allow you to select the variation, but it may not show a live preview of the exact scrollable frontend output. The primary effect of this plugin is on the frontend rendering.

== Screenshots ==
<!-- 1. Admin area - Block selected with style variation panel visible. -->
<!-- 2. Frontend - Example of the gallery rendered in the scrollable container. -->

== Changelog ==

= 1.0.2 =
* Refined PHP render callback to ensure correct image attributes (width, height, data-id, fetchpriority).
* Improved conditional CSS loading.
* Ensured captions are handled based on gallery settings and media library.
* Matched output structure more closely to the provided "original structure" example.

= 1.0.1 =
* Ensured plugin only affects galleries with the specific variation selected.
* Retained srcset and sizes attributes for responsive images.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
Minor bug fixes and improvements to ensure compatibility and correct rendering.