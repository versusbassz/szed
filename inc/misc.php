<?php
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
    global $_wp_additional_image_sizes;
    $intermediate_image_sizes = get_intermediate_image_sizes();

    $sizes = [];

    foreach ($intermediate_image_sizes as $size_id) {

        $is_default_size = in_array($size_id, [
            'thumbnail',
            'medium',
            'medium_large',
            'large',
        ]);

        if ($is_default_size) {
            $size = [
                'id' => $size_id,
                'width' => get_option($size_id . '_size_w'),
                'height' => get_option($size_id . '_size_h'),
                'crop' => (bool) get_option($size_id . '_crop'),
                'default' => true,
            ];
        } elseif (isset ($_wp_additional_image_sizes[$size_id])) {
            $size = [
                'id' => $size_id,
                'width' => $_wp_additional_image_sizes[$size_id]['width'],
                'height' => $_wp_additional_image_sizes[$size_id]['height'],
                'crop' => $_wp_additional_image_sizes[$size_id]['crop'],
                'default' => false,
            ];
        }

        $sizes[$size_id] = $size;
    }

    return $sizes;
}


// TODO
// get_attachment_sizes: attachment_id : []
/*
 * key: id
 * width (real)
 * height (real)
 * path: string - absolute path
 * path_rel: string -
 * file: string - filename (raw from size meta)
 * url_absolute
 * size: [] (see get_sizes_global_data() items)
 */

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
 * path: string - absolute path
 * url: string - absolute url
 */

