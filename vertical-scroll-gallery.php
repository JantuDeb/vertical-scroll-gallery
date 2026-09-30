<?php
/**
 * Plugin Name:       Vertical Scroll Gallery Variation
 * Plugin URI:        https://github.com/JantuDeb/vertical-scroll-gallery
 * Description:       Adds scrollable and full-size vertical layouts to the native Gallery block without rebuilding images.
 * Version:           1.0.5
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Jantu
 * Author URI:        https://thestudypath.com
 * License:           GPL-2.0-or-later
 * Text Domain:       vertical-scroll-gallery
 */
if (!defined('ABSPATH')) {
    exit;
}

function vsg_register_block_assets() {
    $asset_path = plugin_dir_path(__FILE__) . 'build/index.asset.php';
    if (file_exists($asset_path) && file_exists(plugin_dir_path(__FILE__) . 'build/index.js')) {
        $asset = require $asset_path;
        wp_register_script('vsg-editor-script', plugin_dir_url(__FILE__) . 'build/index.js', $asset['dependencies'], $asset['version'], true);
        add_action('enqueue_block_editor_assets', function () {
            wp_enqueue_script('vsg-editor-script');
            wp_add_inline_script('vsg-editor-script', 'window.vsgEditorSettings = ' . wp_json_encode(array('mode' => vsg_get_display_mode(array()))) . ';', 'before');
        });
    } else {
        add_action('admin_notices', function () {
            if (current_user_can('manage_options')) {
                echo '<div class="notice notice-error"><p>' . esc_html__('Vertical Scroll Gallery: build assets are missing. Install the release ZIP, or run npm ci and npm run build before packaging.', 'vertical-scroll-gallery') . '</p></div>';
            }
        });
    }
    $style_path = plugin_dir_path(__FILE__) . 'build/style-index.css';
    if (file_exists($style_path)) {
        // Core handles late rendering, editor styles, RTL and optional CSS inlining.
        wp_enqueue_block_style('core/gallery', array(
            'handle' => 'vsg-frontend-style',
            'src' => plugin_dir_url(__FILE__) . 'build/style-index.css',
            'path' => $style_path,
            'ver' => '1.0.5',
        ));
    }
}
add_action('init', 'vsg_register_block_assets');

function vsg_register_gallery_attributes($args, $block_type) {
    if ($block_type === 'core/gallery') {
        $args['attributes']['displayMode'] = array(
            'type' => 'string',
            'enum' => array('inherit', 'default', 'scroll', 'individual'),
            'default' => 'inherit',
        );
    }
    return $args;
}
add_filter('register_block_type_args', 'vsg_register_gallery_attributes', 10, 2);

/** Explicit WordPress mode must persist even when a global override is enabled. */
function vsg_get_display_mode($attributes) {
    $mode = $attributes['displayMode'] ?? 'inherit';
    if ($mode !== 'inherit') {
        return in_array($mode, array('default', 'scroll', 'individual'), true) ? $mode : 'default';
    }
    $classes = preg_split('/\s+/', trim($attributes['className'] ?? ''));
    if (in_array('is-style-vertical-scroll-gallery', $classes, true)) {
        return 'scroll';
    }
    // get_option is already cached by WordPress; do not cache stale values here.
    $options = get_option('vsg_settings', array());
    if (!is_array($options) || empty($options['override_default_gallery'])) {
        return 'default';
    }
    return in_array($options['override_display_mode'] ?? '', array('scroll', 'individual'), true)
        ? $options['override_display_mode'] : 'scroll';
}

/** Modify the existing wrapper only; core and other plugins render images once. */
function vsg_render_gallery_block_content($content, $block) {
    $mode = vsg_get_display_mode($block['attrs'] ?? array());
    if ($mode === 'default' || trim($content) === '') {
        return $content;
    }
    $html = new WP_HTML_Tag_Processor($content);
    if (!$html->next_tag(array('class_name' => 'wp-block-gallery'))) {
        return $content;
    }
    $html->add_class('vsg-gallery');
    $html->add_class($mode === 'scroll' ? 'vsg-list-view' : 'vsg-individual-view');
    if ($mode === 'scroll') {
        $html->set_attribute('tabindex', '0');
        $html->set_attribute('role', 'region');
        // Retain an author's accessible name if one was already supplied.
        if (!$html->get_attribute('aria-label') && !$html->get_attribute('aria-labelledby')) {
            $html->set_attribute('aria-label', __('Scrollable image gallery', 'vertical-scroll-gallery'));
        }
    }
    return $html->get_updated_html();
}
add_filter('render_block_core/gallery', 'vsg_render_gallery_block_content', 10, 2);

if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/admin-settings.php';
}
