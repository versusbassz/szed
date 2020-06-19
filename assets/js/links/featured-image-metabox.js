// <?= esc_attr($editor_url) ?>
const editorUrl = szed.editor_url;

const editorUrlTemplate = `<a id="szed-featured-image-metabox-link" href="${editorUrl}" target="_blank">Редактировать размеры</a>`;

let currentInputValue = 0;

function removeLink() {
  const $prevLink = $('#szed-featured-image-metabox-link');

  if ($prevLink.length) {
    $prevLink.remove();
  }
}

function tick() {
  const $thumbnailInput = $('#_thumbnail_id');

  if ($thumbnailInput.length) {
    const inputValue = $thumbnailInput.val();

    if (inputValue === '-1' || inputValue === -1) {
      removeLink();
      return;
    }

    if (inputValue && inputValue !== currentInputValue) {
      const link = editorUrlTemplate.split('image-id=0')
        .join(`image-id=${inputValue}`);

      $thumbnailInput.parents('.inside')
        .first()
        .append($(link));

      currentInputValue = inputValue;
    }
  }
}

jQuery(document)
  .ready(() => {
    setInterval(tick, 2000);
  });
