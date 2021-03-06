<?php

declare(strict_types=1);

namespace szed\util;

/**
 * @return array Each item is certain size info. Keys of items are sizes ids.
 *     id: string
 *     width: int
 *     height: int
 *     crop: bool|array
 *     default: bool
 */
function get_sizes_global_data(): array
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
        $crop = (bool) $_wp_additional_image_sizes[$size_id]['crop']; // basic support of [left,top] crop type

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

function calc_ratio(int $width, int $height, $crop)
{
    if (! is_valid_crop_value($crop)) {
        return null;
    }

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

    $crop_params = get_crop_params_from_meta($image_meta);

    $sizes_result = [];

    foreach ($sizes_source as $size_id => $size_data) {
        $size_path = get_image_size_path($image_id, $size_id);

        // compatibility with PTE
        $mime_type = get_mime_type_from_size_data($size_data, $size_path);

        $sizes_result[$size_id] = [
            'id' => $size_id,
            'width' => $size_data['width'],
            'height' => $size_data['height'],
            'mime-type' => $mime_type,
            'type' => get_type_by_mime((string) $mime_type),
            'path' => $size_path,
            'path-rel' => get_image_size_path($image_id, $size_id, true),
            'file' => $size_data['file'],
            'url' => wp_get_attachment_image_url($image_id, $size_id),
            'size' => get_size_global_data($size_id),
            'crop-params' => $crop_params[$size_id] ?? null,
        ];
    }

    $sizes_result['full'] = [
        'id' => 'full',
        'width' => $image_meta['width'],
        'height' => $image_meta['height'],
        'mime-type' => $image->post_mime_type,
        'type' => get_type_by_mime($image->post_mime_type),
        'path' => get_attached_file($image_id),
        'path-rel' => '/' . $image_meta['file'],
        'file' => basename($image_meta['file']),
        'url' => wp_get_attachment_image_url($image_id, 'full'),
        'size' => get_size_global_data('full'),
        'crop-params' => null,
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

function get_crop_params_from_meta(array $image_meta)
{
    $sizes = array_keys(get_sizes_global_data());

    $providers = [
        'szed' => isset($image_meta['szed-sizes-params']) && is_array($image_meta['szed-sizes-params']) ? $image_meta['szed-sizes-params'] : [],
        'mic' => isset($image_meta[SZED_MIC_META]) && is_array($image_meta[SZED_MIC_META]) ? $image_meta[SZED_MIC_META] : [],
    ];

    $result = [];

    foreach ($sizes as $size_id) {

        foreach ($providers as $provider_id => $provider_sizes) {
            if (isset($provider_sizes[$size_id])) {
                switch ($provider_id) {
                    case 'szed':
                        $result[$size_id] = $provider_sizes[$size_id];
                        break 2;

                    case 'mic':
                        $result[$size_id] = convert_params_from_mic_to_szed($provider_sizes[$size_id]);
                        break 2;

                    default:
                        break;
                }
            }
        }
    }

    return $result;
}

function convert_params_from_mic_to_szed(array $mic_params)
{
    $result = [
        'x' => absint($mic_params['x'] * $mic_params['scale']),
        'y' => absint($mic_params['y'] * $mic_params['scale']),
        'width' => absint($mic_params['w'] * $mic_params['scale']),
        'height' => absint($mic_params['h'] * $mic_params['scale']),
        'scaleX' => 1,
        'scaleY' => 1,
        'rotate' => 0,
    ];
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

/**
 * Returns version of a file for using in wp_enqueue_* API
 * @param string $file_path Path to file in filesystem
 *
 * @return false|int
 */
function get_asset_version(string $file_path)
{
    $mtime = filemtime($file_path);
    $ctime = filectime($file_path);

    return $mtime > $ctime ? $mtime : $ctime;
}

// just a part of other function, for readability
function get_mime_type_from_size_data(array $size_data, string $size_path): ?string
{
    $mime_type = null;

    // get mime from size data directly
    if (isset($size_data['mime-type']) && is_string($size_data['mime-type']) && $size_data['mime-type']) {
        $mime_type = $size_data['mime-type'];
    }

    // plugin PTE didnt save mime-type to size info in attachment meta, so... we try fetch it from file system
    if (is_null($mime_type)) {
        $image_info = getimagesize($size_path);

        if (is_array($image_info) && isset($image_info['mime'])) {
            $mime_type = $image_info['mime'];
        }
    }

    return $mime_type;
}

function wp_error_to_assoc_array(\WP_Error $errors)
{
    $code = $errors->get_error_codes();
    $messages = $errors->get_error_messages();

    $result = array_combine($code, $messages);

    return $result;
}

function is_valid_image($image)
{
    $result = $image instanceof \WP_Post && $image->post_type === SZED_ATTACHMENT_POST_TYPE;
    return $result;
}

function is_valid_crop_value($value)
{
    if (is_bool($value)) {
        return true;
    }

    if (is_array($value)) {
        $valid_x_values = [
            'left',
            'center',
            'right',
        ];

        $valid_y_values = [
            'top',
            'center',
            'bottom',
        ];

        if (count($value) === 2 && in_array($value[0], $valid_x_values) && in_array($value[1], $valid_y_values)) {
            return true;
        }
    }

    return false;
}

function image_has_separate_original_file(int $image_id)
{
    $meta = wp_get_attachment_metadata($image_id);

    $result = isset($meta['original_image']) && is_string($meta['original_image']) && $meta['original_image'];
    return $result;
}
