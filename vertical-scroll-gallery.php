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

    // Enqueue the editor script
    add_action('enqueue_block_editor_assets', function () {
        wp_enqueue_script('vsg-editor-script');
    });

    // Register frontend style. We enqueue it on demand from the render path
    // so it only loads on pages that actually use the vertical-scroll variation.
    wp_register_style(
        'vsg-frontend-style',
        plugin_dir_url(__FILE__) . 'build/style-index.css',
        array(),
        $asset_file['version']
    );
}
add_action('init', 'vsg_register_block_assets');

/**
 * Register the `displayMode` attribute on core/gallery server-side so the
 * selected value persists across save/reload in the block editor.
 */
function vsg_register_gallery_attributes($args, $block_type)
{
    if ($block_type !== 'core/gallery') {
        return $args;
    }

    if (!isset($args['attributes']) || !is_array($args['attributes'])) {
        $args['attributes'] = array();
    }

    $args['attributes']['displayMode'] = array(
        'type'    => 'string',
        'default' => 'default',
    );

    return $args;
}
add_filter('register_block_type_args', 'vsg_register_gallery_attributes', 10, 2);




/**
 * Short-circuit core/gallery rendering for the vertical-scroll variation (or
 * when the global override is enabled). By returning non-null from
 * `pre_render_block`, WordPress skips core's gallery render callback entirely,
 * so inner images are rendered exactly once instead of twice.
 *
 * @param string|null $pre_render  Current pre-render value (null means proceed).
 * @param array       $block       Parsed block (innerBlocks already populated).
 * @return string|null             HTML to use, or null to let core render normally.
 */
function vsg_pre_render_gallery_block($pre_render, $block)
{
    if ($pre_render !== null || ($block['blockName'] ?? '') !== 'core/gallery') {
        return $pre_render;
    }

    static $options_cache = null;
    if ($options_cache === null) {
        $options_cache = get_option('vsg_settings', []);
    }

    $override_default      = !empty($options_cache['override_default_gallery']);
    $override_display_mode = $options_cache['override_display_mode'] ?? 'scroll';

    $display_mode = $block['attrs']['displayMode'] ?? ($override_default ? $override_display_mode : 'default');

    if ($display_mode === 'default') {
        return $pre_render;
    }

    if (empty($block['innerBlocks'])) {
        return $pre_render;
    }

    wp_enqueue_style('vsg-frontend-style');

    $inner_html = '';
    foreach ($block['innerBlocks'] as $inner_block) {
        $inner_html .= render_block($inner_block);
    }

    if ($display_mode === 'scroll') {
        return '<div class="vsg-list-view vsg-list-view-padding scroll-container">'
             . '<div class="vsg-list-view-content mini-scroll-bar">'
             . $inner_html
             . '</div></div>';
    }

    return $inner_html;
}
add_filter('pre_render_block', 'vsg_pre_render_gallery_block', 10, 2);

// Include admin settings
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/admin-settings.php';
}
