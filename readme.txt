=== Vertical Scroll Gallery Variation ===
Contributors: Jantu
Tags: gallery, image, scroll, vertical, block, variation
Requires at least: 6.5
Requires PHP: 7.4
Tested up to: 7.1
Stable tag: 1.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Read image pages in a compact scroll area or as full-size vertical images, using the native WordPress Gallery block.

== Description ==

Three layouts: WordPress grid, scrollable pages, or full-size vertical images.
The plugin changes only the existing gallery wrapper. WordPress still renders
images, responsive sources, dimensions, lazy loading, links, lightbox behavior,
image captions and gallery captions. Gallery anchors, alignment, custom classes
and inline styles are preserved. Images are rendered once, including external
images without attachment IDs and legacy galleries without nested Image blocks.

No frontend JavaScript. The compiled stylesheet is approximately 2 KB uncompressed.
It uses WordPress's block stylesheet loader; whether it loads on demand or globally
is controlled by the active theme/WordPress configuration. Scroll height is reserved
in CSS, images keep their original proportions, and print output expands all pages.
The scroll area is keyboard focusable, named for screen readers, and has a visible
focus ring. Native scrolling lets readers use arrows, Page Down, mouse and touch.

FileBird continues to manage the Media Library. No image files are moved, and this
plugin does not create, edit or delete posts, pages or attachments. Deactivation
restores standard Gallery rendering; saved display preferences remain in content.

== Installation ==

Use the built release ZIP, not GitHub's source-code ZIP (which excludes build/).
WordPress: Plugins > Add Plugin > Upload Plugin, select the ZIP, install and activate.
When upgrading an existing copy, use "Replace current with uploaded"; settings remain.

== Using existing galleries ==

1. Open an existing post/page in the block editor.
2. Select the parent Gallery block using List View, rather than an individual image.
3. In the block sidebar, open "Gallery layout" and choose "Display mode":
   * Use site setting: follow Settings > Vertical Scroll Gallery. Existing variation
     markers continue to select scroll mode for backward compatibility.
   * Scrollable pages: one column of uncropped pages inside a scroll area.
   * Full-size vertical images: one column without an inner scroll area.
   * WordPress gallery: ordinary grid, even with the global override enabled.
4. Save/update the post after choosing the layout. No gallery replacement is needed.

To add a new gallery, insert "Vertical Scroll Image List" and choose images normally,
including through FileBird folders. This is a core Gallery variation, not a separate
block type. The editor previews the vertical image stack; scrolling is tested on the
published page. Columns and cropping apply only to the WordPress layout.

== Site-wide settings ==

Settings > Vertical Scroll Gallery:
Enable "Override Default Gallery" and choose Scroll or Individual to set the default
for existing galleries without explicit block preferences. Explicit block modes win.
Disable the override to keep ordinary galleries unchanged. A per-block "WordPress
gallery" selection is stored explicitly and survives editor save/reload.

After changing layouts or upgrading, clear your page cache (for example WP Rocket),
so visitors receive the new markup and CSS.

== Styling ==

Optional CSS variables on .vsg-gallery:
--vsg-height: scroll area height (default clamp(22rem, 75svh, 50rem));
--vsg-gap: space between image pages (default 1rem);
--vsg-border-color: scroll area border;
--vsg-focus-color: keyboard focus outline.
Example: .vsg-list-view { --vsg-height: 650px; }
Print styles remove the fixed height so all images can be printed.

== Development ==

npm ci
npm run build
npm run package

The verified ZIP is generated in dist/. It includes PHP, admin settings and build
assets, with a single vertical-scroll-gallery/ directory. Missing build assets show
an admin notice instead of causing a fatal error.

Regression tests use WordPress's real HTML Tag Processor with isolated option and
hook stubs, without a database. PHP CLI is required:
git clone --depth 1 --branch 6.5 https://github.com/WordPress/WordPress.git /tmp/vsg-wp
WP_CORE_DIR=/tmp/vsg-wp npm test

Tests cover unchanged default galleries, explicit grid override, settings changes,
legacy markup, external images, image metadata, captions, links, anchors/alignment,
accessible names and invalid settings. The release workflow uses the same packager.

== Changelog ==

= 1.0.5 =
* Preserve core gallery markup instead of rebuilding child images.
* Distinguish inherited settings from explicit WordPress layout preferences.
* Improve responsive height, uncropped images, captions, keyboard focus and print.
* Add editor preview, descriptive controls and backward-compatible variation support.
* Use the core block stylesheet loader; keep JavaScript editor-only.
* Guard missing build assets; include admin settings in verified release packages.

= 1.0.3 =
* Add global settings and editor controls using the npm build pipeline.

= 1.0.2 =
* Earlier image rendering and conditional stylesheet changes.

= 1.0.0 =
* Initial release.
