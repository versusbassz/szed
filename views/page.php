<?php
use function szed\util\get_env;

/** @var array $sizes */
/** @var int $image_id */
/** @var \WP_Post $image */

$ajax_url = get_admin_url(null, 'admin-ajax.php?action=' . SZED_AJAX_ACTION_NAME);
$is_debug = get_env('debug');
?>

<div class="hh-editor-page">

    <div class="hh-editor-page__secondary">

        <div class="hh-sizes-list">
            <?php foreach ($sizes as $size_id => $size_data) {
                $file_exists = $size_data['file-exists'];
                $crop_params = $size_data['image']['crop-params'][$size_id] ?? [];
                ?>

                <div class="hh-sizes-list__item js-szed__size-item" data-size-id="<?= esc_attr($size_id) ?>">

                    <div class="hh-sizes-list__item-cell" title="">
                        <input
                            <?= $size_data['data']['crop'] ? '' : 'disabled' ?>
                            type="radio"
                            name="szed__size-select"
                            class="js-szed__size-select"
                            data-size-id="<?= esc_attr($size_id) ?>"
                            data-crop-params="<?= esc_attr(json_encode($crop_params)) ?>"
                        >
                    </div>

                    <div class="hh-sizes-list__item-cell" title="Системное имя размера изображений">
                        ID:
                        <?= $size_data['id'] ?>
                    </div>

                    <?php if ($size_data['data']['width'] || $size_data['data']['height']) { ?>

                        <div class="hh-sizes-list__item-cell" title="Параметры размера">
                            <?= (int) $size_data['data']['width'] ?>x<?= (int) $size_data['data']['height'] ?>
                            <?= $size_data['data']['crop'] ? '(C)' : '' ?>
                        </div>

                    <?php } ?>

                    <div class="hh-sizes-list__item-cell" title="Файл размера создан (в БД)">
                        В БД:
                        <?= $size_data['has-size'] ? 'Да' : 'Нет' ?>
                    </div>

                    <div class="hh-sizes-list__item-cell" title="Файл размер существует физически на диске">
                        Физически:
                        <?= $file_exists ? 'Да' : 'Нет' ?>
                    </div>

                    <?php if ($file_exists) { ?>
                        <div class="hh-sizes-list__item-cell" title="Просмотр файла в отдельной вкладке">
                            <a href="<?= esc_attr($size_data['image']['url']) ?>" target="_blank">Просмотр</a>
                        </div>
                    <?php } ?>

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
            <button class="button-primary button-large hh-editor__button js-szed__button-debug" type="button">Debug</button>
        </div>

    </div>

</div>

<script type="text/javascript">
    var szed = {};
    szed.sizes = <?= json_encode($sizes); ?>;
    szed.ajax_url = '<?= $ajax_url ?>';
    szed.image_id = <?= $image_id ?>;
    szed.debug = <?= $is_debug ? 'true' : 'false' ?>;
</script>
