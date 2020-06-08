/* eslint-disable import/prefer-default-export */

export function getEditorPageUrl(imageId) {
  const urlTemplate = szed.editor_page_url_template;
  const url = urlTemplate.split('image-id=0').join(`image-id=${imageId}`);

  return url;
}

export function disableCacheForUrl(url) {
  if (!url) {
    return '';
  }

  const newUrl = new URL(url);
  const queryArgs = newUrl.searchParams;
  queryArgs.set('timestamp', Date.now());
  newUrl.search = queryArgs.toString();

  return newUrl.toString();
}
