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
    // Enqueue form block script and style for the editor
    wp_enqueue_script('vsg-form-block-editor-script');
    wp_enqueue_style('vsg-form-block-style');
}
add_action('enqueue_block_editor_assets', 'vsg_enqueue_editor_assets');

/**
 * Register styles and scripts for both gallery variation and form block.
 */
function vsg_register_assets()
{
    // Gallery variation styles
    wp_register_style(
        'vsg-frontend-style',
        plugin_dir_url(__FILE__) . 'assets/css/vertical-scroll-gallery.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/vertical-scroll-gallery.css')
    );

    // Form block script
    wp_register_script(
        'vsg-form-block-editor-script',
        plugin_dir_url(__FILE__) . 'assets/js/form-block.js',
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'), // Added more specific dependencies
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/form-block.js'),
        true
    );

    // Form block styles (for editor and frontend)
    wp_register_style(
        'vsg-form-block-style',
        plugin_dir_url(__FILE__) . 'assets/css/form-block.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/form-block.css')
    );

    // Register the form block type
    register_block_type('vsg/contact-form', array(
        'editor_script' => 'vsg-form-block-editor-script',
        'style'         => 'vsg-form-block-style', // Used for both editor and frontend
        // Note: render_callback is not needed if save function returns static HTML and JS handles submission
    ));

    // Register frontend script for the form block
    wp_register_script(
        'vsg-form-frontend-script',
        plugin_dir_url(__FILE__) . 'assets/js/form-frontend.js',
        array('wp-element'), // Or empty if no WP core JS needed directly, but good to have for potential future use
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/form-frontend.js'),
        true
    );
}
add_action('init', 'vsg_register_assets');


/**
 * Enqueue frontend styles and scripts.
 */
add_action('wp_enqueue_scripts', function () {
    global $post; // Ensure $post is available

    wp_enqueue_style('vsg-frontend-style'); // For the gallery

    // Check if the current post/page content has the vsg/contact-form block
    if (is_a($post, 'WP_Post') && has_block('vsg/contact-form', $post->post_content)) {
        wp_enqueue_style('vsg-form-block-style'); // Enqueue form styles
        wp_enqueue_script('vsg-form-frontend-script'); // Enqueue form frontend script

        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'vsg-form-frontend-script',
            'vsgFormData', // Object name in JavaScript
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('vsg_form_submit_nonce'), // Action name for nonce
            )
        );
    }
});


/**
 * Handle Contact Form AJAX Submission.
 */
