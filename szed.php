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

use WP_CLI;
use function szed\util\get_sizes_global_data;

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/inc/debug.php';
require_once __DIR__ . '/inc/misc.php';

define('SZED_PLUGIN_URL', plugin_dir_url(__FILE__));


add_action('admin_menu', function () {
   add_submenu_page(
       'tools.php',
       'szed debug',
       'szed debug',
       'delete_plugins',
       'szed-debug',
       '\\szed\\render_debug_page'
   );
});

add_action('wp_body_open', '\\szed\\render_debug_page');

function render_debug_page()
{
    echo '<h1>debug page content</h1>';

    $image_id = 5;

    $sizes = get_intermediate_image_sizes();

    $image = get_post($image_id);
    $image__meta = get_post_meta($image_id);
    $image__attached_file = get_post_meta($image_id, '_wp_attached_file', true);
    $image__attachment_meta = get_post_meta($image_id, '_wp_attachment_metadata', true);

//    dump($image__attachment_meta);
    ?>

    <div style="margin-bottom: 40px;">
        <input type="text" id="hh-chose-img" value="">
    </div>

    <div style="width: 800px; border: 1px solid #ffccaa;">
        <div>
            <img id="hh-image" src="<?= wp_get_attachment_url($image_id); ?>">
        </div>
    </div>

    <div>
        <div class="hh-preview"></div>
    </div>

    <style type="text/css">
        img {
            display: block;

            /* This rule is very important, please don't ignore this */
            max-width: 100%;
        }

        .hh-preview {
            width: 200px;
        }

        .hh-preview img {
            max-width: 100%;
        }
    </style>

    <?php
}

if (defined('WP_CLI')) {
    WP_CLI::add_command('szed:debug', function () {
        $image_id = 5;

        $image__attachment_meta = get_post_meta($image_id, '_wp_attachment_metadata', true);

//        dump($image__attachment_meta);

//        $sizes = wp_get_attachment_image_sizes($image_id);
//        dump($sizes);

        add_image_size('test-square-small', 100, 100, true);

        $sizes = get_sizes_global_data();
        dump($sizes);
    });
}

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style('szed-cropper-css', SZED_PLUGIN_URL . 'assets/build/cropper.css');
    wp_enqueue_script('szed-editor-js', SZED_PLUGIN_URL . 'assets/build/sizes-editor.build.js', [], 'asdf', true);

    global $pagenow;

    if ($pagenow === 'tools.php' && $_GET['page'] === 'szed-debug') {
        wp_enqueue_media();
    }
});
