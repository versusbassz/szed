<?php
/** @var array $data */

if (! isset($data['size-data']) || ! is_array($data['size-data'])) {
    return '';
}

$size_data = $data['size-data'];

$file_exists = $size_data['file-exists'];
?>

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
