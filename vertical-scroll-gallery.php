<?php

/**
 * Plugin Name:       Vertical Scroll Gallery Variation
 * Plugin URI:        https://thestudypath.com/vertical-scroll-gallery
 * Description:       Adds a "Vertical Scroll Image List" variation to the core/gallery block with a vertically scrollable layout.
 * Version:           1.0.2
 * Author:            Jantu
 * Author URI:        https://thestudypath.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       vertical-scroll-gallery
 * Domain Path:       /languages
 */


if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register the block assets.
 */
function vsg_register_block_assets()
{
    $asset_file = include(plugin_dir_path(__FILE__) . 'build/index.asset.php');

    // Register the block editor script
    wp_register_script(
        'vsg-editor-script',
        plugin_dir_url(__FILE__) . 'build/index.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    // Register the frontend style
    wp_register_style(
        'vsg-frontend-style',
        plugin_dir_url(__FILE__) . 'build/style-index.css',
        array(),
        $asset_file['version']
    );

    // Enqueue the editor script
    add_action('enqueue_block_editor_assets', function () {
        wp_enqueue_script('vsg-editor-script');
    });

    // Enqueue the frontend style
    add_action('wp_enqueue_scripts', function () {
        wp_enqueue_style('vsg-frontend-style');
    });
}
add_action('init', 'vsg_register_block_assets');




/**
 * Custom render callback for the core/gallery block.
 * This function modifies the content *inside* the WordPress-generated gallery wrapper.
 *
 * @param string $block_content The default block content (already rendered HTML by WordPress).
 * @param array  $block         The full block object, including attributes and inner blocks.
 * @return string               The modified or default block content.
 */

function vsg_render_gallery_block_content($block_content, $block)
{
    if ($block['blockName'] !== 'core/gallery') {
        return $block_content;
    }

    $options = get_option('vsg_settings');
    $override_default = $options['override_default_gallery'] ?? 0;
    $is_vsg_variation = isset($block['attrs']['className']) && strpos($block['attrs']['className'], 'is-style-vertical-scroll-gallery') !== false;

    // Determine if we should apply the vertical scroll effect
    $apply_effect = $override_default || $is_vsg_variation;

    if (!$apply_effect) {
        return $block_content;
    }

    // Get display mode. Default to 'scroll' if the override is on but the attribute is not set.
    $display_mode = $block['attrs']['displayMode'] ?? 'scroll';

    // Reconstruct the gallery from inner blocks for consistency
    $inner_html = '';
    if (!empty($block['innerBlocks'])) {
        foreach ($block['innerBlocks'] as $inner_block) {
            $inner_html .= render_block($inner_block);
        }
    } else {
        // Fallback for galleries without inner blocks (older WordPress versions)
        $inner_html = $block_content;
    }


    if ($display_mode === 'scroll') {
        return '<div class="vsg-list-view vsg-list-view-padding scroll-container">
                    <div class="vsg-list-view-content mini-scroll-bar">'
            . $inner_html .
            '</div>
                </div>';
    } else {
        // 'individual' mode - return the reconstructed content without the scroll wrapper
        return $inner_html;
    }
}


// The filter 'render_block_core/gallery' passes the PRE-RENDERED HTML content.
// To replace it, we simply return our new HTML.
add_filter('render_block_core/gallery', 'vsg_render_gallery_block_content', 10, 2);

// Include admin settings
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/admin-settings.php';
}
