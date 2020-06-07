<?php

declare(strict_types=1);

namespace szed\debug;

/**
 * @param mixed $value Value to debug.
 * @param bool $die Die or not after function's launch end.
 * @param bool $htmlspecialchars Filter or not result text with 'htmlspecialchars' php function
 * @param bool $var_dump_strict Use var_dump always or not for arrays and objects.
 */
function dump($value, $die = false, $htmlspecialchars = true, $var_dump_strict = false)
{
    ob_start();

    $is_xdebug_enabled = extension_loaded('Xdebug') || extension_loaded('xdebug');

    if (! ($is_xdebug_enabled) && (is_array($value) || is_object($value)) && ! $var_dump_strict) {
        print_r($value);
    } else {
        var_dump($value);
    }

    $content = ob_get_contents();
    ob_end_clean();

    if ($is_xdebug_enabled) {
        echo $content;

        if ($die === true) {
            die;
        }

        return;
    }

    if ($htmlspecialchars === true) {
        $content = htmlspecialchars($content);
    }

    echo "<pre>{$content}</pre>";

    if ($die === true) {
        die;
    }
}
