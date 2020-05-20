<?php
/** @var array $data */

if (! isset($data['message']) || ! is_string($data['message']) || ! $data['message']) {
    return '';
}

?>

<?php require __DIR__ . '/page-header.php'; ?>

<?= $data['message'] ?>
