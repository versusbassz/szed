<?php
/** @var array $data */

if (! isset($data['size-data']) || ! is_array($data['size-data'])) {
    return '';
}

$size_data = $data['size-data'];
$size_settings = $data['size-settings'];
$size_custom_title = isset($size_settings['custom-title']) && $size_settings['custom-title'] ? $size_settings['custom-title'] : null;

$file_exists = $size_data['file-exists'];
?>

<div class="hh-sizes-list__item-cell hh-sizes-list__item-cell--id">

    <!-- Системное имя размера изображений -->
    <?php if ($size_custom_title) { ?>
        <span><?= esc_html($size_custom_title) ?></span>
        <br>
        <span class="hh-sizes-list__size-id-sub-line">id: <?= $size_data['id'] ?></span>
    <?php } else { ?>
        <span><?= $size_data['id'] ?></span>
    <?php } ?>

    <?php if (isset($size_settings['content']) && $size_settings['content']) { ?>

        <a
            href="javascript:void(0)"
            class="js-szed__size-wiki-icon"
            data-size-id="<?= esc_attr($size_data['id']) ?>"
        >&#x1F6C8;</a>

    <?php } ?>
</div>

<div class="hh-sizes-list__item-cell hh-sizes-list__item-cell--params" title="Параметры размера">

    <?php if ($size_data['data']['width'] || $size_data['data']['height']) { ?>
        <?= (int) $size_data['data']['width'] ?>x<?= (int) $size_data['data']['height'] ?>
        <?php if ($size_data['data']['crop']) { ?>
            <i class="szed-icon-crop hh-sizes-list__crop-icon"></i>
        <?php } ?>

    <?php } ?>

</div>

<div class="hh-sizes-list__item-cell hh-sizes-list__item-cell--misc">
    <i
        class="szed-icon-database"
        style="<?= $size_data['has-size'] ? '' : ' opacity: 0.3; ' ?>"
        title="Файл размера создан (в БД)"
    ></i>

    <i
        class="szed-icon-hdd"
        style="<?= $file_exists ? '' : ' opacity: 0.3; ' ?>"
        title="Файл размер существует физически на диске"
    ></i>

    <?php if ($file_exists) { ?>
        <a
            href="<?= esc_attr($size_data['image']['url']) ?>"
            target="_blank"
            title="Просмотр файла в отдельной вкладке"
        ><i class="szed-icon-eye"></i></a>
    <?php } ?>
</div>


<div class="hh-sizes-list__item-cell hh-sizes-list__item-cell--more js-szed-extra-actions__root">
    <div>
        <a href="javascript:void(0)" class="hh-extra-actions-button js-szed-extra-actions__button"><i class="szed-icon-ellipsis-vert"></i></a>
    </div>

    <div class="hh-extra-actions">
        <div class="hh-extra-actions__list js-szed-extra-actions__list">
            <a href="<?= esc_attr($size_data['image']['url']) ?>" class="hh-extra-actions__item" download="">Download</a>
        </div>
    </div>
</div>
