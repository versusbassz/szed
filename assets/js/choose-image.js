import { getEditorPageUrl } from './util';

const button = document.querySelector('.js-szed__choose-image');

if (button) {
  button.addEventListener('click', handleClick);
}

function handleClick() {
  const frame = new wp.media.view.MediaFrame.Select({
    // Modal title
    title: 'Select image to crop',

    // Enable/disable multiple select
    multiple: false,

    // Library WordPress query arguments.
    library: {
      order: 'ASC',

      // [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo',
      // 'id', 'post__in', 'menuOrder' ]
      orderby: 'date',

      // mime type. e.g. 'image', 'image/jpeg'
      type: szed.valid_mime_types,

      // Searches the attachment title.
      search: null,

      // Attached to a specific post (ID).
      uploadedTo: null,
    },

    button: {
      text: 'Открыть в редакторе',
    },
  });

  frame.on('select', () => {
    const attachment = frame.state().get('selection').first().toJSON();
    const url = getEditorPageUrl(attachment.id);
    window.location.href = url;
  });

  frame.open();
}
