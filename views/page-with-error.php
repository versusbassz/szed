<?php
/** @var array $data */

use function szed\util\get_crop_page_url;

$message = isset($data['message']) && is_string($data['message']) && $data['message'] ? $data['message'] : null;
$button_text = isset($data['button-text']) && is_string($data['button-text']) && $data['button-text'] ? $data['button-text'] : 'Выбрать другое изображение';
?>

<div class="wrap">
    <?php require __DIR__ . '/page-header.php'; ?>

    <?php if ($message) { ?>
        <p><?= $message ?></p>
    <?php } ?>

    <p><button class="button-primary js-szed__choose-image" type="button"><?= esc_html($button_text) ?></button></p>
</div>

<script type="text/javascript">
    var szed = szed ? szed : {};
    szed.valid_mime_types = <?= json_encode(SZED_VALID_MIME_TYPES); ?>;
    szed.editor_page_url_template = '<?= get_crop_page_url(0) ?>';
</script>
