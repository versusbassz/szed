<?php
declare(strict_types=1);

namespace szed;

use WP_Error;
use WP_Post;
use function szed\integration\fly_dynamic_image_resizer\is_fly_dynamic_image_resizer_activated;

/**
 * @param int $attachment_id
 * @param string $size_id
 *
 * @return string|WP_Error
 */
function get_attachment_image_src(int $attachment_id, string $size_id)
{
    $image = get_post($attachment_id);

    if (! ($image instanceof WP_Post)) {
        return new WP_Error('szed.incorrect_attachment_id', 'Передан некорректный ID изображения');
    } elseif ($image->post_type !== 'attachment') {
        return new WP_Error('szed.post_is_not_attachment', 'Запись не является изображением');
    }

    $image_meta = wp_get_attachment_metadata($attachment_id);

    $image_has_size = is_array($image_meta) && isset($image_meta['sizes']) && isset($image_meta['sizes'][$size_id]);

    if ($image_has_size) {
        $result = wp_get_attachment_image_url($attachment_id, $size_id);

        // what if image doesnt exist on disk ??? dont check that yet, maybe better to fallback to fly resizer
        if (! is_string($result)) {
            return new WP_Error('szed.getting_normal_size_url_failed', 'Не удалось получить ссылку на изображение');
        }

        return $result;
    }

    if (! is_fly_dynamic_image_resizer_activated()) {
        return new WP_Error('szed.getting_normal_size_url_failed', 'Не удалось получить ссылку на изображение');
    }

    $global_sizes = wp_get_registered_image_subsizes();
    $has_global_size = is_array($global_sizes) && isset($global_sizes[$size_id]);

    if (! $has_global_size) {
        return new WP_Error('szed.global_size_not_found', 'Информация об указанном размере не найдена');
    }

    $global_size = $global_sizes[$size_id];

    $custom_data = fly_get_attachment_image_src($attachment_id, [$global_size['width'], $global_size['height']], $global_size['crop']);

    // what if generating of image has failed ??? return wp_error for now
    if (! is_array($custom_data) || ! isset($custom_data['src']) || ! is_string($custom_data['src'])) {
        return new WP_Error('szed.getting_dynamic_size_url_failed', 'Не удалось получить ссылку на изображение.');
    }

    $custom_url = $custom_data['src'];

    return $custom_url;
}
