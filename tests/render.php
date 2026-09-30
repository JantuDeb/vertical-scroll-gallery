<?php
// Test the production callback against WordPress's real HTML parser, without a DB.
$core = getenv('WP_CORE_DIR');
if (!$core || !is_file($core . '/wp-includes/html-api/class-wp-html-tag-processor.php')) {
    fwrite(STDERR, "Set WP_CORE_DIR to a WordPress source checkout (6.5+). See readme.txt.\n");
    exit(1);
}
define('ABSPATH', rtrim($core, '/') . '/');
foreach (array('attribute-token', 'span', 'text-replacement', 'tag-processor') as $name) {
    require ABSPATH . 'wp-includes/html-api/class-wp-html-' . $name . '.php';
}
function add_action(...$args) {}
function add_filter(...$args) {}
function is_admin() { return false; }
function get_option($name, $default = false) { return $GLOBALS['settings'] ?? $default; }
function __($text, $domain = '') { return $text; }
function esc_attr($value) { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
require dirname(__DIR__) . '/vertical-scroll-gallery.php';
require dirname(__DIR__) . '/admin/admin-settings.php';
$count = 0;
function check($condition, $message) {
    global $count;
    if (!$condition) throw new Exception($message);
    $count++;
}
$children = '<figure class="wp-block-image"><a href="/original.pdf"><img src="/external.png" alt="Page &amp; diagram" width="1200" height="1800" srcset="/small.png 600w, /external.png 1200w" sizes="100vw" loading="lazy" decoding="async" /></a><figcaption>Edited <strong>caption</strong></figcaption></figure><figcaption class="blocks-gallery-caption">Gallery caption</figcaption>';
$content = '<figure id="lesson-pages" class="wp-block-gallery alignfull custom-class has-nested-images is-cropped" style="margin-top:2rem">' . $children . '</figure>';
$GLOBALS['settings'] = array('override_default_gallery' => 0);
check(vsg_render_gallery_block_content($content, array()) === $content, 'Ordinary gallery must be unchanged');
$GLOBALS['settings'] = array('override_default_gallery' => 1, 'override_display_mode' => 'individual');
foreach (array('scroll', 'individual') as $mode) {
    $out = vsg_render_gallery_block_content($content, array('attrs' => array('displayMode' => $mode)));
    check(strpos($out, $children) !== false, "$mode must preserve children byte-for-byte, including an image without an attachment ID");
    check(strpos($out, 'id="lesson-pages"') !== false && strpos($out, 'alignfull custom-class') !== false && strpos($out, 'style="margin-top:2rem"') !== false, "$mode must preserve anchor, classes, alignment and styles");
    check(substr_count($out, '<img ') === 1, "$mode must not duplicate images");
    check(strpos($out, $mode === 'scroll' ? 'vsg-list-view' : 'vsg-individual-view') !== false, "$mode class");
    check(($mode === 'scroll') === (strpos($out, 'tabindex="0"') !== false), "$mode keyboard focus");
}
check(vsg_render_gallery_block_content($content, array('attrs' => array('displayMode' => 'default'))) === $content, 'Explicit WordPress layout beats global override');
check(vsg_get_display_mode(array()) === 'individual', 'Unset mode inherits settings');
check(vsg_get_display_mode(array('displayMode' => 'inherit')) === 'individual', 'Explicit inheritance');
check(vsg_get_display_mode(array('displayMode' => 'invalid')) === 'default', 'Invalid mode fails closed');
check(vsg_get_display_mode(array('className' => 'custom is-style-vertical-scroll-gallery alignwide')) === 'scroll', 'Legacy variation marker');
check(vsg_get_display_mode(array('className' => 'not-is-style-vertical-scroll-gallery')) === 'individual', 'Marker must match an entire class');
$GLOBALS['settings']['override_display_mode'] = 'scroll';
check(vsg_get_display_mode(array()) === 'scroll', 'Setting changes must not be stuck in a static cache');
$named = str_replace('id="lesson-pages"', 'id="lesson-pages" aria-labelledby="lesson-title"', $content);
$out = vsg_render_gallery_block_content($named, array());
check(strpos($out, 'aria-labelledby="lesson-title"') !== false && strpos($out, 'aria-label=') === false, 'Preserve accessible name');
$legacy = '<figure class="wp-block-gallery columns-3"><ul class="blocks-gallery-grid"><li class="blocks-gallery-item"><figure><img src="/legacy.png" /><figcaption>Legacy caption</figcaption></figure></li></ul></figure>';
$out = vsg_render_gallery_block_content($legacy, array());
check(strpos($out, 'vsg-list-view') !== false && strpos($out, '<ul class="blocks-gallery-grid">') !== false, 'Legacy gallery without innerBlocks');
check(vsg_render_gallery_block_content('<p>Other output</p>', array()) === '<p>Other output</p>', 'Other renderer output preserved');
check(vsg_render_gallery_block_content('', array()) === '', 'Empty gallery preserved');
check(vsg_sanitize_settings(null) === array('override_default_gallery' => 0, 'override_display_mode' => 'scroll'), 'Missing settings safe');
check(vsg_sanitize_settings(array('override_default_gallery' => 0, 'override_display_mode' => '<script>')) === array('override_default_gallery' => 0, 'override_display_mode' => 'scroll'), 'Invalid settings safe');
$attribute = vsg_register_gallery_attributes(array(), 'core/gallery')['attributes']['displayMode'];
check($attribute['default'] === 'inherit' && in_array('default', $attribute['enum'], true), 'Explicit default serializes instead of being omitted');
echo "$count gallery regression assertions passed.\n";
