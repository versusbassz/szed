# Szed
Szed = Attachments **S**i**Z**es **ED**itor

- editor for usual attacments sizes
- uses "Fly Dynamic Images Resizer" as a fallback (experimental feature)
- compatible with results (cropped files) of similar plugins:
    - [Manual Image Crop](https://wordpress.org/plugins/manual-image-crop/)
    - [Post Thumbnail Editor](https://wordpress.org/plugins/post-thumbnail-editor/)

## Restrictions
- works only with `{uploads-path}/{year}}/{month}}/{file-name}.{ext}` structure
- doesnt have i18n, fow now
- doesnt work with Multisite

## Requirements
- PHP 7.4+
- Wordpress 5.7+
- display resolution (minimal): 1366x768
- browsers: old browsers are not supported, only latest versions

## How to start development
- `make build`
- `make dev-env--up` (requires docker)
