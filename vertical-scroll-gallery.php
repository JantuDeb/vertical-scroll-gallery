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
 * Register the block variation script.
 */
function vsg_register_block_variation_script()
{
    wp_register_script(
        'vsg-block-variation-script',
        plugin_dir_url(__FILE__) . 'assets/js/variation.js',
        array('wp-blocks', 'wp-dom-ready', 'wp-edit-post'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/variation.js'),
        true
    );
}
add_action('init', 'vsg_register_block_variation_script');

/**
 * Enqueue the block variation script in the editor.
 */
function vsg_enqueue_editor_assets()
{
    wp_enqueue_script('vsg-block-variation-script');
}
add_action('enqueue_block_editor_assets', 'vsg_enqueue_editor_assets');

/**
 * Enqueue frontend styles conditionally.
 */

function vsg_register_styles()
{
    wp_register_style(
        'vsg-frontend-style',
        plugin_dir_url(__FILE__) . 'assets/css/vertical-scroll-gallery.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/vertical-scroll-gallery.css')
    );
}

add_action('init', 'vsg_register_styles');


/**
 * Loading the css irrespective of blocks since it's very small
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('vsg-frontend-style');
});




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
    // Only target the vertical scroll variation of the core/gallery block
    if (
        $block['blockName'] === 'core/gallery' &&
        isset($block['attrs']['className']) &&
        strpos($block['attrs']['className'], 'is-style-vertical-scroll-gallery') !== false
    ) {
        return '<div class="vsg-list-view vsg-list-view-padding scroll-container">
                    <div class="vsg-list-view-content mini-scroll-bar">'
            . $block_content .
            '</div>
            </div>';
    }

    return $block_content;
}


// The filter 'render_block_core/gallery' passes the PRE-RENDERED HTML content.
// To replace it, we simply return our new HTML.
add_filter('render_block_core/gallery', 'vsg_render_gallery_block_content', 10, 2);
