<?php
namespace szed;

use WP_Post;
use function szed\util\fetch_env;
use function szed\util\get_asset_version;
use function szed\util\get_attachment_sizes_for_editor;
use function szed\util\is_valid_image;
use function szed\util\is_valid_mime_type;
use function szed\integration\fly_dynamic_image_resizer\is_fly_dynamic_image_resizer_activated;
use function szed\util\load_view;

require_once dirname(__DIR__) . '/vendor/autoload.php';

require_once __DIR__ . '/debug-helpers.php';
require_once __DIR__ . '/misc.php';
require_once __DIR__ . '/ajax.php';
require_once __DIR__ . '/generation.php';
require_once __DIR__ . '/links.php';
require_once __DIR__ . '/integrations/fly-dynamic-image-resizer/fly-dynamic-image-resizer.php';
require_once __DIR__ . '/user-api/misc.php';

define('SZED_AJAX_ACTION_NAME', 'szed-crop');
define('SZED_ADMIN_PAGE_SLUG', 'szed');
define('SZED_CAPABILITY', 'upload_files');
define('SZED_NONCE', 'szed-crop-image');
define('SZED_VALID_MIME_TYPES', [
    'image/jpeg',
    'image/png',
]);

define('SZED_ATTACHMENT_POST_TYPE', 'attachment');
define('SZED_MIC_META', 'micSelectedArea');

define('SZED_ENV', fetch_env());

add_action('init', 'szed\\links\\add_links_in_admin_panel');

// changing generation of sizes
add_action('init', function () {
    if (! is_fly_dynamic_image_resizer_activated()) {
        return;
    }

    add_filter('intermediate_image_sizes_advanced', 'szed\\util\\filter_generating_sizes', 10, 3);
});

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
    if (! isset($_GET['image-id'])) {
        echo load_view(SZED_PLUGIN_PATH . 'views/page-with-error.php', [
            'message' => '',
            'button-text' => 'Выбрать изображение',
        ]);
        return;
    }

    $is_valid_image_id_param = isset($_GET['image-id']) && is_numeric($_GET['image-id']);

    if (! $is_valid_image_id_param) {
        echo load_view(SZED_PLUGIN_PATH . 'views/page-with-error.php', [
            'message' => 'Некорректный ID изображения',
        ]);
        return;
    }

    $image_id = absint($_GET['image-id']);
    $image = get_post($image_id);
    $is_valid_image = is_valid_image($image);

    if (! $is_valid_image) {
        echo load_view(SZED_PLUGIN_PATH . 'views/page-with-error.php', [
            'message' => 'Изображение не найдено',
        ]);
        return;
    }

    $is_valid_mime_type = is_valid_mime_type($image->post_mime_type);

    if (! $is_valid_mime_type) {
        echo load_view(SZED_PLUGIN_PATH . 'views/page-with-error.php', [
            'message' => 'Редактор не поддерживает данный формат изображения.<br><b>Поддерживаемые форматы:</b> ' . implode(', ', SZED_VALID_MIME_TYPES),
        ]);
        return;
    }

    $sizes = get_attachment_sizes_for_editor($image_id);

    echo load_view(SZED_PLUGIN_PATH . 'views/page.php', [
        'image' => $image,
        'sizes' => $sizes,
    ]);
}

add_action('admin_enqueue_scripts', function () {
    global $pagenow;

    if ($pagenow === 'tools.php' && $_GET['page'] === SZED_ADMIN_PAGE_SLUG) {
        wp_enqueue_media();

        wp_enqueue_style(
            'szed-cropper-css',
            SZED_PLUGIN_URL . 'assets/build/cropper.css',
            [],
            get_asset_version(SZED_PLUGIN_PATH . 'assets/build/cropper.css')
        );

        wp_enqueue_style(
            'szed-admin-css',
            SZED_PLUGIN_URL . 'assets/build/editor-page.css',
            [],
            get_asset_version(SZED_PLUGIN_PATH . 'assets/build/editor-page.css')
        );

        wp_enqueue_script(
            'szed-editor-js',
            SZED_PLUGIN_URL . 'assets/build/sizes-editor.build.js',
            ['jquery'],
            get_asset_version(SZED_PLUGIN_PATH . 'assets/build/sizes-editor.build.js'),
            true
        );
    }
});

add_action('init', function () {
    if (wp_doing_ajax()) {
        add_action('wp_ajax_' . SZED_AJAX_ACTION_NAME, 'szed\\ajax\\handle_ajax_response_callback');
    }
});
