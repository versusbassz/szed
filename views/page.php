<?php
use function szed\util\get_env;
use function szed\util\image_has_separate_original_file;
use function szed\util\load_view;
use function szed\util\get_original_file_info;

/** @var array $data */

/** @var \WP_Post $image */
$image = $data['image'];

/** @var array $sizes */
$sizes = $data['sizes'];

$image_id = $image->ID;

$ajax_url = get_admin_url(null, 'admin-ajax.php?action=' . SZED_AJAX_ACTION_NAME);
$edit_url__list = get_edit_post_link($image_id);

$edit_url__grid_default = get_admin_url(null , "/upload.php?item={$image_id}");
$user_grid_mode = get_user_meta(get_current_user_id(), 'wp_media_library_mode', true);

switch ($user_grid_mode) {
    case 'grid':
        $edit_url__grid_direct = $edit_url__grid_default;
        break;

    case 'list':
    default:
        $edit_url__grid_direct = add_query_arg([
            'mode' => 'grid',
        ], $edit_url__grid_default);
        break;
}

$is_debug = get_env('debug');

$full_size = $sizes['full'];
$has_separate_original_file = image_has_separate_original_file($image_id);
$original_size = get_original_file_info($image_id);

$nonce = wp_create_nonce(SZED_NONCE);
?>

<div class="wrap">

<?php require __DIR__ . '/page-header.php'; ?>

<div class="hh-editor-page">

    <div class="hh-editor-page__secondary">

        <div class="hh-sizes-list">
            <div class="hh-sizes-list__item">
                <div class="hh-sizes-list__item-cell hh-sizes-list__item-cell--choose"></div>
                <div class="hh-sizes-list__item-cell hh-sizes-list__item-cell--id">ID</div>
                <div class="hh-sizes-list__item-cell hh-sizes-list__item-cell--params">Params</div>
                <div class="hh-sizes-list__item-cell hh-sizes-list__item-cell--misc">Misc</div>
            </div>

            <?php foreach ($sizes as $size_id => $size_data) {
                $crop_params = $size_data['image']['crop-params'] ?? [];
                $can_crop = $size_data['data']['crop'] && $size_data['is-possible'];
                ?>

                <div class="hh-sizes-list__item js-szed__size-item" data-size-id="<?= esc_attr($size_id) ?>">

                    <div class="hh-sizes-list__item-cell hh-sizes-list__item-cell--choose">
                        <input
                            <?= $can_crop ? '' : 'disabled' ?>
                            type="radio"
                            name="szed__size-select"
                            class="hh-sizes-list__item-choose js-szed__size-select"
                            data-size-id="<?= esc_attr($size_id) ?>"
                            data-crop-params="<?= esc_attr(json_encode($crop_params)) ?>"
                        >
                    </div>

                    <div class="hh-sizes-list__item-info js-szed__size-info">
                        <?= load_view(SZED_PLUGIN_PATH . '/views/size-info.php', [
                            'size-data' => $size_data,
                        ]); ?>
                    </div>

                </div>

            <?php } ?>
        </div>

        <!-- Preview -->
        <div class="hh-previews">
            <div class="hh-previews__item">
                <div class="hh-previews__item-title">Новое изоражение</div>
                <div class="hh-previews__item-content hh-preview-current js-szed__preview"></div>
            </div>
            <div class="hh-previews__item">
                <div class="hh-previews__item-title">Предыдущее изображение</div>
                <div class="hh-previews__item-content">
                    <img src="" class="js-szed__preview-old" alt="">
                </div>

            </div>
        </div>

        <!-- Misc -->
        <div class="hh-misc">
            <div class="hh-misc__title">Прочее</div>
            <div class="hh-misc__content">
                <!-- edit attachment: list -->
                <a target="_blank" href="<?= esc_attr($edit_url__list) ?>">Изменить параметры изображения (list, old style)</a>
                <br>

                <!-- edit attachment: grid -->
                <a target="_blank" href="<?= esc_attr($edit_url__grid_direct) ?>">Изменить параметры изображения (in grid)</a>
                <br>
                <br>

                <!-- full size info -->
                <b>Параметры полного размера:</b>
                <?= esc_html($full_size['image']['width']) ?>
                x
                <?= esc_html($full_size['image']['height']) ?>
                (<?= esc_html($full_size['image']['type']) ?>)
                <a href="<?= esc_attr($sizes['full']['image']['url']) ?>" target="_blank">Просмотр</a>
                <br>

                <!-- original file info -->
                <b>Параметры исходного файла:</b>
                <?php if ($has_separate_original_file) { ?>
                    <?= esc_html($original_size['width']) ?>
                    x
                    <?= esc_html($original_size['height']) ?>
                    (<?= esc_html($original_size['type']) ?>)
                    <a href="<?= esc_attr($original_size['url']) ?>" target="_blank">Просмотр</a>
                <?php } else { ?>
                    отсутствует (см. параметры полного размера).
                <?php } ?>
                <br>

                <!-- attachment ID-->
                <b>ID изображения:</b> <?= esc_html($image_id) ?>
            </div>

        </div>

    </div>

    <div class="hh-editor-page__editor">

        <!-- Editor -->
        <div class="hh-editor">
            <div><!-- dont delete -->
                <img id="hh-image" class="hh-editor__image" src="">
            </div>
        </div>

        <div class="hh-editor__buttons-container">
            <button class="button-primary button-large hh-editor__button js-szed__button-crop" type="button">Обрезать</button>
            <button class="button-primary button-large hh-editor__button js-szed__button-reset" type="button">Сбросить</button>
            <button class="button-primary button-large hh-editor__button js-szed__button-download" type="button">Скачать</button>
            <?php if ($is_debug) { ?>
                <button class="button-primary button-large hh-editor__button js-szed__button-debug" type="button">Debug</button>
            <?php } ?>
        </div>

        <div class="hh-pending-info js-szed__preloader">
            <div class="hh-pending-info__preloader hh-preloader spinner is-active"></div>
            <div class="hh-pending-info__text">Cropping...</div>
        </div>

        <div class="notice notice-error inline is-dismissible hh-errors js-szed__errors">
            <p>Интересно будет посмотреть за их развитием, как команды.</p>
        </div>

    </div>

</div>

</div><!-- .wrap -->

<script type="text/javascript">
    var szed = szed ? szed : {};
    szed.sizes = <?= json_encode($sizes); ?>;
    szed.ajax_url = '<?= $ajax_url ?>';
    szed.image_id = <?= $image_id ?>;
    szed.debug = <?= $is_debug ? 'true' : 'false' ?>;
    szed.nonce = '<?= $nonce ?>';
</script>
