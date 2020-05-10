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


    return null;
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
