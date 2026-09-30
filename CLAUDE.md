# Project guidance

This WordPress plugin extends core/gallery through a block variation. It adds
scroll and individual vertical layouts while preserving WordPress image rendering.

## Commands

- npm ci: install the locked build dependencies.
- npm run build: compile editor JS and frontend SCSS into build/.
- npm run start: watch source changes.
- WP_CORE_DIR=/path/to/WordPress npm test: run PHP regression tests with the real
  WordPress HTML parser and isolated option/hook stubs; no database required.
- npm run package: build and verify a release ZIP in dist/.

## Architecture

- vertical-scroll-gallery.php registers attributes/assets and modifies only the
  existing gallery wrapper with WP_HTML_Tag_Processor in render_block_core/gallery.
- src/index.js adds editor controls, an editor-only vertical preview, and the
  Vertical Scroll Image List variation. Never change core Gallery saved markup.
- src/style.scss contains scoped vertical/scroll styles, responsive CSS height,
  accessible focus and print overrides. Frontend JavaScript is unnecessary.
- admin/admin-settings.php exposes global defaults in option vsg_settings.
- scripts/package.py verifies that runtime PHP, admin/ and build/ are included.

## Display preferences

The displayMode attribute supports inherit (default), default (explicit WordPress
layout), scroll, and individual. Explicit preferences win over global settings.
An inherited legacy is-style-vertical-scroll-gallery class selects scroll mode.
Unmarked galleries inherit the global override if enabled, otherwise core layout.
Using inherit as the attribute default ensures an explicit default value survives
WordPress serialization. Match variation classes as whole class names.

## Rendering and assets

Let core and other plugins render every image once. Never rebuild attachment HTML,
strip gallery anchors/classes/alignment/styles, or use pre_render_block to bypass
core image/lightbox behavior. Preserve legacy gallery and externally hosted images.
Use wp_enqueue_block_style for frontend/editor CSS. The theme controls on-demand
loading. Missing build assets must show an admin notice instead of a fatal error.

## Safe testing and releases

Test markup preservation, explicit grid override, option changes, legacy galleries,
external images, metadata, captions, links, anchors, alignment and accessible names.
Never modify or delete existing posts, pages, attachments or FileBird folders just
for testing. Temporarily changed global layout settings must be restored afterward.
Package only runtime files, retaining a single vertical-scroll-gallery/ root folder.
Do not deploy GitHub source ZIPs without build assets or omit admin settings.
