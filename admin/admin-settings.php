<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Admin Settings Page
 */

// Add the options page
function vsg_add_options_page()
{
    add_options_page(
        __('Vertical Scroll Gallery Settings', 'vertical-scroll-gallery'),
        __('Vertical Scroll Gallery', 'vertical-scroll-gallery'),
        'manage_options',
        'vsg-settings',
        'vsg_render_options_page'
    );
}
add_action('admin_menu', 'vsg_add_options_page');

// Render the options page
function vsg_render_options_page()
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('vsg_settings_group');
            do_settings_sections('vsg-settings');
            submit_button(__('Save Settings', 'vertical-scroll-gallery'));
            ?>
        </form>
    </div>
    <?php
}

// Register settings, sections, and fields
function vsg_register_settings()
{
    register_setting(
        'vsg_settings_group',
        'vsg_settings',
        [
            'type' => 'array',
            'sanitize_callback' => 'vsg_sanitize_settings',
            'default' => [
                'override_default_gallery' => 0,
            ],
        ]
    );

    add_settings_section(
        'vsg_general_section',
        __('General Settings', 'vertical-scroll-gallery'),
        null,
        'vsg-settings'
    );

    add_settings_field(
        'vsg_override__gallery',
        __('Override Default Gallery', 'vertical-scroll-gallery'),
        'vsg_render_override_gallery_field',
        'vsg-settings',
        'vsg_general_section'
    );
}
add_action('admin_init', 'vsg_register_settings');

// Render the field
function vsg_render_override_gallery_field()
{
    $options = get_option('vsg_settings');
    $override = $options['override_default_gallery'] ?? 0;
    ?>
    <label>
        <input type="checkbox" name="vsg_settings[override_default_gallery]" value="1" <?php checked(1, $override); ?> />
        <?php _e('Apply vertical scroll effect to all core/gallery blocks by default.', 'vertical-scroll-gallery'); ?>
    </label>
    <?php
}

// Sanitize the settings
function vsg_sanitize_settings($input)
{
    $sanitized_input = [];
    $sanitized_input['override_default_gallery'] = isset($input['override_default_gallery']) ? 1 : 0;
    return $sanitized_input;
}
