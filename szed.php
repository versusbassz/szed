<?php
/*
Plugin Name: szed
Description: ---
Version: 0.1.0
Author: Vladimir Sklyar
Author URI: https://profiles.wordpress.org/versusbassz/
License: GPL3
*/

namespace szed;

use function szed\util\fetch_env;
use function szed\util\get_attachment_sizes_for_editor;
use function szed\util\get_crop_page_url;
use function szed\util\is_valid_mime_type;

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/inc/debug-helpers.php';
require_once __DIR__ . '/inc/misc.php';
require_once __DIR__ . '/inc/ajax.php';
require_once __DIR__ . '/inc/links.php';

define('SZED_VERSION', '0.1.0');
define('SZED_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SZED_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SZED_AJAX_ACTION_NAME', 'szed-crop');
define('SZED_ADMIN_PAGE_SLUG', 'szed');
define('SZED_CAPABILITY', 'upload_files');
define('SZED_VALID_MIME_TYPES', [
    'image/jpeg',
    'image/png',
]);

define('SZED_ENV', fetch_env());

add_action('init', 'szed\\links\\add_links_in_admin_panel');

add_action('admin_menu', function () {

    if (! current_user_can(SZED_CAPABILITY)) {
        return;
    }

    add_submenu_page(
       'tools.php',
       'Sizes editor',
       'Sizes editor',
       SZED_CAPABILITY,
       SZED_ADMIN_PAGE_SLUG,
       '\\szed\\render_admin_page'
    );
});

function render_admin_page()
{
    $image_id = isset($_GET['image-id']) && is_numeric($_GET['image-id']) ? absint($_GET['image-id']) : null;

    $image = get_post($image_id);
    $sizes = get_attachment_sizes_for_editor($image_id);

    $is_valid_image_id = ! is_null($image_id) && $image instanceof \WP_Post && $image->post_type === 'attachment';
    $is_valid_mime_type = is_valid_mime_type($image->post_mime_type);

    $show_editor = $is_valid_mime_type;

    require __DIR__ . '/views/page-header.php';

    if ($show_editor) {
        require __DIR__ . '/views/page.php';
    } elseif (! $is_valid_mime_type) {
        echo '<p>Некорректный ID изображения</p>';
    } elseif (! $is_valid_image_id) {
        echo '<p>Редактор не поддерживает данный формат изображения.<br>Поддерживаемые форматы: ' . implode(',', SZED_VALID_MIME_TYPES) . '</p>';
    }
}

add_action('admin_enqueue_scripts', function () {
    global $pagenow;

    if ($pagenow === 'tools.php' && $_GET['page'] === SZED_ADMIN_PAGE_SLUG) {
        wp_enqueue_media();
        wp_enqueue_style('szed-cropper-css', SZED_PLUGIN_URL . 'assets/build/cropper.css', [], SZED_VERSION);
        wp_enqueue_style('szed-admin-css', SZED_PLUGIN_URL . 'assets/build/editor-page.css', [], SZED_VERSION);
        wp_enqueue_script('szed-editor-js', SZED_PLUGIN_URL . 'assets/build/sizes-editor.build.js', ['jquery'], SZED_VERSION, true);
    }
});

add_action('init', function () {
    if (wp_doing_ajax()) {
        add_action('wp_ajax_' . SZED_AJAX_ACTION_NAME, 'szed\\ajax\\handle_ajax_response_callback');
    }
});
