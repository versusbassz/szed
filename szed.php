<?php

/**
 * Plugin Name: szed
 * Description: ---
 * Version: 0.2.0
 * Author: Vladimir Sklyar
 * Author URI: https://profiles.wordpress.org/versusbassz/
 * License: GPL3
 *
 * Requires PHP: 7.4
 * Requires at least: 5.7
 */

if (! version_compare(PHP_VERSION, '7.4.0', '>=')) {
    return;
}

define('SZED_VERSION', '0.2.0');
define('SZED_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SZED_PLUGIN_PATH', plugin_dir_path(__FILE__));

require dirname(__FILE__) . '/inc/main.php';
