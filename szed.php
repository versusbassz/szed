<?php
/**
 * Plugin Name: szed
 * Description: ---
 * Version: 0.1.0
 * Author: Vladimir Sklyar
 * Author URI: https://profiles.wordpress.org/versusbassz/
 * License: GPL3
 *
 * Requires PHP: 7.1
 * Requires at least: 5.3
 */

if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
    require dirname(__FILE__) . '/inc/main.php';
}
