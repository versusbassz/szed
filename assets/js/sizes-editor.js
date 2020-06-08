import Cropper from 'cropperjs';
import constructErrorsBlock from './components/errors-block';
import constructPreloader from './components/preloader';
import { log } from './debug';
import initChooseImageLogic from './choose-image';
import { disableCacheForUrl } from './util';

let editor;
let size;
const image = document.getElementById('hh-image');
const $image = $(image);

const $prevSizeImage = $('.js-szed__preview-old');
const preloader = constructPreloader($('.js-szed__preloader'));
const errorsBlock = constructErrorsBlock($('.js-szed__errors'));

initChooseImageLogic();

$('.js-szed__size-select').change(function () {
  const $input = $(this);

  const sizeId = $input.attr('data-size-id');

  const prevSizeUrl = szed.sizes[sizeId]['file-exists'] ? szed.sizes[sizeId].image.url : '';
  $prevSizeImage.attr('src', disableCacheForUrl(prevSizeUrl));

  const cropParamsRaw = JSON.parse($input.attr('data-crop-params'));
  const cropParams = cropParamsRaw || [];

  size = sizeId; // rethink this logic (where should it store global state ???)

  startEditor(sizeId, cropParams);
});

// Button - Crop
$('.js-szed__button-crop').click(() => {
  if (!editor) {
    // eslint-disable-next-line no-alert
    alert('Выберите размер изображения для редактирования!');
    return;
  }

  log('Ajax request started');
  editor.disable();

  const data = editor.getData(true);
  data.size_id = size;
  data.image_id = szed.image_id;
  data.nonce = szed.nonce;
  log(data, 'Request data:');

  errorsBlock.hide();
  preloader.show();

  $.ajax({
    type: 'POST',
    cache: false,
    url: szed.ajax_url,
    data,
    dataType: 'json',
    success(response) {
      log(response, 'Response:');

      // check errors
      if (response.result === 'fail') {
        errorsBlock.show(response.data);
        return;
      }

      // prev_image set
      const newUrl = response.data.url;
      $prevSizeImage.attr('src', disableCacheForUrl(newUrl));

      // crop params update
      const newCropParams = response.data.crop_params;
      const $curSizeInput = $(`.js-szed__size-select[data-size-id="${size}"]`);

      $curSizeInput.attr('data-crop-params', JSON.stringify(newCropParams));

      // size row info update
      const newRowLayout = response.data['row-layout'];
      $(`.js-szed__size-item[data-size-id="${size}"]`).find('.js-szed__size-info').html(newRowLayout);
    },
    error(jqXHR, textStatus) {
      errorsBlock.show({ 'szed.request-error': textStatus });
    },
    complete: () => {
      editor.enable();
      preloader.hide();
    },
  });
});

// Button - Reset
$('.js-szed__button-reset').click(() => {
  if (!editor) {
    // eslint-disable-next-line no-console
    console.warn('Editor is not initialized');
    return;
  }

  editor.enable();
  editor.reset();
});

// Button - Download
$('.js-szed__button-download').click(() => {
  if (!editor) {
    // eslint-disable-next-line no-console
    console.warn('Editor is not initialized');
    return;
  }

  const mimeType = szed.image_mime_type;
  let extension;

  switch (mimeType) {
    case 'image/jpeg':
      extension = 'jpg';
      break;

    case 'image/png':
      extension = 'png';
      break;

    default:
      // eslint-disable-next-line no-console
      console.error('Некорректный mime-type скачиваемого файла');
      extension = 'jpg'; // trying to set jpeg as fallback...
      return;
  }

  const result = editor.getCroppedCanvas().toDataURL(mimeType);

  triggerDownload(result, `result.${extension}`);
});

// Button - Debug
$('.js-szed__button-debug').click(() => {
  if (!editor) {
    // eslint-disable-next-line no-console
    console.warn('Editor is not initialized');
    return;
  }

  triggerDownload('data:text/html,HelloWorld!', 'helloWorld.txt');
});

// Extra action menu - toggle visibility
$(document).on('click', '.js-szed-extra-actions__button', function () {
  const $link = $(this);
  const $currentSizeItem = $link.parents('.js-szed__size-item').first();

  // hide extra actions for other sizes
  $currentSizeItem.siblings().find('.js-szed-extra-actions__button').removeClass('hh-extra-actions-button--focused');
  $currentSizeItem.siblings().find('.js-szed-extra-actions__list').hide();

  // show extra actions for selected size
  const $root = $link.parents('.js-szed-extra-actions__root');
  const $menu = $root.find('.js-szed-extra-actions__list');

  $link.toggleClass('hh-extra-actions-button--focused');
  $menu.toggle();
});

// Size info icon click (to modal window)
$(document).on('click', '.js-szed__size-wiki-icon', function () {
  const $link = $(this);
  const sizeId = $link.attr('data-size-id');

  if (!szed.sizes_help[sizeId] || !szed.sizes_help[sizeId].content) {
    return;
  }

  $.fancybox.open({
    src: szed.sizes_help[sizeId].content,
    type: 'html',
    opts: {
      baseClass: 'szed-fancybox',
    },
  });
});

function startEditor(sizeId, cropParams) {
  if (editor) {
    editor.destroy();
  }

  const fullSizeUrl = szed.sizes.full.image.url;
  szed.image_mime_type = szed.sizes.full.image['mime-type'];

  if ($image.attr('src') !== fullSizeUrl) {
    $image.attr('src', fullSizeUrl);
    $image.on('load', () => {
      initEditor(sizeId, cropParams);
    });
    return;
  }

  initEditor(sizeId, cropParams);
}

function initEditor(sizeId, cropParams) {
  const currentSize = szed.sizes[sizeId];

  // cropbox minimal dimentions
  const imageWidthVisible = $image.width();
  const imageHeightVisible = $image.height();
  const imageWidthNatural = image.naturalWidth;
  const imageHeightNatural = image.naturalHeight;

  /* eslint-disable max-len */
  const minCropBoxWidth = Math.ceil(currentSize.data.width * (imageWidthVisible / imageWidthNatural));
  const minCropBoxHeight = Math.ceil(currentSize.data.height * (imageHeightVisible / imageHeightNatural));
  /* eslint-enable max-len */

  // init
  editor = new Cropper(image, {
    viewMode: 1, // for availability of .setData()
    aspectRatio: currentSize.data.ratio,
    autoCropArea: 1,
    minCropBoxWidth,
    minCropBoxHeight,
    preview: '.js-szed__preview',
    guides: false,
    movable: false,
    rotatable: false,
    zoomable: false,
    ready() {
      if (cropParams) {
        editor.setData(cropParams);
      }
    },
  });
}

function triggerDownload(dataurl, filename) {
  const a = document.createElement('a');
  a.href = dataurl;
  a.setAttribute('download', filename);
  a.click();
}

// Start with 1st active size in list
$('.js-szed__size-select:enabled').first().prop('checked', true).trigger('change');

preloader.hide();
errorsBlock.hide();
