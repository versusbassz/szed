
export function get_editor_page_url(image_id) {
    let url_template = szed.editor_page_url_template;
    let url = url_template.split('image-id=0').join('image-id=' + image_id);

    return url;
}
