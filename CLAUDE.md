# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin that extends the core WordPress Gallery block (`core/gallery`) by adding a "Vertical Scroll Image List" variation. The plugin creates a custom scrollable container for displaying images in a compact, vertically scrollable format.

## Development Commands

- `npm run build` - Build the plugin for production using @wordpress/scripts
- `npm run start` - Start development mode with watch/hot reload using @wordpress/scripts

## Architecture

### Core Components

**Main Plugin File**: `vertical-scroll-gallery.php`
- Registers block assets and handles frontend rendering
- Contains the main render callback `vsg_render_gallery_block_content()` that filters the `core/gallery` block output
- Conditionally applies vertical scroll styling based on block attributes or admin settings
- Includes admin settings functionality

**Block Editor Integration**: `src/index.js`
- Registers a block variation for `core/gallery` called "Vertical Scroll Image List"
- Extends gallery block attributes with `displayMode` (scroll/individual/default)
- Adds custom inspector controls in the block editor sidebar
- Uses WordPress hooks and filters to modify the gallery block behavior

**Frontend Styling**: `src/style.scss`
- Provides responsive CSS for the scrollable container (`.vsg-list-view`)
- Includes custom scrollbar styling and aspect-ratio-based responsive design
- Handles different viewport breakpoints (mobile, tablet, desktop)

**Admin Settings**: `admin/admin-settings.php`
- Creates WordPress admin options page under Settings
- Allows global override of default gallery behavior
- Provides checkbox to apply vertical scroll to all gallery blocks by default

### Build Process

The plugin uses `@wordpress/scripts` for building:
- Source files in `src/` are compiled to `build/`
- CSS is compiled from SCSS and includes RTL support
- Asset dependencies are automatically managed via `build/index.asset.php`

### Block Variation System

The plugin uses WordPress block variations rather than creating a new block:
- Safer approach that leverages core gallery functionality
- Maintains compatibility with WordPress updates
- Uses `className: "is-style-vertical-scroll-gallery"` to identify the variation
- Filter `render_block_core/gallery` intercepts and modifies gallery output

### Display Modes

Three display modes are supported via the `displayMode` attribute:
- `scroll`: Wraps content in scrollable container
- `individual`: Returns gallery content without scroll wrapper
- `default`: Uses WordPress default rendering

### Frontend Rendering Logic

1. Check if block is `core/gallery`
2. Check admin setting `override_default_gallery` OR presence of `is-style-vertical-scroll-gallery` class
3. If conditions met, reconstruct gallery HTML from inner blocks
4. Apply appropriate wrapper based on `displayMode` attribute
5. Return modified HTML or fallback to default content

## Plugin Structure

```
├── vertical-scroll-gallery.php  # Main plugin file
├── src/
│   ├── index.js                # Block editor JavaScript
│   └── style.scss              # Frontend styles
├── admin/
│   └── admin-settings.php      # WordPress admin page
├── build/                      # Compiled assets (generated)
├── package.json                # NPM configuration
└── readme.txt                  # WordPress plugin readme
```

## Development Notes

- The plugin modifies core WordPress gallery blocks, not custom blocks
- Uses WordPress hooks system extensively (`add_filter`, `add_action`)
- Responsive design uses padding-bottom percentage technique for aspect ratios
- Custom scrollbar styling is WebKit-specific
- Admin settings are stored in WordPress options table as `vsg_settings`
- Text domain: `vertical-scroll-gallery` for internationalization