<?php
declare(strict_types=1);

namespace szed\util;

/**
 * @return array Each item is certain size info. Keys of items are sizes ids.
 *     id: string
 *     width: int
 *     height: int
 *     crop: bool
 *     default: bool
 */
function get_sizes_global_data() : array
{
    $intermediate_image_sizes = get_intermediate_image_sizes();

    $sizes = [];

    foreach ($intermediate_image_sizes as $size_id) {
        $sizes[$size_id] = get_size_global_data($size_id);
    }

    return $sizes;
}

function get_size_global_data(string $size_id)
{
    global $_wp_additional_image_sizes;

    $is_default_size = in_array($size_id, [
        'thumbnail',
        'medium',
        'medium_large',
        'large',
    ]);

    $size = null; // for "dead" sizes

    if ($is_default_size) {
        $width = (int) get_option($size_id . '_size_w');
        $height = (int) get_option($size_id . '_size_h');
        $crop = (bool) get_option($size_id . '_crop');

        $size = [
            'id' => $size_id,
            'width' => $width,
            'height' => $height,
            'crop' => $crop,
            'ratio' => calc_ratio($width, $height, $crop),
            'type' => get_size_type($size_id),
        ];
    } elseif (isset($_wp_additional_image_sizes[$size_id])) {
        $width = $_wp_additional_image_sizes[$size_id]['width'];
        $height = $_wp_additional_image_sizes[$size_id]['height'];
        $crop = $_wp_additional_image_sizes[$size_id]['crop'];

        $size = [
            'id' => $size_id,
            'width' => $width,
            'height' => $height,
            'crop' => $crop,
            'ratio' => calc_ratio($width, $height, $crop),
            'type' => get_size_type($size_id),
        ];
    } elseif ($size_id === 'full') {
        $size = [
            'id' => $size_id,
            'width' => null,
            'height' => null,
            'crop' => false,
            'ratio' => null,
            'type' => get_size_type($size_id),
        ];
    }

    return $size;
}

// object - type->isDefault() type->isAdditional() etc
function get_size_type(string $size_id)
{
    switch ($size_id) {
        case 'thumbnail':
        case 'medium':
        case 'medium_large':
        case 'large':
        case 'full':
            return 'default';
            break;

        case 'post-thumbnail':
            return 'default-optional';
            break;

        case '1536x1536':
        case '2048x2048':
            return 'default-additional';
            break;

        default:
            return 'custom';
            break;
    }
}

function calc_ratio(int $width, int $height, bool $crop)
{
    if (! $crop || $height <= 0) {
        return null;
    }

    $ratio = round(($width / $height), 4, PHP_ROUND_HALF_UP);

    return $ratio;
}

/*
 * key: id
 * width (real)
 * height (real)
 * path: string - absolute path
 * path_rel: string -
 * file: string - filename (raw from size meta)
 * url_absolute
 * size: [] (see get_size_global_data())
 */
function get_attachment_sizes(int $image_id)
{
    $image = get_post($image_id);
    $image_meta = wp_get_attachment_metadata($image_id);

    if (! is_array($image_meta) || ! isset($image_meta['sizes']) || ! is_array($image_meta['sizes']) || ! count($image_meta['sizes'])) {
        return [];
    }

    $sizes_source = $image_meta['sizes'];

    $crop_params = isset($image_meta['szed-sizes-params']) && is_array($image_meta['szed-sizes-params']) ? $image_meta['szed-sizes-params'] : [];

    $sizes_result = [];

    foreach ($sizes_source as $size_id => $size_data) {

        $sizes_result[$size_id] = [
            'id' => $size_id,
            'width' => $size_data['width'],
            'height' => $size_data['height'],
            'mime-type' => $size_data['mime-type'],
            'type' => get_type_by_mime($size_data['mime-type']),
            'path' => get_image_size_path($image_id, $size_id),
            'path-rel' => get_image_size_path($image_id, $size_id, true),
            'file' => $size_data['file'],
            'url' => wp_get_attachment_image_url($image_id, $size_id),
            'size' => get_size_global_data($size_id),
            'crop-params' => $crop_params,
        ];
    }

    $sizes_result['full'] = [
        'id' => 'full',
        'width' => $image_meta['width'],
        'height' => $image_meta['height'],
        'mime-type' => $image->post_mime_type,
        'type' => get_type_by_mime($image->post_mime_type, 'full'),
        'path' => get_attached_file($image_id),
        'path-rel' => '/' . $image_meta['file'],
        'file' => basename($image_meta['file']),
        'url' => wp_get_attachment_image_url($image_id, 'full'),
        'size' => get_size_global_data('full'),
    ];

    return $sizes_result;
}

// TODO
// get_attachment_data: attachment_id : []
/*
 * ID: int - copy from
 * post: WP_Post
 * width: int
 * height: int
 * file: string - rel path (raw from meta)
 * image_meta: [] - (raw from meta)
 *
 * sizes: []
 * path: string - absolute path --- get_attached_file()
 * path_rel: string --- get_post_meta($image_id, '_wp_attached_file', true)
 * url: string - absolute url
 */

