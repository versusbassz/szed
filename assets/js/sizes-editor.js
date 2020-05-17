import Cropper from 'cropperjs';

let editor;
let size;
let image = document.getElementById('hh-image');
let $image = $(image);

let $prev_size_image = $('.js-szed__preview-old');

$('.js-szed__size-select').change(function () {
    let $input = $(this);

    let size_id = $input.attr('data-size-id');

    let prev_size_url = szed.sizes[size_id]['file-exists'] ? szed.sizes[size_id].image.url : '';
    $prev_size_image.attr('src', disable_cache_for_url(prev_size_url));

    let crop_params_raw = JSON.parse($input.attr('data-crop-params'));
    console.log(crop_params_raw);
    let crop_params = crop_params_raw ? crop_params_raw : [];

    let $size_item = $input.parents('.js-szed__size-item').first();

    size = size_id; // rethink this logic (where should it store global state ???)

    start_editor(size_id, crop_params);
});

// Button - Crop
$('.js-szed__button-crop').click(function () {

    if (! editor) {
        alert('Выберите размер изображения для редактирования!');
        return;
    }

    console.log('Ajax request started');
    editor.disable();

    let data = editor.getData(true);
    data.size_id = size;
    data.image_id = szed.image_id;
    console.log(data);

    $.ajax({
        type : 'POST',
        cache : false,
        url : szed.ajax_url,
        data : data,
        dataType : 'json',
        success : function(response) {
            console.log(response);

            editor.enable();

            // prev_image set
            let new_url = response.data.url;
            $prev_size_image.attr('src', disable_cache_for_url(new_url));

            // crop params update
            let new_crop_params = response.data.crop_params;
            let $cur_size_input = $('.js-szed__size-select[data-size-id="' + size + '"]');

            $cur_size_input.attr('data-crop-params', JSON.stringify(new_crop_params));
        }
    });
});

// Button - Reset
$('.js-szed__button-reset').click(function () {

    if (! editor) {
        console.warn('Editor is not initialized');
        return;
    }

    editor.enable();
    editor.reset();
});

// Button - Download
$('.js-szed__button-download').click(function () {

    if (! editor) {
        console.warn('Editor is not initialized');
        return;
    }

    let result = editor.getCroppedCanvas().toDataURL('image/jpeg'); // TODO depending on soure mime-type ???

    trigger_download(result, 'result.jpg');
});

// Button - Debug
$('.js-szed__button-debug').click(function () {

    if (! editor) {
        console.warn('Editor is not initialized');
        return;
    }

    trigger_download("data:text/html,HelloWorld!", "helloWorld.txt");
});

function start_editor(size_id, crop_params) {
    if (editor) {
        editor.destroy();
    }

    let full_size_url = szed.sizes.full.image.url;

    if ($image.attr('src') !== full_size_url) {
        $image.attr('src', full_size_url);
        $image.on('load', function () {
            init_editor(size_id, crop_params);
        });
        return;
    }

    init_editor(size_id, crop_params);
}

function init_editor(size_id, crop_params) {
    let current_size = szed.sizes[size_id];

    // cropbox minimal dimentions
    let image_width_visible = $image.width();
    let image_height_visible = $image.height();
    let image_width_natural = image.naturalWidth;
    let image_height_natural = image.naturalHeight;

    let minCropBoxWidth = Math.ceil(current_size.data.width * (image_width_visible / image_width_natural));
    let minCropBoxHeight = Math.ceil(current_size.data.height * (image_height_visible / image_height_natural));

    // init
    editor = new Cropper(image, {
        viewMode: 1, // for availability of .setData()
        aspectRatio: current_size.data.ratio,
        minCropBoxWidth: minCropBoxWidth,
        minCropBoxHeight: minCropBoxHeight,
        preview: '.js-szed__preview',
        guides: false,
        movable: false,
        rotatable: false,
        zoomable: false,
        ready (event) {
            if (crop_params) {
                editor.setData(crop_params);
            }
        }
    });
}

function disable_cache_for_url(url) {
    if (! url) {
        return '';
    }

    let new_url = new URL(url);
    let query_args = new_url.searchParams;
    query_args.set('timestamp', Date.now());
    new_url.search = query_args.toString();

    return new_url.toString();
}

function trigger_download(dataurl, filename) {
    var a = document.createElement('a');
    a.href = dataurl;
    a.setAttribute('download', filename);
    a.click();
}

// Start with 1st active size in list
$('.js-szed__size-select:enabled').first().prop('checked', true).trigger('change');
