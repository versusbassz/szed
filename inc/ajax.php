<?php
declare(strict_types=1);

namespace szed\ajax;

use function szed\util\arrays_equals;
use function szed\util\get_attachment_sizes_for_editor;
use function szed\util\get_size_file_name;
use function szed\util\get_sizes_global_data;
use function szed\util\load_view;

function handle_ajax_response_callback()
{
    $request = $_POST;

    $response = _handle_ajax_response_callback($request);

    echo json_encode($response);
    die();
}

function _handle_ajax_response_callback(array $params)
{
    $result_data = handle_ajax_response($params);

    if (is_wp_error($result_data)) {
        $response = [
            'result' => 'fail',
            'data' => $result_data->get_error_codes(),
            'debug' => [
                'params' => $params,
            ],
        ];
    } else {
        $response = [
            'result' => 'success',
            'data' => $result_data,
            'debug' => [
                'params' => $params,
            ],
        ];
    }

    return $response;
}

function handle_ajax_response(array $request)
{
    // user logged in
    if (! is_user_logged_in()) {
        return new \WP_Error('szed.ajax.crop.user_not_logged_in', 'Пользователь не залогинен.');
    }

    // user has capabilities
    if (! current_user_can(SZED_CAPABILITY)) {
        return new \WP_Error('szed.ajax.crop.user_doesnt_have_capabilities', 'У пользователя нет прав на редактирование медиа-файлов.');
    }

    // nonce verifying
    if (! isset($request['nonce']) || ! is_string($request['nonce']) || ! wp_verify_nonce($request['nonce'], SZED_NONCE) ) {
        return new \WP_Error('szed.ajax.crop.nonce_check_failed', 'Проверка токена операции закончилась неудачно. Пожалуйста, повторите попытку.');
    }

    // correct params
    if (
        ! isset($request['x']) || ! is_numeric($request['x'])
        ||
        ! isset($request['y']) || ! is_numeric($request['y'])
        ||
        ! isset($request['width']) || ! is_numeric($request['width'])
        ||
        ! isset($request['height']) || ! is_numeric($request['height'])
    ) {
        return new \WP_Error('szed.ajax.crop.incorrect_request_params', 'Некорректные параметры запроса');
    }

    if (
        ! isset($request['scaleX']) || $request['scaleX'] !== '1'
        ||
        ! isset($request['scaleY']) || $request['scaleY'] !== '1'
        ||
        ! isset($request['width']) || ! is_numeric($request['width'])
        ||
        isset($request['rotate'])
    ) {
        return new \WP_Error('szed.ajax.crop.incorrect_request_static_params', 'Некорректные статичные параметры запроса');
    }

    $params_for_meta = [
        'x' => absint($request['x']),
        'y' => absint($request['y']),
        'width' => absint($request['width']),
        'height' => absint($request['height']),
        'scaleX' => absint($request['scaleX']),
        'scaleY' => absint($request['scaleY']),
        'rotate' => 0,
    ];

    $params = $params_for_meta;

    // valid size_id param
    $global_sizes = get_sizes_global_data();
    $valid_sizes = array_keys($global_sizes);

    if (! isset($request['size_id']) || ! in_array($request['size_id'], $valid_sizes)) {
        return new \WP_Error('szed.ajax.crop.incorrect_size_id', 'Некорректный id размера изображения');
    }

    $size_id = $request['size_id'];
    $params['size_id'] = $size_id;
    $size = $global_sizes[$size_id];

    // size has cropping possibility
    if ($size['crop'] !== true) {
        return new \WP_Error('szed.ajax.crop.cropping_isnt_allowed', 'Для данного размера запрещена обрезка');
    }

    // valid image_id
    if (! isset($request['image_id']) || ! is_numeric($request['image_id'])) {
        return new \WP_Error('szed.ajax.crop.incorrect_image_id', 'Некорректный id изображения');
    }

    $image_id = absint($request['image_id']);
    $params['image_id'] = $image_id;
    $image = get_post($image_id);

    if (! ($image instanceof \WP_Post) || $image->post_type !== 'attachment') {
        return new \WP_Error('szed.ajax.crop.incorrect_image', 'Изображение не найдено в БД или находится некорректная запись');
    }

    // prepare for cropping
    $image_sizes = get_attachment_sizes_for_editor($image_id);
    $image_size = $image_sizes[$size_id];
    $full_size = $image_sizes['full'];

    $attached_file = $full_size['image']['path'];
    $result_path = get_size_file_name($image_id, $size_id);

    // cropping
    $editor = wp_get_image_editor($attached_file);

    if (is_wp_error($editor)) {
        return $editor;
    }

    $crop_result = $editor->crop(
        $params['x'],
        $params['y'],
        $params['width'],
        $params['height'],
        $size['width'],
        $size['height']
    );

    if (is_wp_error($crop_result)) {
        return $crop_result;
    }

    $save_result = $editor->save($result_path);

    if (is_wp_error($save_result)) {
        return $save_result;
    }

    // saving size meta
    $size_meta_to_insert = [
        'file' => basename($result_path),
        'width' => $size['width'],
        'height' => $size['height'],
        'mime-type' => $image->post_mime_type,
    ];

    $image_meta_source = wp_get_attachment_metadata($image_id);
    $image_meta = $image_meta_source;

    if (! isset($image_meta['sizes'])) {
        $image_meta['sizes'] = [];
    }

    if (! $image_size['has-size'] || ! arrays_equals($image_meta['sizes'][$size_id], $size_meta_to_insert)) {
        $image_meta['sizes'][$size_id] = $size_meta_to_insert;
    }

    // saving plugin cropping params
    if (! isset($image_meta['szed-sizes-params'])) {
        $image_meta['szed-sizes-params'] = [];
    }

    $image_meta['szed-sizes-params'][$size_id] = $params_for_meta;

    // removing old cropping params of other plugins for current size
    if (isset($image_meta[SZED_MIC_META]) && isset($image_meta[SZED_MIC_META][$size_id])) {
        unset($image_meta[SZED_MIC_META][$size_id]);

        if (! count($image_meta[SZED_MIC_META])) {
            unset($image_meta[SZED_MIC_META]);
        }
    }

    // execute saving attachment metadata
    if (! arrays_equals($image_meta_source, $image_meta)) {

        // is that possible to check the result here for anything logical ???
        wp_update_attachment_metadata($image_id, $image_meta);
    }

    // form new info for sizes list on editor page
    $new_image_sizes = get_attachment_sizes_for_editor($image_id);
    $row_layout = load_view(SZED_PLUGIN_PATH . '/views/size-info.php', [
        'size-data' => $new_image_sizes[$size_id],
    ]);

    // form response
    $response = [
        'url' => wp_get_attachment_image_url($image_id, $size_id),
        'crop_params' => $params_for_meta,
        'row-layout' => $row_layout,
    ];

    return $response;
}
