import Cropper from 'cropperjs';


console.log('szed started');

const image = document.getElementById('hh-image');
console.log(image);

const cropper = new Cropper(image, {
    aspectRatio: 16 / 9,
    preview: '.hh-preview',
    // aspectRatio: 1,
    crop(event) {
        // console.log('State ===========================');
        // console.log(event.detail.x);
        // console.log(event.detail.y);
        // console.log(event.detail.width);
        // console.log(event.detail.height);
        // console.log(event.detail.rotate);
        // console.log(event.detail.scaleX);
        // console.log(event.detail.scaleY);
    },
});


document.getElementById('hh-chose-img');


var button = document.querySelector('#hh-chose-img');

button.addEventListener('click', function () {

    var frame = new wp.media.view.MediaFrame.Select({
        // Modal title
        title: 'Select profile background',

        // Enable/disable multiple select
        multiple: true,

        // Library WordPress query arguments.
        library: {
            order: 'ASC',

            // [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo',
            // 'id', 'post__in', 'menuOrder' ]
            orderby: 'title',

            // mime type. e.g. 'image', 'image/jpeg'
            type: 'image',

            // Searches the attachment title.
            search: null,

            // Attached to a specific post (ID).
            uploadedTo: null
        },

        button: {
            text: 'Set profile background'
        }
    });

    frame.on('select', function() {
        var attachment = frame.state().get('selection').first().toJSON();
        console.log(attachment);
        button.value = attachment.id;
    });

    frame.open();

});
