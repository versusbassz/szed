<?php

declare(strict_types=1);

namespace szed\util;

/**
 * @param array $new_sizes     Associative array of image sizes to be created.
 * @param array $image_meta    The image meta data: width, height, file, sizes, etc.
 * @param int   $attachment_id The attachment post ID for the image.
 */
function filter_generating_sizes($new_sizes, $image_meta, $attachment_id)
{
    $new_sizes_ids = apply_filters('szed/generating_sizes_whitelist', array_keys($new_sizes), $new_sizes);

    $new_sizes_ids = array_unique($new_sizes_ids);

    $result = [];

    foreach ($new_sizes_ids as $size_id) {
        $result[$size_id] = $new_sizes[$size_id];
    }

    return $result;
}