function get_attachment_sizes_for_editor(int $image_id)
{
    $global_sizes = get_sizes_global_data();

    $image_sizes = get_attachment_sizes($image_id);

    $result = [];

    foreach ($global_sizes as $global_size_id => $global_size) {
        // in this moment (foreach logic) dead sizes are excluded

        $image_has_size = isset($image_sizes[$global_size_id]);
        $image_size = $image_has_size ? $image_sizes[$global_size_id] : null;

        $result[$global_size_id] =  [
            'id' => $global_size_id,
            'data' => $global_size,
            'has-size' => $image_has_size,
            'file-exists' => $image_has_size ? file_exists($image_size['path']) : false,
            'is-possible' => $global_size['crop'] && is_size_possible(
                $image_sizes['full']['width'],
                $image_sizes['full']['height'],
                $global_size['width'],
                $global_size['height']
            ),
            'image' => $image_size,
        ];
    }

    $result['full'] = [
        'id' => 'full',
        'data' => get_size_global_data('full'),
        'has-size' => true, // is it necessary to do smth here?
        'file-exists' => file_exists($image_sizes['full']['path']),
        'image' => $image_sizes['full'],
    ];

    return $result;
}

/**
 * @param int $image_id
 * @param string $size_id
 *
 * @return bool|string|string[]
 */
function get_image_size_path(int $image_id, string $size_id, bool $relative = false)
{
    $uploads_dir = wp_upload_dir();
    $src_file_url = wp_get_attachment_image_src($image_id, $size_id);

    if (! $src_file_url) {
        return false;
    }

    $replace = $relative ? '' : $uploads_dir['basedir'];
    $src_file = str_replace($uploads_dir['baseurl'], $replace, $src_file_url[0]);

    return $src_file;
}

function get_size_file_name(int $image_id, string $size_id)
{
    // TODO validation

    $attached_file = wp_get_original_image_path($image_id);
    $size = get_size_global_data($size_id);

    $dir = pathinfo($attached_file, PATHINFO_DIRNAME);
    $ext = pathinfo($attached_file, PATHINFO_EXTENSION);

    $name = wp_basename($attached_file, ".{$ext}");
    $suffix = $size['width'] . 'x' . $size['height'];

    $result = "{$dir}/{$name}-{$suffix}.{$ext}";

    return $result;
}

function arrays_equals(array $list_1, array $list_2)
{
    return serialize($list_1) === serialize($list_2);
}

function is_valid_mime_type(string $type)
{
    return in_array($type, SZED_VALID_MIME_TYPES);
}

// this logic isn't enough coz of param crop (true/false)
// this function should have interface like: (int image_id, int size_id) : return bool
// and should be able to work with crop:false sizes
function is_size_possible(int $source_width, int $source_height, int $size_width, int $size_height)
{
    return $source_width >= $size_width && $source_height >= $size_height;
}

function get_crop_page_url(int $image_id)
{
    $page_slug = SZED_ADMIN_PAGE_SLUG;
    $result_url = get_admin_url(null, "/tools.php?page={$page_slug}&image-id={$image_id}");
    return $result_url;
}

function get_type_by_mime(string $mime_type)
{
    $expected_types = [
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
    ];

    if (! in_array($mime_type, array_keys($expected_types))) {
        return null;
    }

    $result = $expected_types[$mime_type];
    return $result;
}

// private helper
function fetch_env()
{
    $file_path = SZED_PLUGIN_PATH . '/env.php';

    if (! file_exists($file_path)) {
        return [];
    }

    $env = require $file_path;

    if (! is_array($env)) {
        return [];
    }

    return $env;
}

// private helper
function get_env(string $key = '')
{
    if (! defined('SZED_ENV')) {
        return $key ? null : [];
    }

    // all params
    if (! $key) {
        $env = SZED_ENV;
        return $env;
    }

    // only single key
    $env = SZED_ENV;

    if (isset($env[$key])) {
        return $env[$key];
    }

    return null;
}

function load_view($path, $data = [])
{
    if (! file_exists($path)) {
        throw new \Exception(__FUNCTION__ . ": view file not exists: {$path}");
    }

    ob_start();

    require $path;

    return ob_get_clean();
}

/**
 * Check if Classic Editor plugin is active.
 *
 * @see https://wordpress.stackexchange.com/questions/320653/how-to-detect-the-usage-of-gutenberg
 * @return bool
 */
function is_classic_editor_plugin_active()
{
    if (! function_exists('is_plugin_active')) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $result = is_plugin_active('classic-editor/classic-editor.php');

    return $result;
}

// temporary function. Delete after original size will be added to common sizes list
function get_original_file_info(int $image_id)
{
    $image_path = wp_get_original_image_path($image_id);

    $info = getimagesize($image_path);

    $result = [
        'width' => $info[0],
        'height' => $info[1],
        'mime-type' => $info['mime'],
        'type' => get_type_by_mime($info['mime']),
        'url' => wp_get_original_image_url($image_id),
    ];

    return $result;
}