function vsg_handle_form_submission() {
    // 1. Nonce Verification
    check_ajax_referer('vsg_form_submit_nonce', '_ajax_nonce'); // Second param is the name of the nonce field in JS

    $errors = array();
    $sanitized_data = array();

    // 2. Data Retrieval & Sanitization
    $name = isset($_POST['vsg_name']) ? sanitize_text_field(wp_unslash($_POST['vsg_name'])) : '';
    $email = isset($_POST['vsg_email']) ? sanitize_email(wp_unslash($_POST['vsg_email'])) : '';
    $comment = isset($_POST['vsg_comment']) ? sanitize_textarea_field(wp_unslash($_POST['vsg_comment'])) : '';

    // 3. Validation
    if (empty($name)) {
        $errors['vsg_name'] = 'Name is required.';
    }
    if (empty($email)) {
        $errors['vsg_email'] = 'Email is required.';
    } elseif (!is_email($email)) {
        $errors['vsg_email'] = 'Invalid email address.';
    }
    if (empty($comment)) {
        $errors['vsg_comment'] = 'Comment is required.';
    }

    if (!empty($errors)) {
        wp_send_json_error(array('errors' => $errors));
    } else {
        // Data is valid and sanitized. Variables $name, $email, $comment are already sanitized.

        // Retrieve Email Settings
        $to_email = get_option('vsg_form_to_email');
        $from_email = get_option('vsg_form_from_email', get_option('admin_email')); // Fallback to admin email
        $subject_template = get_option('vsg_form_email_subject', 'New Form Submission from [vsg_form_name]'); // Default subject
        $handler_option = get_option('vsg_form_handler_option', 'email'); // Default to 'email'

        // Replace placeholders in subject
        $subject = str_replace('[vsg_form_name]', $name, $subject_template);
        // Potentially add more placeholders like [vsg_form_email] if needed

        $sanitized_data = array( // Keep this for logging if not emailing or for 'both'
            'name'    => $name,
            'email'   => $email,
            'comment' => $comment,
        );

        if ($handler_option === 'email' || $handler_option === 'both') {
            if (empty($to_email)) {
                error_log('VSG Contact Form: Recipient email (To Email) is not configured in VSG Settings.');
                wp_send_json_error(array(
                    'message_type' => 'email_error',
                    'message' => 'Form submission successful, but the site administrator needs to configure the recipient email in VSG Settings for notifications to be sent.'
                ));
                wp_die();
            }

            // Construct Email Body
            $body = "You have a new form submission:\n\n";
            $body .= "Name: " . $name . "\n";
            $body .= "Email: " . $email . "\n";
            $body .= "Comment:\n" . $comment . "\n\n";
            $body .= "--\nThis email was sent from your website (" . get_bloginfo('name') . " - " . home_url() . ")";

            // Prepare Headers
            $headers = array();
            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $from_email . '>';
            $headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
            // $headers[] = 'Content-Type: text/plain; charset=UTF-8'; // Optional: ensure plain text

            // Send Email
            $mail_sent = wp_mail($to_email, $subject, $body, $headers);

            if ($mail_sent) {
                if ($handler_option === 'both') {
                    // TODO: Implement database saving logic here in a future step
                    error_log('VSG Contact Form (Handler: Both - Email Sent, DB TODO): ' . print_r($sanitized_data, true));
                    wp_send_json_success(array('message' => 'Form submitted successfully! Email sent and data logged (DB part pending).'));
                } else {
                    error_log('VSG Contact Form (Handler: Email - Sent): ' . print_r($sanitized_data, true));
                    wp_send_json_success(array('message' => 'Form submitted successfully and email sent!'));
                }
            } else {
                error_log('VSG Contact Form (Handler: ' . $handler_option . ' - Email Send Failed): ' . print_r($sanitized_data, true));
                wp_send_json_error(array(
                    'message_type' => 'email_error', // Custom type for frontend to potentially identify
                    'message' => 'Form data is valid, but the email could not be sent. Please check server or plugin email settings, or contact the site administrator.'
                ));
            }
        } elseif ($handler_option === 'db') {
            // TODO: Implement database saving logic here in a future step
            error_log('VSG Contact Form (Handler: DB - TODO): ' . print_r($sanitized_data, true));
            wp_send_json_success(array('message' => 'Form submitted successfully! Data logged (DB part pending). Handler: db.'));
        } else {
            // Fallback for any other unexpected handler_option value or if it's not set (though default is 'email')
            error_log('VSG Contact Form (Handler: Unknown - ' . $handler_option . '): ' . print_r($sanitized_data, true));
            wp_send_json_success(array('message' => 'Form submitted successfully! Data logged (Handler: ' . esc_html($handler_option) . ').'));
        }
    }

    // Ensure to die() is called explicitly in AJAX handlers
    wp_die();
}
add_action('wp_ajax_vsg_submit_form', 'vsg_handle_form_submission');
add_action('wp_ajax_nopriv_vsg_submit_form', 'vsg_handle_form_submission');

// Settings Page Implementation

/**
 * Add options page for VSG Settings.
 */
function vsg_add_settings_page() {
    add_options_page(
        'VSG Settings',                     // Page title
        'VSG Contact Form',                 // Menu title
        'manage_options',                   // Capability required
        'vsg-settings',                     // Menu slug
        'vsg_render_settings_page_html'     // Function to display the page
    );
}
add_action('admin_menu', 'vsg_add_settings_page');

/**
 * Render the HTML for the settings page.
 */
function vsg_render_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('vsg_settings_group'); // Output nonce, action, and option_page fields for the group.
            do_settings_sections('vsg-settings');    // Print out all settings sections for the page.
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

/**
 * Register plugin settings, sections, and fields.
 */
