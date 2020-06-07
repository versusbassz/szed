<?php

declare(strict_types=1);

namespace szed\links;

use function szed\util\get_crop_page_url;
use function szed\util\is_classic_editor_plugin_active;
use function szed\util\is_valid_mime_type;

function add_links_in_admin_panel()
{
    if (! is_admin() || wp_doing_ajax()) {
        return;
    }

    add_action('admin_footer', 'szed\links\add_link_to_featured_image_metabox', 101);
    add_action('print_media_templates', 'szed\links\add_link_to_media_library_ui', 10);
}

/**
 * @see https://developer.wordpress.org/reference/hooks/media_row_actions/
 */
function add_link_to_media_row_actions(array $actions, \WP_Post $post, bool $detached)
{
    if (is_valid_mime_type($post->post_mime_type)) {
        $crop_page_url = get_crop_page_url($post->ID);
        $actions['szed-crop'] = '<a target="_blank" href="' . esc_attr($crop_page_url) . '">Редактировать размеры</a>';
    }

    return $actions;
}


/**
 * Adds link below "Remove featured image" in post editing form (Classic Editor or WP 4.9 and below)
 */
function add_link_to_featured_image_metabox()
{
    global $pagenow;

    if (! in_array($pagenow, ['post.php', 'post-new.php'])) {
        return;
    }
    
    if (! is_classic_editor_plugin_active()) {
        return;
    }

    $editor_url = get_crop_page_url(0); // dirty hack...
    ?>

<script>
    jQuery(document).ready(function($) {

        var editor_url_template = '<a id="szed-featured-image-metabox-link" href="<?= esc_attr($editor_url) ?>" target="_blank">Редактировать размеры</a>';

        var current_input_value = 0;

        setInterval(function() {

            var $thumbnail_input = $('#_thumbnail_id');

            if ($thumbnail_input.length) {

                var input_value = $thumbnail_input.val();

                if (input_value === '-1' || input_value === -1) {
                    remove_link();
                    return;
                }

                if (input_value && input_value !== current_input_value) {
                    var link = editor_url_template.split('image-id=0').join('image-id=' + input_value);
                    $thumbnail_input.parents('.inside').first().append($(link));

                    current_input_value = input_value;
                }
            }
        }, 2000);

        function remove_link () {
            var $prev_link = $('#szed-featured-image-metabox-link');

            if ($prev_link.length) {
                $prev_link.remove();
            }
        }

    });
</script>

    <?php
}


/**
 * Adds link in the media library UI
 */
function add_link_to_media_library_ui()
{
    $editor_url = get_crop_page_url(0); // dirty hack...
    ?>

<script>
    jQuery(document).ready(function($) {

        // common
        var editor_url_template = '<a href="<?= esc_attr($editor_url) ?>" class="szed-js__edit-link" target="_blank">Редактировать размеры</a>';

        var current_edit_id = 0;
        var current_media_id = 0;

        // for Media:grid
        var initial_url = location.href;
        var initial_media_id = get_item_id_from_url(location.href);

        function get_item_id_from_url(url) {
            var historyRegexp = /\?item=([0-9]+)/;
            var historyRegexpMatch = historyRegexp.exec(url);

            if (historyRegexpMatch && historyRegexpMatch.length) {
                return historyRegexpMatch[1];
            }

            return null;
        }

        setInterval(function() {

            // Edit Post - Choose thumbnail or Add Image in Post content
            var $edit_link = $('.details .edit-attachment');

            if ($edit_link.length) {
                try {
                    var mRegexp = /\?post=([0-9]+)/;
                    var match = mRegexp.exec($edit_link.attr('href'));
                    var post_id = match[1];

                    if (current_edit_id !== post_id) {
                        remove_link('.szed-js__edit-link');
                        current_edit_id = post_id;

                        var link = editor_url_template.split('image-id=0').join('image-id=' + post_id);
                        $edit_link.after($(link));
                    }

                } catch (e) {
                    console.log(e);
                }
            } else {
                current_edit_id = 0;
            }

            // Media - grid layout
            if ($('.attachment-details .details-image').length) {
                try {
                    var media_post_id = get_item_id_from_url(location.href);

                    // this condition is for bug in wordpress core
                    // how to reproduce this situation:
                    //     start media:grid with item_id={int} -->
                    //     close image details window -->
                    //     open same (initial) image details window -->
                    //     bug: url isn't recovered to proper value (with item_id)
                    // variable "button_exists" placed below is for this case too
                    if (! media_post_id && initial_media_id) {
                        media_post_id = initial_media_id;
                    }

                    var item_id_changed = current_media_id !== media_post_id;

                    // this variable is to handle bug described above
                    var button_exists = $('.attachment-details .szed-js__edit-link').length;

                    if (item_id_changed || ! button_exists) {
                        remove_link('.szed-js__edit-link');
                        current_media_id = media_post_id;

                        var link = editor_url_template.split('image-id=0').join('image-id=' + media_post_id);
                        $('.button.edit-attachment')
                            .after($(link)
                            .addClass('button')
                            .css({'margin-left' : '5px'}));
                    }
                } catch (e) {
                    console.log(e);
                }
            } else {
                current_media_id = 0;
            }
        }, 1000);

        function remove_link (selector) {
            var $link = $(selector);

            if ($link.length) {
                $link.remove();
            }
        }
    });
</script>

    <?php
}
