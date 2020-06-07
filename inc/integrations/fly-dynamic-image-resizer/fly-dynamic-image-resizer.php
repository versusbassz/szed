<?php

declare(strict_types=1);

namespace szed\integration\fly_dynamic_image_resizer;

function is_fly_dynamic_image_resizer_activated()
{
    // maybe checking "active_plugins" option is better ???

    $result = defined('JB_FLY_PLUGIN_PATH') && JB_FLY_PLUGIN_PATH;
    return $result;
}