function vsg_register_plugin_settings() {
    // Register the main settings group
    register_setting('vsg_settings_group', 'vsg_form_to_email');
    register_setting('vsg_settings_group', 'vsg_form_from_email');
    register_setting('vsg_settings_group', 'vsg_form_email_subject');
    register_setting(
        'vsg_settings_group',
        'vsg_form_handler_option',
        array('default' => 'email') // Default value
    );

    // Add a section for Contact Form settings
    add_settings_section(
        'vsg_form_settings_section',             // ID
        'Contact Form Submission Settings',      // Title
        null, //'vsg_form_settings_section_cb',        // Callback for section description (optional)
        'vsg-settings'                           // Page slug where this section will be displayed
    );

    // Add fields for the section
    add_settings_field(
        'vsg_form_to_email',
        'Recipient Email (To)',
        'vsg_form_to_email_callback',
        'vsg-settings',
        'vsg_form_settings_section'
    );
    add_settings_field(
        'vsg_form_from_email',
        'Sender Email (From)',
        'vsg_form_from_email_callback',
        'vsg-settings',
        'vsg_form_settings_section'
    );
    add_settings_field(
        'vsg_form_email_subject',
        'Email Subject',
        'vsg_form_email_subject_callback',
        'vsg-settings',
        'vsg_form_settings_section'
    );
    add_settings_field(
        'vsg_form_handler_option',
        'Form Submission Handling',
        'vsg_form_handler_option_callback',
        'vsg-settings',
        'vsg_form_settings_section'
    );
}
add_action('admin_init', 'vsg_register_plugin_settings');

// Field rendering callback functions
function vsg_form_to_email_callback() {
    $option = get_option('vsg_form_to_email');
    echo '<input type="email" id="vsg_form_to_email" name="vsg_form_to_email" value="' . esc_attr($option) . '" class="regular-text" />';
    echo '<p class="description">The email address where submitted forms will be sent.</p>';
}

function vsg_form_from_email_callback() {
    $option = get_option('vsg_form_from_email');
    $admin_email = get_option('admin_email');
    $value = $option ? $option : $admin_email; // Default to admin email if not set
    echo '<input type="email" id="vsg_form_from_email" name="vsg_form_from_email" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">The "From" email address for notifications. Defaults to site admin email.</p>';
}

function vsg_form_email_subject_callback() {
    $option = get_option('vsg_form_email_subject');
    $default_subject = 'New Form Submission from [vsg_form_name]';
    $value = $option ? $option : $default_subject;
    echo '<input type="text" id="vsg_form_email_subject" name="vsg_form_email_subject" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Subject line for email notifications. You can use placeholders like [vsg_form_name].</p>';
}

function vsg_form_handler_option_callback() {
    $option = get_option('vsg_form_handler_option', 'email'); // Default to 'email'
    ?>
    <select id="vsg_form_handler_option" name="vsg_form_handler_option">
        <option value="email" <?php selected($option, 'email'); ?>>Send Email</option>
        <option value="db" <?php selected($option, 'db'); ?>>Save to Database (Not Implemented)</option>
        <option value="both" <?php selected($option, 'both'); ?>>Send Email and Save to Database (DB Not Implemented)</option>
    </select>
    <p class="description">Choose how form submissions are handled.</p>
    <?php
}


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
        // Retrieve height attributes
        $desktop_height = isset($block['attrs']['desktopHeight']) ? esc_attr($block['attrs']['desktopHeight']) : '100vh';
        $tablet_height = isset($block['attrs']['tabletHeight']) ? esc_attr($block['attrs']['tabletHeight']) : '80vh';
        $mobile_height = isset($block['attrs']['mobileHeight']) ? esc_attr($block['attrs']['mobileHeight']) : '60vh';

        // Generate unique ID for the style block if multiple galleries are on the page
        $unique_id = 'vsg-' . md5($block_content); // Simple way to get a somewhat unique ID

        $style_tag = sprintf(
            '<style>
                #%1$s.vsg-list-view { height: %2$s; }
                @media (max-width: 768px) {
                    #%1$s.vsg-list-view { height: %3$s; }
                }
                @media (max-width: 480px) {
                    #%1$s.vsg-list-view { height: %4$s; }
                }
            </style>',
            esc_attr($unique_id),
            esc_attr($desktop_height),
            esc_attr($tablet_height),
            esc_attr($mobile_height)
        );

        return $style_tag .
            '<div id="' . esc_attr($unique_id) . '" class="vsg-list-view scroll-container">
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
